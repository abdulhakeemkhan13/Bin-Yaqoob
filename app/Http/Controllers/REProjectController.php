<?php

namespace App\Http\Controllers;

use App\Models\ReProject;
use App\Models\ReFloor;
use App\Models\ReUnit;
use App\Models\ReTower;
use App\Models\RePaymentPlan;
use App\Models\ReInstallment;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class REProjectController extends Controller
{
    /**
     * Display a listing of real estate projects.
     */
    public function index()
    {
        $projects = ReProject::where('created_by', Auth::user()->creatorId())
            ->orderBy('id', 'desc')
            ->get();

        return view('reproject.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project (wizard).
     */
    public function create()
    {
        // Get income accounts (type 4 = Income)
        $incomeAccounts = ChartOfAccount::select(DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            ->where('created_by', Auth::user()->creatorId())
            ->where('type', 4) // Income type
            ->where('is_enabled', 1)
            ->orderBy('code')
            ->pluck('code_name', 'id');
        $incomeAccounts->prepend('Select Income Account', '');

        // Get receivable accounts (Assets - Current Assets)
        $receivableAccounts = ChartOfAccount::select(DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            ->where('created_by', Auth::user()->creatorId())
            ->where('type', 1) // Assets type
            ->where('is_enabled', 1)
            ->orderBy('code')
            ->pluck('code_name', 'id');
        $receivableAccounts->prepend('Select Receivable Account', '');

        return view('reproject.create', compact('incomeAccounts', 'receivableAccounts'));
    }

    /**
     * Tab 1: Store project basic information.
     */
    public function storeBasicInfo(Request $request)
    {
        $projectId = $request->input('project_id');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:re_projects,code' . ($projectId ? ',' . $projectId : ''),
            'city' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'total_towers' => 'required|integer|min:1',
            'type' => 'required|in:Residential,Commercial,Mixed',
            'start_date' => 'nullable|date',
            'expected_completion' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $project = ReProject::updateOrCreate(
                ['id' => $projectId],
                [
                    'name' => $request->name,
                    'code' => strtoupper($request->code),
                    'city' => $request->city,
                    'area' => $request->area,
                    'address' => $request->address,
                    'total_floors' => 0, // Will be calculated when floors are saved
                    'total_towers' => $request->total_towers,
                    'total_units' => 0, // Will be calculated when units are saved
                    'type' => $request->type,
                    'status' => 'Planning',
                    'start_date' => $request->start_date,
                    'expected_completion' => $request->expected_completion,
                    'description' => $request->description,
                    'approval_authority' => $request->approval_authority,
                    'noc_number' => $request->noc_number,
                    'approval_date' => $request->approval_date,
                    'income_account_id' => $request->income_account_id ?: null,
                    'receivable_account_id' => $request->receivable_account_id ?: null,
                    'created_by' => Auth::user()->creatorId(),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Project basic info saved successfully.'),
                'project_id' => $project->id,
                'next_tab' => 2,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Failed to save project info.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tab 2: Store towers/blocks for a project.
     */
    public function storeTowers(Request $request, $id)
    {
        $project = ReProject::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'towers' => 'required|array|min:1',
            'towers.*.tower_code' => 'required|string|max:50',
            'towers.*.tower_name' => 'nullable|string|max:100',
            'towers.*.floors_count' => 'required|integer|min:1',
            'towers.*.construction_type' => 'required|in:RCC,Steel,Composite,Other',
            'towers.*.parking_type' => 'required|in:Basement,Podium,Mechanical,Open,None',
            'towers.*.elevator_count' => 'nullable|integer|min:0',
            'towers.*.status' => 'required|in:Planning,Construction,Completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete existing towers
            $project->towers()->delete();

            // Create new towers
            foreach ($request->towers as $towerData) {
                ReTower::create([
                    're_project_id' => $project->id,
                    'tower_code' => $towerData['tower_code'],
                    'tower_name' => $towerData['tower_name'] ?? null,
                    'floors_count' => $towerData['floors_count'],
                    'construction_type' => $towerData['construction_type'],
                    'parking_type' => $towerData['parking_type'],
                    'elevator_count' => $towerData['elevator_count'] ?? 0,
                    'status' => $towerData['status'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Towers saved successfully.'),
                'next_tab' => 3,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Failed to save towers.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tab 3: Store floors for a project.
     */
    public function storeFloors(Request $request, $id)
    {
        $project = ReProject::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'floors' => 'required|array|min:1',
            'floors.*.tower_code' => 'nullable|string|max:50',
            'floors.*.floor_number' => 'required|string|max:50',
            'floors.*.floor_name' => 'nullable|string|max:100',
            'floors.*.total_units' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete existing floors
            $project->floors()->delete();

            // Get all towers for this project for lookup
            $towers = $project->towers()->pluck('id', 'tower_code');

            // Create new floors
            $totalUnits = 0;
            foreach ($request->floors as $floorData) {
                // Get tower ID if tower_code is provided
                $towerId = null;
                if (!empty($floorData['tower_code']) && isset($towers[$floorData['tower_code']])) {
                    $towerId = $towers[$floorData['tower_code']];
                }

                // Generate floor code: PROJECT_CODE_TOWER_CODE_FLOOR_NUMBER or PROJECT_CODE_FLOOR_NUMBER
                $floorCode = $project->code;
                if (!empty($floorData['tower_code'])) {
                    $floorCode .= '_' . $floorData['tower_code'];
                }
                $floorCode .= '_' . $floorData['floor_number'];
                
                ReFloor::create([
                    're_project_id' => $project->id,
                    're_tower_id' => $towerId,
                    'code' => $floorCode,
                    'floor_number' => $floorData['floor_number'],
                    'floor_name' => $floorData['floor_name'] ?? null,
                    'total_units' => $floorData['total_units'],
                ]);
                $totalUnits += (int) $floorData['total_units'];
            }

            // Update project total floors and total units
            $project->update([
                'total_floors' => count($request->floors),
                'total_units' => $totalUnits,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Floors saved successfully.'),
                'next_tab' => 4,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Failed to save floors.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tab 4: Store units for floors.
     */
    public function storeUnits(Request $request, $id)
    {
        $project = ReProject::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'units' => 'required|array|min:1',
            'units.*.floor_id' => 'required|exists:re_floors,id',
            'units.*.unit_number' => 'required|string|max:50',
            'units.*.unit_type' => 'required|in:Flat,Shop,Office,Penthouse',
            'units.*.covered_area' => 'nullable|numeric|min:0',
            'units.*.price_per_sqft' => 'nullable|numeric|min:0',
            'units.*.price' => 'required|numeric|min:0',
            'units.*.facing' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete existing units and their products for all floors
            foreach ($project->floors as $floor) {
                // Delete associated products first
                foreach ($floor->units as $unit) {
                    if ($unit->product_id) {
                        ProductService::where('id', $unit->product_id)->delete();
                    }
                }
                $floor->units()->forceDelete();
            }

            // Get or create "Unit" unit type
            $unitType = ProductServiceUnit::firstOrCreate(
                ['name' => 'Unit', 'created_by' => Auth::user()->creatorId()],
                ['name' => 'Unit', 'created_by' => Auth::user()->creatorId()]
            );

            // Create new units
            foreach ($request->units as $unitData) {
                // Get floor info for SKU generation
                $floor = ReFloor::find($unitData['floor_id']);
                
                // Generate unique SKU: PROJECT_CODE_FLOOR_UNIT
                $sku = $project->code . '_' . $floor->floor_number . '_' . $unitData['unit_number'];
                
                // Get or create category based on unit type
                $category = ProductServiceCategory::firstOrCreate(
                    ['name' => $unitData['unit_type'], 'created_by' => Auth::user()->creatorId()],
                    ['name' => $unitData['unit_type'], 'type' => 'product & service', 'created_by' => Auth::user()->creatorId()]
                );

                // Create ProductService entry
                $product = ProductService::create([
                    'name' => $unitData['unit_number'],
                    'sku' => $sku,
                    'sale_price' => $unitData['price'],
                    'purchase_price' => 0,
                    'category_id' => $category->id,
                    'unit_id' => $unitType->id,
                    'type' => 'unit',
                    'created_by' => Auth::user()->creatorId(),
                ]);

                // Create unit with product link
                $unit = ReUnit::create([
                    're_floor_id' => $unitData['floor_id'],
                    'product_id' => $product->id,
                    'unit_number' => $unitData['unit_number'],
                    'unit_type' => $unitData['unit_type'],
                    'covered_area' => $unitData['covered_area'] ?? null,
                    'price_per_sqft' => $unitData['price_per_sqft'] ?? null,
                    'price' => $unitData['price'],
                    'facing' => $unitData['facing'] ?? null,
                    'status' => 'Available',
                ]);

                $product->update([
                    'project_unit_id' => $unit->id,
                ]);
            }

            // Update project total units
            $project->update(['total_units' => count($request->units)]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Units saved successfully.'),
                'next_tab' => 5,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Failed to save units.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tab 5: Assign existing payment plans to project.
     */
    public function assignPaymentPlans(Request $request, $id)
    {
        $project = ReProject::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'payment_plan_ids' => 'required|array|min:1',
            'payment_plan_ids.*' => 'exists:re_payment_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Sync payment plans using pivot table
            $project->paymentPlans()->sync($request->payment_plan_ids);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Payment plans assigned successfully.'),
                'next_tab' => 6,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Failed to assign payment plans.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tab 6: Final submit - activate the project.
     */
    public function finalSubmit(Request $request, $id)
    {
        $project = ReProject::findOrFail($id);

        try {
            DB::beginTransaction();

            $project->update(['status' => 'Active']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Project submitted and activated successfully.'),
                'redirect' => route('re-projects.show', $project->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Failed to submit project.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified project.
     */
    public function show($id)
    {
        $project = ReProject::with(['floors.units', 'paymentPlans'])->findOrFail($id);

        return view('reproject.show', compact('project'));
    }

    /**
     * Show form to edit basic info for active projects.
     */
    public function editBasicInfo($id)
    {
        $project = ReProject::findOrFail($id);
        
        // Only allow editing for active projects
        if ($project->status !== 'Active') {
            return redirect()->route('re-projects.show', $id)
                ->with('error', __('Only active projects can be edited.'));
        }
        
        $chartOfAccounts = ChartOfAccount::where('created_by', Auth::user()->creatorId())->pluck('name', 'id');
        
        return view('reproject.edit_basic_info', compact('project', 'chartOfAccounts'));
    }

    /**
     * Update basic info for active projects.
     */
    public function updateBasicInfo(Request $request, $id)
    {
        $project = ReProject::findOrFail($id);
        
        // Only allow editing for active projects
        if ($project->status !== 'Active') {
            return redirect()->route('re-projects.show', $id)
                ->with('error', __('Only active projects can be edited.'));
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'type' => 'required|in:Residential,Commercial,Mixed',
            'start_date' => 'nullable|date',
            'expected_completion' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'approval_authority' => 'nullable|string',
            'noc_number' => 'nullable|string',
            'approval_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $project->update([
                'name' => $request->name,
                'city' => $request->city,
                'area' => $request->area,
                'address' => $request->address,
                'type' => $request->type,
                'start_date' => $request->start_date,
                'expected_completion' => $request->expected_completion,
                'description' => $request->description,
                'approval_authority' => $request->approval_authority,
                'noc_number' => $request->noc_number,
                'approval_date' => $request->approval_date,
                'income_account_id' => $request->income_account_id ?: null,
                'receivable_account_id' => $request->receivable_account_id ?: null,
            ]);

            return redirect()->route('re-projects.show', $id)
                ->with('success', __('Project basic info updated successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to update project info.'))
                ->withInput();
        }
    }

    /**
     * Get project data for review (AJAX).
     */
    public function getProjectData($id)
    {
        $project = ReProject::with(['towers', 'floors.tower', 'floors.units', 'paymentPlans'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'project' => $project,
        ]);
    }

    /**
     * Get floors for a project (AJAX).
     */
    public function getFloors($id)
    {
        $project = ReProject::findOrFail($id);
        $floors = $project->floors()->with('units')->get();

        return response()->json([
            'success' => true,
            'floors' => $floors,
        ]);
    }

    /**
     * Get a single unit (AJAX).
     */
    public function getUnit($projectId, $unitId)
    {
        $unit = ReUnit::findOrFail($unitId);

        return response()->json([
            'success' => true,
            'unit' => $unit,
        ]);
    }

    /**
     * Update a single unit (AJAX).
     */
    public function updateUnit(Request $request, $projectId, $unitId)
    {
        $unit = ReUnit::findOrFail($unitId);
        
        // Only allow editing if unit is Available
        if ($unit->status !== 'Available') {
            return response()->json([
                'success' => false,
                'message' => __('Only available units can be edited.'),
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'unit_number' => 'required|string|max:50',
            'unit_type' => 'required|in:Flat,Shop,Office,Penthouse',
            'covered_area' => 'nullable|numeric|min:0',
            'price_per_sqft' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'facing' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            // Flat fields
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'balconies' => 'nullable|integer|min:0',
            'has_parking' => 'nullable|boolean',
            // Shop/Office fields
            'floor_position' => 'nullable|string|max:50',
            'has_mezzanine' => 'nullable|boolean',
            'has_washroom' => 'nullable|boolean',
            // Penthouse fields
            'terrace_area' => 'nullable|numeric|min:0',
            'is_duplex' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit->update([
            'unit_number' => $request->unit_number,
            'unit_type' => $request->unit_type,
            'covered_area' => $request->covered_area,
            'price_per_sqft' => $request->price_per_sqft,
            'price' => $request->price,
            'facing' => $request->facing,
            'description' => $request->description,
            // Flat fields
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'balconies' => $request->balconies,
            'has_parking' => $request->has('has_parking'),
            // Shop/Office fields
            'floor_position' => $request->floor_position,
            'has_mezzanine' => $request->has('has_mezzanine'),
            'has_washroom' => $request->has('has_washroom'),
            // Penthouse fields
            'terrace_area' => $request->terrace_area,
            'is_duplex' => $request->has('is_duplex'),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Unit updated successfully.'),
            'unit' => $unit->fresh(),
        ]);
    }

    /**
     * Show unit details page.
     */
    public function showUnit($projectId, $unitId)
    {
        $project = ReProject::findOrFail($projectId);
        $unit = ReUnit::with(['floor', 'booking.customer', 'contract.installments.invoice.payments', 'contract.customer'])->findOrFail($unitId);

        \Log::info('Unit Details Debug', [
            'unit_id' => $unitId,
            'unit_status' => $unit->status,
            'has_booking' => $unit->booking ? true : false,
            'booking_data' => $unit->booking ? [
                'customer_name' => $unit->booking->customer_name,
                'customer_phone' => $unit->booking->customer_phone,
                'customer_email' => $unit->booking->customer_email,
                'booking_date' => $unit->booking->booking_date,
            ] : null,
            'has_contract' => $unit->contract ? true : false,
            'contract_id' => $unit->contract ? $unit->contract->id : null,
            'contract_status' => $unit->contract ? $unit->contract->status : null,
        ]);

        $contract = $unit->contract;
        $installments = $contract ? $contract->installments : collect();

        \Log::info('Installments Debug', [
            'installments_count' => $installments->count(),
            'installments_data' => $installments->map(function($inst) {
                return [
                    'number' => $inst->installment_number,
                    'type' => $inst->installment_type,
                    'amount' => $inst->amount,
                    'status' => $inst->status,
                ];
            })->toArray(),
        ]);

        return view('reproject.unit_show', compact('project', 'unit', 'contract', 'installments'));
    }
}

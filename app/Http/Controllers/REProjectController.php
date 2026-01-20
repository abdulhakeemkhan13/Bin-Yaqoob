<?php

namespace App\Http\Controllers;

use App\Models\ReProject;
use App\Models\ReFloor;
use App\Models\ReUnit;
use App\Models\RePaymentPlan;
use App\Models\ReInstallment;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
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
     
        return view('reproject.create');
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
            'total_floors' => 'required|integer|min:1',
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
                    'total_floors' => $request->total_floors,
                    'total_units' => 0, // Will be calculated when floors are saved
                    'type' => $request->type,
                    'status' => 'Planning',
                    'start_date' => $request->start_date,
                    'expected_completion' => $request->expected_completion,
                    'description' => $request->description,
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
     * Tab 2: Store floors for a project.
     */
    public function storeFloors(Request $request, $id)
    {
        $project = ReProject::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'floors' => 'required|array|min:1',
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

            // Create new floors
            $totalUnits = 0;
            foreach ($request->floors as $floorData) {
                // Generate floor code: PROJECT_CODE_FLOOR_NUMBER
                $floorCode = $project->code . '_' . $floorData['floor_number'];
                
                ReFloor::create([
                    're_project_id' => $project->id,
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
                'next_tab' => 3,
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
     * Tab 3: Store units for floors.
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
                'next_tab' => 4,
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
     * Tab 4: Assign existing payment plans to project.
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
                'next_tab' => 5,
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
     * Tab 5: Final submit - activate the project.
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
     * Get project data for review (AJAX).
     */
    public function getProjectData($id)
    {
        $project = ReProject::with(['floors.units', 'paymentPlans'])->findOrFail($id);

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
}

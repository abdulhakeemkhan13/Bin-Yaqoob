<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Employee;
use App\Models\User;
use App\Models\Contract;
use App\Models\Deal;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function index()
    {
        // if (Auth::user()->can('manage commission')) {
            $commissions = Commission::where('created_by', '=', Auth::user()->creatorId())->with(['employee', 'dealer', 'contract', 'deal'])->get();
            return view('commission.index', compact('commissions'));
        // } else {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }

    public function create(Request $request)
    {
        // if (Auth::user()->can('create commission')) {
            $employees = Employee::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
            // For now, dealers and management can be selected from all users excluding super admin
            $users = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '!=', 'super admin')->get()->pluck('name', 'id');
            $contracts = Contract::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('contract_no', 'id');
            $deals = Deal::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
            
            $commission_for = Commission::$commission_for;
            $release_condition_types = Commission::$release_condition_types;
            $commission_types = Commission::$commissiontype;

            // Optional defaults from request (e.g. creating from contract/deal page)
            $selected_contract = $request->contract_id;
            $selected_deal = $request->deal_id;
            $selected_employee = $request->employee_id;

            return view('commission.create', compact(
                'employees', 'users', 'contracts', 'deals', 
                'commission_for', 'release_condition_types', 'commission_types',
                'selected_contract', 'selected_deal', 'selected_employee'
            ));
        // } else {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }
    }

    public function store(Request $request)
    {
        // if (Auth::user()->can('create commission')) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'commission_for' => 'required',
                    'type' => 'required',
                    'release_condition_type' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $commission = new Commission();
            $commission->title = $request->title;
            $commission->commission_for = $request->commission_for;
            $commission->type = $request->type;
            
            if ($request->commission_for == 'employee') {
                $commission->employee_id = $request->employee_id;
            } elseif ($request->commission_for == 'dealer') {
                $commission->dealer_id = $request->dealer_id;
            } elseif ($request->commission_for == 'company_management') {
                $commission->dealer_id = $request->user_id; // Using dealer_id for management too or keep it flexible
            }

            $commission->contract_id = $request->contract_id;
            $commission->deal_id = $request->deal_id;
            
            $commission->amount = $request->amount ?? 0;
            $commission->commission_percentage = $request->commission_percentage ?? 0;
            
            // Calculate total commission amount if percentage
            if ($request->type == 'percentage' && $request->contract_id) {
                $contract = Contract::find($request->contract_id);
                if ($contract) {
                    $commission->total_commission_amount = ($contract->sale_price * $request->commission_percentage) / 100;
                } else {
                    $commission->total_commission_amount = $request->amount ?? 0;
                }
            } else {
                $commission->total_commission_amount = $request->amount ?? 0;
            }

            $commission->release_condition_type = $request->release_condition_type;
            $commission->release_percentage = $request->release_percentage;
            $commission->status = 'pending';
            $commission->amount_released = 0;
            $commission->notes = $request->notes;
            $commission->created_by = Auth::user()->creatorId();
            $commission->save();

            return redirect()->back()->with('success', __('Commission successfully created.'));
        // } else {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }

    public function edit(Commission $commission)
    {
        // if (Auth::user()->can('edit commission')) {
            if ($commission->created_by == Auth::user()->creatorId()) {
                $employees = Employee::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
                $users = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '!=', 'super admin')->get()->pluck('name', 'id');
                $contracts = Contract::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('contract_no', 'id');
                $deals = Deal::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
                
                $commission_for = Commission::$commission_for;
                $release_condition_types = Commission::$release_condition_types;
                $commission_types = Commission::$commissiontype;

                return view('commission.edit', compact(
                    'commission', 'employees', 'users', 'contracts', 'deals', 
                    'commission_for', 'release_condition_types', 'commission_types'
                ));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        // } else {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }
    }

    public function update(Request $request, Commission $commission)
    {
        // if (Auth::user()->can('edit commission')) {
            if ($commission->created_by == Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(), [
                        'title' => 'required',
                        'commission_for' => 'required',
                        'type' => 'required',
                        'release_condition_type' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $commission->title = $request->title;
                $commission->commission_for = $request->commission_for;
                $commission->type = $request->type;
                
                if ($request->commission_for == 'employee') {
                    $commission->employee_id = $request->employee_id;
                    $commission->dealer_id = null;
                } elseif ($request->commission_for == 'dealer') {
                    $commission->dealer_id = $request->dealer_id;
                    $commission->employee_id = null;
                } elseif ($request->commission_for == 'company_management') {
                    $commission->dealer_id = $request->user_id;
                    $commission->employee_id = null;
                }

                $commission->contract_id = $request->contract_id;
                $commission->deal_id = $request->deal_id;
                
                $commission->amount = $request->amount ?? 0;
                $commission->commission_percentage = $request->commission_percentage ?? 0;
                
                // Recalculate total commission
                if ($request->type == 'percentage' && $request->contract_id) {
                    $contract = Contract::find($request->contract_id);
                    if ($contract) {
                        $commission->total_commission_amount = ($contract->sale_price * $request->commission_percentage) / 100;
                    }
                } else {
                    $commission->total_commission_amount = $request->amount ?? 0;
                }

                $commission->release_condition_type = $request->release_condition_type;
                $commission->release_percentage = $request->release_percentage;
                $commission->notes = $request->notes;
                $commission->save();

                return redirect()->back()->with('success', __('Commission successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        // } else {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }

    public function destroy(Commission $commission)
    {
        // if (Auth::user()->can('delete commission')) {
            if ($commission->created_by == Auth::user()->creatorId()) {
                $commission->delete();
                return redirect()->back()->with('success', __('Commission successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        // } else {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }

    public function release($id)
    {
        // if (Auth::user()->can('manage commission')) {
            $commission = Commission::find($id);
            if ($commission && $commission->created_by == Auth::user()->creatorId()) {
                $releasable = $this->calculateReleasableAmount($commission);
                
                if ($releasable > 0) {
                    $commission->amount_released += $releasable;
                    if ($commission->amount_released >= $commission->total_commission_amount) {
                        $commission->status = 'released';
                    } else {
                        $commission->status = 'partially_released';
                    }
                    $commission->save();

                    // Optional: Create a transaction or journal entry here

                    return redirect()->back()->with('success', __('Commission release processed.') . ' ' . Auth::user()->priceFormat($releasable) . ' ' . __('released.'));
                } else {
                    return redirect()->back()->with('error', __('No amount currently releasable based on payment conditions.'));
                }
            }
            return redirect()->back()->with('error', __('Commission not found.'));
        // }
        // return redirect()->back()->with('error', __('Permission denied.'));
    }

    private function calculateReleasableAmount(Commission $commission)
    {
        if (!$commission->contract_id) {
            return 0; // Manual commissions without contracts are released manually or don't use auto logic
        }

        $contract = Contract::with('installments')->find($commission->contract_id);
        if (!$contract) return 0;

        $total_paid = $contract->installments->where('status', 'paid')->sum('amount');
        $total_value = $contract->net_sale_price > 0 ? $contract->net_sale_price : $contract->sale_price;
        
        if ($total_value <= 0) return 0;

        $total_expected = $commission->total_commission_amount;
        $already_released = $commission->amount_released;

        switch ($commission->release_condition_type) {
            case 'full_payment_received':
                if ($total_paid >= $total_value) {
                    return $total_expected - $already_released;
                }
                break;

            case 'down_payment_received':
                $down_payment_paid = $contract->installments->where('installment_type', 'down_payment')->where('status', 'paid')->count() > 0;
                if ($down_payment_paid) {
                    return $total_expected - $already_released;
                }
                break;

            case 'percentage_received':
                $payment_ratio = $total_paid / $total_value;
                $should_be_released = $total_expected * $payment_ratio;
                $diff = $should_be_released - $already_released;
                return $diff > 0 ? $diff : 0;

            case 'fixed_milestone':
                // For now, if user clicks release, we check if there's any pending amount
                // In a more complex system, this would check against project milestones
                return 0; 
        }

        return 0;
    }
}

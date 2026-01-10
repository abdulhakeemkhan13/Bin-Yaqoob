<?php

namespace App\Http\Controllers;

use App\Models\RePaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RePaymentPlanController extends Controller
{
    /**
     * Display a listing of payment plans.
     */
    public function index()
    {
        $plans = RePaymentPlan::where('created_by', Auth::user()->creatorId())
            ->orderBy('id', 'desc')
            ->get();

        return view('repaymentplan.index', compact('plans'));
    }

    /**
     * Show the form for creating a new payment plan.
     */
    public function create()
    {
        return view('repaymentplan.create');
    }

    /**
     * Store a newly created payment plan.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
            'down_payment_percentage' => 'nullable|numeric|min:0|max:100',
            'num_installments' => 'required|integer|min:1',
            'frequency' => 'required|in:Monthly,Quarterly,Half-Yearly,Yearly',
            'possession_charges' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'extra_charges' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        RePaymentPlan::create([
            'plan_name' => $request->plan_name,
            'duration_months' => 1,
            'down_payment_percentage' => $request->down_payment_percentage,
            'down_payment_amount' => null,
            'num_installments' => $request->num_installments,
            'frequency' => $request->frequency,
            'possession_charges' => $request->possession_charges,
            'discount' => $request->discount,
            'extra_charges' => $request->extra_charges,
            'is_active' => true,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('re-payment-plans.index')
            ->with('success', __('Payment plan created successfully.'));
    }

    /**
     * Show the form for editing the specified payment plan.
     */
    public function edit($id)
    {
        $plan = RePaymentPlan::findOrFail($id);
        return view('repaymentplan.edit', compact('plan'));
    }

    /**
     * Update the specified payment plan.
     */
    public function update(Request $request, $id)
    {
        $plan = RePaymentPlan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
            'down_payment_percentage' => 'nullable|numeric|min:0|max:100',
            'num_installments' => 'required|integer|min:1',
            'frequency' => 'required|in:Monthly,Quarterly,Half-Yearly,Yearly',
            'possession_charges' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'extra_charges' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $plan->update([
            'plan_name' => $request->plan_name,
            'down_payment_percentage' => $request->down_payment_percentage,
            'num_installments' => $request->num_installments,
            'frequency' => $request->frequency,
            'possession_charges' => $request->possession_charges,
            'discount' => $request->discount,
            'extra_charges' => $request->extra_charges,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('re-payment-plans.index')
            ->with('success', __('Payment plan updated successfully.'));
    }

    /**
     * Remove the specified payment plan.
     */
    public function destroy($id)
    {
        $plan = RePaymentPlan::findOrFail($id);
        
        // Check if plan is used in any bookings
        if ($plan->bookings()->exists()) {
            return redirect()->back()
                ->with('error', __('Cannot delete plan - it is used in existing bookings.'));
        }

        $plan->delete();

        return redirect()->route('re-payment-plans.index')
            ->with('success', __('Payment plan deleted successfully.'));
    }

    /**
     * Get active plans for AJAX (used in project wizard)
     */
    public function getActivePlans()
    {
        $plans = RePaymentPlan::where('created_by', Auth::user()->creatorId())
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }
}

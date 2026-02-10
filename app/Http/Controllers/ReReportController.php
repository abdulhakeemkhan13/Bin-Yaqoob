<?php

namespace App\Http\Controllers;

use App\Models\ReProject;
use App\Models\ReFloor;
use App\Models\ReUnit;
use App\Models\ReBooking;
use App\Models\ReInstallment;
use App\Models\RePaymentPlan;
use App\Models\ContractInstallment;
use App\Models\Customer;
use App\Models\Contract;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerInstallmentLedgerExport;

class ReReportController extends Controller
{
    public function availability(Request $request)
    {
        $projects = ReProject::with(['towers.floors.units' => function($query) {
            $query->where('status', 'Available');
        }])->where('status', 'Active')->get();

        return view('re_reports.availability', compact('projects'));
    }

    public function bookingSummary(Request $request)
    {
        $query = ReBooking::with(['unit.floor.project', 'unit.floor.tower', 'customer']);

        if ($request->start_date) {
            $query->where('booking_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('booking_date', '<=', $request->end_date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->get();

        return view('re_reports.booking_summary', compact('bookings'));
    }

    public function unitStatus(Request $request)
    {
        $projects = ReProject::where('status', 'Active')->get();
        $allProjects = ReProject::all();

        $query = ReUnit::with(['floor.project', 'floor.tower']);

        // Default to active projects if no project filter is applied
        if ($request->project_id) {
            if ($request->project_id != 'all') {
                $query->whereHas('floor.project', function($q) use ($request) {
                    $q->where('id', $request->project_id);
                });
            }
        } else {
            $query->whereHas('floor.project', function($q) {
                $q->where('status', 'Active');
            });
        }

        $units = $query->get();
        $statusCounts = ReUnit::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');

        return view('re_reports.unit_status', compact('units', 'statusCounts', 'projects', 'allProjects'));
    }

    public function unitBooking(Request $request)
    {
        $bookings = ReBooking::with(['unit.floor.project', 'unit.floor.tower', 'customer'])->get();

        return view('re_reports.unit_booking', compact('bookings'));
    }

    public function priceList(Request $request)
    {
        $projects = ReProject::where('status', 'Active')->get();
        $allProjects = ReProject::all();

        $query = ReUnit::with(['floor.project', 'floor.tower']);

        // Project Filter
        if ($request->project_id) {
            if ($request->project_id != 'all') {
                $query->whereHas('floor.project', function($q) use ($request) {
                    $q->where('id', $request->project_id);
                });
            }
        } else {
            $query->whereHas('floor.project', function($q) {
                $q->where('status', 'Active');
            });
        }

        // Status Filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $units = $query->get();

        return view('re_reports.price_list', compact('units', 'projects', 'allProjects'));
    }

    public function installmentPlans(Request $request)
    {
        $allProjects = ReProject::all();

        $query = ReProject::with(['paymentPlans' => function($q) {
          
        }]);

        // Project Filter
        if ($request->project_id && $request->project_id != 'all') {
            $query->where('id', $request->project_id);
        } else {
            // Default to active projects only if no "all" is specified
            if (!$request->project_id) {
                $query->where('status', 'Active');
            }
        }

        $projectsWithPlans = $query->get();

        return view('re_reports.installment_plans', compact('projectsWithPlans', 'allProjects'));
    }

    public function installmentOverdue(Request $request)
    {
        $projects = ReProject::where('status', 'Active')->get();
        $allProjects = ReProject::all();

        $query = ContractInstallment::with(['contract.re_project', 'contract.tower', 'contract.floor', 'contract.unit', 'contract.customer'])
            ->where('status', '!=', 'paid')
            ->where('issue_date', '<', Carbon::today());

        if ($request->project_id && $request->project_id != 'all') {
            $query->whereHas('contract', function($q) use ($request) {
                $q->where('re_project_id', $request->project_id);
            });
        }

        $overdueInstallments = $query->get();

        return view('re_reports.installment_overdue', compact('overdueInstallments', 'projects', 'allProjects'));
    }

    public function installmentUpcoming(Request $request)
    {
        $projects = ReProject::where('status', 'Active')->get();
        $allProjects = ReProject::all();

        $query = ContractInstallment::with(['contract.re_project', 'contract.tower', 'contract.floor', 'contract.unit', 'contract.customer'])
            ->where('status', '!=', 'paid')
            ->whereBetween('issue_date', [Carbon::today(), Carbon::today()->addDays(30)]);

        if ($request->project_id && $request->project_id != 'all') {
            $query->whereHas('contract', function($q) use ($request) {
                $q->where('re_project_id', $request->project_id);
            });
        }

        $upcomingInstallments = $query->get();

        return view('re_reports.installment_upcoming', compact('upcomingInstallments', 'projects', 'allProjects'));
    }

    public function customerLedger(Request $request)
    {
        $allProjects = ReProject::all();
        $customers_list = Customer::all();

        $query = Customer::with(['contracts.re_project', 'contracts.tower', 'contracts.floor', 'contracts.unit', 'contracts.installments.invoice.payments.voucher']);

        // Filter by project
        if ($request->project_id && $request->project_id != 'all') {
            $query->whereHas('contracts', function($q) use ($request) {
                $q->where('re_project_id', $request->project_id);
            });
        }

        // Filter by customer
        if ($request->customer_id && $request->customer_id != 'all') {
            $query->where('id', $request->customer_id);
        }

        $customers = $query->get();

        if ($request->get('export') == 'excel') {
            return Excel::download(new CustomerInstallmentLedgerExport($customers), 'customer_installment_ledger_' . date('Y-m-d') . '.xlsx');
        }

        return view('re_reports.customer_ledger', compact('customers', 'allProjects', 'customers_list'));
    }

    public function customerStatementPrint($contract_id)
    {
        $contract = Contract::with(['customer', 're_project', 'tower', 'floor', 'unit', 'installments.invoice.payments.voucher'])->find($contract_id);

        if (!$contract) {
            return redirect()->back()->with('error', __('Contract not found.'));
        }

        $customer = $contract->customer;
        
        // Calculate financial summaries
        $totalPaid = 0;
        $totalOverdue = 0;
        $today = date('Y-m-d');

        foreach ($contract->installments as $installment) {
            $paid = $installment->invoice ? $installment->invoice->payments->sum('amount') : 0;
            $totalPaid += $paid;

            if ($installment->due_date && $installment->due_date < $today) {
                $due_remaining = $installment->amount - $paid;
                if ($due_remaining > 0) {
                    $totalOverdue += $due_remaining;
                }
            }
        }

        $financials = [
            'cost_of_unit' => $contract->sale_price,
            'discount_price' => $contract->discount_amount,
            'discount_installments' => 0, // Placeholder if not explicitly tracked
            'net_price' => $contract->net_sale_price,
            'other_charges' => $contract->other_charges,
            'paid_amount' => $totalPaid,
            'remaining_amount' => $contract->net_sale_price - $totalPaid,
            'overdue_amount' => $totalOverdue,
            'adjusted_amount' => 0, // Placeholder
        ];

        return view('re_reports.customer_statement_print', compact('contract', 'customer', 'financials'));
    }
}

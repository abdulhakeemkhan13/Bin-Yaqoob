<?php

namespace App\Http\Controllers;

use App\Models\ReProject;
use App\Models\ReFloor;
use App\Models\ReUnit;
use App\Models\ReBooking;
use App\Models\ReInstallment;
use App\Models\ContractInstallment;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $query = ReBooking::with(['unit.floor.project', 'customer']);

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
        $bookings = ReBooking::with(['unit.floor.project', 'unit.floor.tower', 'customer', 'paymentPlan'])->get();

        return view('re_reports.installment_plans', compact('bookings'));
    }

    public function installmentOverdue(Request $request)
    {
        $overdueInstallments = ContractInstallment::with(['contract.re_project', 'contract.tower', 'contract.floor', 'contract.unit', 'contract.customer'])
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', Carbon::today())
            ->get();

        return view('re_reports.installment_overdue', compact('overdueInstallments'));
    }

    public function installmentUpcoming(Request $request)
    {
        $upcomingInstallments = ContractInstallment::with(['contract.re_project', 'contract.tower', 'contract.floor', 'contract.unit', 'contract.customer'])
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(30)])
            ->get();

        return view('re_reports.installment_upcoming', compact('upcomingInstallments'));
    }
}

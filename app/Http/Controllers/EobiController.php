<?php

// app/Http/Controllers/EobiController.php
namespace App\Http\Controllers;

use App\Models\Eobi;
use App\Models\Employee;
use Illuminate\Http\Request;

class EobiController extends Controller
{
    public function create($employeeId)
    {
        if (!\Auth::user()->can('create other payment')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
        $employee = Employee::findOrFail($employeeId);
        return view('eobi.create', compact('employee'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create other payment')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title'       => 'required|string|max:191',
            'amount'      => 'required|numeric|min:0',
        ]);

        $data['created_by'] = \Auth::user()->creatorId();
        Eobi::create($data);

        return redirect()->back()->with('success', __('EOBI created successfully.'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('edit other payment')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
        $eobi = Eobi::findOrFail($id);
        return view('eobi.edit', compact('eobi'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('edit other payment')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $eobi = Eobi::findOrFail($id);
        $data = $request->validate([
            'title'  => 'required|string|max:191',
            'amount' => 'required|numeric|min:0',
        ]);
        $eobi->update($data);

        return redirect()->back()->with('success', __('EOBI updated successfully.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('delete other payment')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $eobi = Eobi::findOrFail($id);
        $eobi->delete();
        return redirect()->back()->with('success', __('EOBI deleted successfully.'));
    }
}


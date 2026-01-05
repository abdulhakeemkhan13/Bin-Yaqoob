<?php

namespace App\Http\Controllers;

use App\Models\SalaryTax;
use App\Models\Employee;
use Illuminate\Http\Request;

class SalaryTaxController extends Controller
{
    public function salarytaxCreate($id)
    {
        // FIX: SalaryTax has 'title', not 'name'
        $salary_tax = SalaryTax::where('created_by', \Auth::user()->creatorId())
                        ->get()->pluck('title', 'id');

        $employee   = Employee::find($id);

        return view('salarytax.create', compact('employee', 'salary_tax'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create allowance'))
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'title'       => 'required',
                    'amount'      => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $salarytax                 = new SalaryTax();
            $salarytax->employee_id    = $request->employee_id;
            $salarytax->title          = $request->title;
            $salarytax->amount         = $request->amount;
            $salarytax->created_by     = \Auth::user()->creatorId();
            $salarytax->save();

            return redirect()->back()->with('success', __('SalaryTax successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

public function create($employeeId)
{
    return $this->salarytaxCreate($employeeId);
}


    public function show(SalaryTax $salarytax)
    {
        return redirect()->route('salarytax.index');
    }

    public function edit($salarytax)
    {
        $salarytax = SalaryTax::find($salarytax);

        if(\Auth::user()->can('edit allowance'))
        {
            if($salarytax->created_by == \Auth::user()->creatorId())
            {
                // FIX: use 'title' and pass the correct variable to the view
                $salary_tax = SalaryTax::where('created_by', \Auth::user()->creatorId())
                                ->get()->pluck('title', 'id');

                return view('salarytax.edit', compact('salarytax', 'salary_tax'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, SalaryTax $salarytax)
    {
        if(\Auth::user()->can('edit allowance'))
        {
            if($salarytax->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title'  => 'required',
                        'amount' => 'required',
                    ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $salarytax->title  = $request->title;
                $salarytax->amount = $request->amount;
                $salarytax->save();

                return redirect()->back()->with('success', __('SalaryTax successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(SalaryTax $salarytax)
    {
        if(\Auth::user()->can('delete allowance'))
        {
            if($salarytax->created_by == \Auth::user()->creatorId())
            {
                $salarytax->delete();
                return redirect()->back()->with('success', __('SalaryTax successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

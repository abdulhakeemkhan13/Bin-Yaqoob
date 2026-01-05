<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Transfer;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TransferController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage transfer'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $emp       = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $transfers = Transfer::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', '=', $emp->id)->with('employee','branch','department')->get();
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $transfers = Transfer::where($column, '=', $ownerId)->with('employee','branch','department')->get();
            }

            return view('transfer.index', compact('transfers'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

      public function create()
    {
        if(\Auth::user()->can('create transfer'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $departments = Department::where($column, $ownerId)->get()->pluck('name', 'id');
            $branches    = Branch::where($column, $ownerId)->get()->pluck('name', 'id');
            $employees   = Employee::where($column, $ownerId)
                            ->get()
                            ->pluck('name', 'id');

            return view('transfer.create', compact('employees', 'departments', 'branches'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('create transfer'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'branch_id' => 'required',
                                   'department_id' => 'required',
                                   'transfer_date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error' => $messages->first()], 422);
            }

            $transfer                = new Transfer();
            $transfer->employee_id   = $request->employee_id;
            $transfer->branch_id     = $request->branch_id;
            $transfer->department_id = $request->department_id;
            $transfer->transfer_date = $request->transfer_date;
            $transfer->description   = $request->description;
            $transfer->created_by    = \Auth::user()->creatorId();
            $transfer->owned_by    = \Auth::user()->ownedId();
            $transfer->save();
            if($transfer->employee_id){
                $emp = Employee::find($transfer->employee_id);
                $emp->termination_date = $transfer->transfer_date;
                $emp->save();
            }            
            $userarr = array_filter([
                \Auth::user()->id,
                $transfer->employee->report_to,
                $transfer->employee->id,
            ]);            
            // Remove empty values
            $userarr = array_filter ($userarr, function ($value) {
                return !is_null($value) && $value !== ''; 
            });
            $dataarr = [
                "updated_by" => Auth::user()->id,
                "data_id" => $transfer->id,
                "name" => @$transfer->employee->name,
            ];
            foreach($userarr as $key => $notifyto){
                Utility::makeNotification($notifyto,'transfer',$dataarr,$transfer->id,'get transferd by',\Auth::user()->name);
            }
            $setings = Utility::settings();
            if($setings['transfer_sent'] == 1)
            {
                $employee             = Employee::find($transfer->employee_id);
                $branch               = Branch::find($transfer->branch_id);
                $department           = Department::find($transfer->department_id);
                $transfer->name       = $employee->name;
                $transfer->email      = $employee->email;
                $transfer->branch     = $branch->name;
                $transfer->department = $department->name;

                $transferArr = [
                    'transfer_name'=>$employee->name,
                    'transfer_email'=>$employee->email,
                    'transfer_date'=>$transfer->transfer_date,
                    'transfer_department'=>$transfer->department,
                    'transfer_branch'=>$transfer->branch,
                    'transfer_description'=>$transfer->description,
                ];

                $resp = Utility::sendEmailTemplate('transfer_sent', [$employee->id => $employee->email], $transferArr);
                Utility::makeActivityLog(\Auth::user()->id,'Transfer',$transfer->id,'Create Transfer',$employee->name);
            }
            Utility::makeActivityLog(\Auth::user()->id,'Transfer',$transfer->id,'Create Transfer',$transfer->name);
            \DB::commit();
            $html = view('transfer.appendrow', compact('transfer'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "transfer-table",
                'action' => 'add',
                'row_id' => $transfer->id,
            ];
            return response()->json(['success' => true, 'message' => __('Transfer  successfully created.'), 'data' => $data]);
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Transfer $transfer)
    {
        return redirect()->route('transfer.index');
    }



    public function edit(Transfer $transfer)
    {
        if(\Auth::user()->can('edit transfer'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $departments = Department::where($column,$ownerId)->get()->pluck('name', 'id');
            $branches    = Branch::where($column,$ownerId)->get()->pluck('name', 'id');
            $employees   = Employee::where($column,$ownerId)->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get()->pluck('name', 'id');
            if($transfer->created_by == \Auth::user()->creatorId())
            {
                return view('transfer.edit', compact('transfer', 'employees', 'departments', 'branches'));
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

    public function update(Request $request, Transfer $transfer)
    {
        \DB::beginTransaction();
        try {
            if(\Auth::user()->can('edit transfer'))
            {
                if($transfer->created_by == \Auth::user()->creatorId())
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'employee_id' => 'required',
                                           'branch_id' => 'required',
                                           'department_id' => 'required',
                                           'transfer_date' => 'required',
                                       ]
                    );
                    if($validator->fails())
                    {
                        $messages = $validator->getMessageBag();

                        return response()->json(['error' => $messages->first()], 422);
                    }

                    $transfer->employee_id   = $request->employee_id;
                    $transfer->branch_id     = $request->branch_id;
                    $transfer->department_id = $request->department_id;
                    $transfer->transfer_date = $request->transfer_date;
                    $transfer->description   = $request->description;
                    $transfer->save();
                    if($transfer->employee_id){
                        $emp = Employee::find($transfer->employee_id);
                        $emp->termination_date = $transfer->transfer_date;
                        $emp->save();
                    }
                    \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Transfer',$transfer->id,'Update Transfer',$transfer->name);
                $html = view('transfer.appendrow', compact('transfer'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "transfer-table",
                    'action' => 'edit',
                    'row_id' => $transfer->id,
                ];
                return response()->json(['success' => true, 'message' => __('Transfer successfully updated.'), 'data' => $data]);
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
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Transfer $transfer)
    {
        if(\Auth::user()->can('delete transfer'))
        {
            if($transfer->created_by == \Auth::user()->creatorId())
            {
                Utility::makeActivityLog(\Auth::user()->id,'Transfer',$transfer->id,'Delete Transfer',$transfer->name);
                $transfer->delete();

                return redirect()->route('transfer.index')->with('success', __('Transfer successfully deleted.'));
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

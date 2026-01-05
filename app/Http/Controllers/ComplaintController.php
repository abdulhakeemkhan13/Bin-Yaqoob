<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ComplaintController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage complaint'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $emp        = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $complaints = Complaint::where('complaint_from', '=', $emp->id)->with(['complaintFrom'])->get();
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $complaints = Complaint::where($column, '=', $ownerId)->with(['complaintFrom'])->get();
            }

            return view('complaint.index', compact('complaints'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create complaint'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('user_id', '!=', $user->id) ->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());}) ->get()->pluck('name', 'id');
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees = Employee::where($column, $ownerId) ->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());}) ->get()->pluck('name', 'id');
            }


            return view('complaint.create', compact('employees', 'current_employee'));
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
        if(\Auth::user()->can('create complaint'))
        {
            if(\Auth::user()->type != 'Employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'complaint_from' => 'required',
                                   ]
                );
            }

            $validator = \Validator::make(
                $request->all(), [
                                   'complaint_against' => 'required',
                                   'title' => 'required',
                                   'complaint_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error' => $messages->first()], 422);
            }
            $complaint = new Complaint();
            if(\Auth::user()->type == 'Employee')
            {
                $emp                       = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $complaint->complaint_from = $emp->id;
            }
            else
            {
                $complaint->complaint_from = $request->complaint_from;
            }
            $complaint->complaint_against = $request->complaint_against;
            $complaint->title             = $request->title;
            $complaint->complaint_date    = $request->complaint_date;
            $complaint->description       = $request->description;
            $complaint->created_by        = \Auth::user()->creatorId();
            $complaint->owned_by        = \Auth::user()->ownedId();
            $complaint->save();
            if($complaint->complaint_against){
                $emp = Employee::find($complaint->complaint_against);
                $emp->termination_date = $complaint->complaint_date;
                $emp->save();
            }
            $userarr = array_filter(
                [
                    \Auth::user()->id,
                    $complaint->employee->report_to ?? null,
                    $complaint->employee->id ?? null,
                ],
                function ($value) {
                    return !is_null($value); 
                }
            );
            // Remove empty values
            $userarr = array_filter ($userarr, function ($value) {
                return !is_null($value) && $value !== ''; 
            });
            $dataarr = [
                "updated_by" => Auth::user()->id,
                "data_id" => $complaint->id,
                "name" => @$complaint->employee->name,
            ];
            foreach($userarr as $key => $notifyto){
                Utility::makeNotification($notifyto,'complaint',$dataarr,$complaint->id,'Complained By',\Auth::user()->name);
            }


            // Send Email
            $setings = Utility::settings();
            if($setings['complaint_resent'] == 1)
            {

                $employee         = Employee::find($complaint->complaint_against);
                $complaintArr = [

                    'complaint_name'=> $employee->name,
                    'complaint_title' => $complaint->title,
                    'complaint_against' =>  $complaint->complaint_against,
                    'complaint_date' => $complaint->complaint_date,
                    'complaint_description' => $complaint->description,

                ];


                $resp = Utility::sendEmailTemplate('complaint_resent', [$employee->id => $employee->email], $complaintArr);
                Utility::makeActivityLog(\Auth::user()->id,'Complaint',$complaint->id,'Create Complaint',$employee->name);
            }
            Utility::makeActivityLog(\Auth::user()->id,'Complaint',$complaint->id,'Create Complaint',$complaint->title);
            \DB::commit();
            $html = view('complaint.appendrow', compact('complaint'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "complaint-table",
                'action' => 'add',
                'row_id' => $complaint->id,
            ];
            return response()->json(['success' => true, 'message' => __('Complaint  successfully created.'), 'data' => $data]);
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

    public function show(Complaint $complaint)
    {
        return redirect()->route('complaint.index');
    }

    public function edit($complaint)
    {
        $complaint = Complaint::find($complaint);
        if(\Auth::user()->can('edit complaint'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('user_id', '!=', $user->id)
                    ->where(function($query) {
                        $query->whereNull('termination_date')
                            ->orWhere('termination_date', '>', now());
                    })
                    ->get()->pluck('name', 'id');
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees = Employee::where($column, $ownerId)
                    ->where(function($query) {
                        $query->whereNull('termination_date')
                            ->orWhere('termination_date', '>', now());
                    })
                    ->get()->pluck('name', 'id');
            }
            if($complaint->created_by == \Auth::user()->creatorId())
            {
                return view('complaint.edit', compact('complaint', 'employees', 'current_employee'));
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

    public function update(Request $request, Complaint $complaint)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit complaint'))
        {
            if($complaint->created_by == \Auth::user()->creatorId())
            {
                if(\Auth::user()->type != 'Employee')
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'complaint_from' => 'required',
                                       ]
                    );
                }

                $validator = \Validator::make(
                    $request->all(), [

                                       'complaint_against' => 'required',
                                       'title' => 'required',
                                       'complaint_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return response()->json(['error' => $messages->first()], 422);
                }

                if(\Auth::user()->type == 'Employee')
                {
                    $emp                       = Employee::where('user_id', '=', \Auth::user()->id)->first();
                    $complaint->complaint_from = $emp->id;
                }
                else
                {
                    $complaint->complaint_from = $request->complaint_from;
                }
                $complaint->complaint_against = $request->complaint_against;
                $complaint->title             = $request->title;
                $complaint->complaint_date    = $request->complaint_date;
                $complaint->description       = $request->description;
                $complaint->save();
                if($complaint->complaint_against){
                    $emp = Employee::find($complaint->complaint_against);
                    $emp->termination_date = $complaint->complaint_date;
                    $emp->save();
                }
                Utility::makeActivityLog(\Auth::user()->id,'Complaint',$complaint->id,'Update Complaint',$complaint->title);
                \DB::commit();
                $html = view('complaint.appendrow', compact('complaint'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "complaint-table",
                    'action' => 'edit',
                    'row_id' => $complaint->id,
                ];
                return response()->json(['success' => true, 'message' => __('Complaint successfully updated.'), 'data' => $data]);
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

    public function destroy(Complaint $complaint)
    {
        if(\Auth::user()->can('delete complaint'))
        {
            if($complaint->created_by == \Auth::user()->creatorId())
            {
                // log will be here
                Utility::makeActivityLog(\Auth::user()->id,'Complaint',$complaint->id,'Delete Complaint',$complaint->title);
                $complaint->delete();

                return redirect()->route('complaint.index')->with('success', __('Complaint successfully deleted.'));
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
}

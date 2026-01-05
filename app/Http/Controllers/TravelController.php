<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Travel;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class TravelController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage travel'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $emp     = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $travels = Travel::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', '=', $emp->id)->with('employee')->get();
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $travels = Travel::where($column, '=', $ownerId)->with('employee')->get();
            }

            return view('travel.index', compact('travels'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create travel'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $employees = Employee::where($column, $ownerId)->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get()->pluck('name', 'id');

            return view('travel.create', compact('employees'));
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
            if(!\Auth::user()->can('create travel'))
            {
                if($request->ajax())
                {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            
            $validator = \Validator::make(
                $request->all(), [
                    'employee_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'purpose_of_visit' => 'required',
                    'place_of_visit' => 'required',
                ]
            );
            
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                if($request->ajax())
                {
                    return response()->json(['error' => $messages->first()], 400);
                }
                return redirect()->back()->with('error', $messages->first());
            }
            
            $travel = new Travel();
            $travel->employee_id = $request->employee_id;
            $travel->start_date = $request->start_date;
            $travel->end_date = $request->end_date;
            $travel->purpose_of_visit = $request->purpose_of_visit;
            $travel->place_of_visit = $request->place_of_visit;
            $travel->description = $request->description;
            $travel->created_by = \Auth::user()->creatorId();
            $travel->owned_by = \Auth::user()->ownedId();
            $travel->save();

            if($travel->employee_id){
                $emp = Employee::find($travel->employee_id);
                $emp->termination_date = $travel->end_date;
                $emp->save();
            }
            
            $userarr = array_filter([
                \Auth::user()->id,
                $travel->employee->report_to,
                $travel->employee->id,
            ]);

            // Remove empty values
            $userarr = array_filter ($userarr, function ($value) {
                return !is_null($value) && $value !== ''; 
            });
            
            $dataarr = [
                "updated_by" => Auth::user()->id,
                "data_id" => $travel->id,
                "name" => @$travel->employee->name,
            ];
            
            foreach($userarr as $key => $notifyto){
                Utility::makeNotification($notifyto, 'travel', $dataarr, $travel->id, 'Travel to '.$travel->place_of_visit .' for the purpose of '.$travel->purpose_of_visit, \Auth::user()->name);
            }
            
            $setings = Utility::settings();
            if($setings['trip_sent'] == 1)
            {
                $employee = Employee::find($travel->employee_id);
                
                $tripArr = [
                    'trip_name' => $employee->name,
                    'purpose_of_visit' => $travel->purpose_of_visit,
                    'start_date' => $travel->start_date,
                    'end_date' => $travel->end_date,
                    'place_of_visit' => $travel->place_of_visit,
                    'trip_description' => $travel->description,
                ];
                
                $resp = Utility::sendEmailTemplate('trip_sent', [$employee->id => $employee->email], $tripArr);
                Utility::makeActivityLog(\Auth::user()->id, 'Travel', $travel->id, 'Create Travel', $employee->name);
                
                \DB::commit();
                
                if($request->ajax())
                {
                    $html = view('travel.appendrow', compact('travel'))->render();
                    $data = [
                        'datarow' => $html,
                        'table_id' => "travel-table",
                        'action' => 'add',
                        'row_id' => $travel->id,
                    ];
                    return response()->json(['success' => true, 'message' => __('Travel successfully created.'), 'data' => $data]);
                }
                
                return redirect()->route('travel.index')->with('success', __('Travel successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            
            Utility::makeActivityLog(\Auth::user()->id, 'Travel', $travel->id, 'Create Travel', $travel->purpose_of_visit);
            \DB::commit();
            
            if($request->ajax())
            {
                $html = view('travel.appendrow', compact('travel'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "travel-table",
                    'action' => 'add',
                    'row_id' => $travel->id,
                ];
                return response()->json(['success' => true, 'message' => __('Travel successfully created.'), 'data' => $data]);
            }
            
            return redirect()->route('travel.index')->with('success', __('Travel successfully created.'));
        } catch (\Exception $e) {
            \DB::rollback();
            if($request->ajax())
            {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(Travel $travel)
    {
        return redirect()->route('travel.index');
    }

    public function edit(Travel $travel)
    {

        if(\Auth::user()->can('edit travel'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $employees = Employee::where($column, $ownerId)->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get()->pluck('name', 'id');
            if($travel->created_by == \Auth::user()->creatorId())
            {
                return view('travel.edit', compact('travel', 'employees'));
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

    public function update(Request $request, Travel $travel)
    {
        \DB::beginTransaction();
        try {
            if(!\Auth::user()->can('edit travel'))
            {
                if($request->ajax())
                {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            
            if($travel->created_by != \Auth::user()->creatorId())
            {
                if($request->ajax())
                {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            
            $validator = \Validator::make(
                $request->all(), [
                    'employee_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'purpose_of_visit' => 'required',
                    'place_of_visit' => 'required',
                ]
            );
            
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                if($request->ajax())
                {
                    return response()->json(['error' => $messages->first()], 400);
                }
                return redirect()->back()->with('error', $messages->first());
            }
            
            $travel->employee_id = $request->employee_id;
            $travel->start_date = $request->start_date;
            $travel->end_date = $request->end_date;
            $travel->purpose_of_visit = $request->purpose_of_visit;
            $travel->place_of_visit = $request->place_of_visit;
            $travel->description = $request->description;
            $travel->save();
            if($travel->employee_id){
                $emp = Employee::find($travel->employee_id);
                $emp->termination_date = $travel->end_date;
                $emp->save();
            }
            
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id, 'Travel', $travel->id, 'Update Travel', $travel->purpose_of_visit);
            
            if($request->ajax())
            {
                $html = view('travel.appendrow', compact('travel'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "travel-table",
                    'action' => 'edit',
                    'row_id' => $travel->id,
                ];
                return response()->json(['success' => true, 'message' => __('Travel successfully updated.'), 'data' => $data]);
            }
            
            return redirect()->route('travel.index')->with('success', __('Travel successfully updated.'));
        } catch (\Exception $e) {
            \DB::rollback();
            if($request->ajax())
            {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Travel $travel)
    {
        if(\Auth::user()->can('delete travel'))
        {
            if($travel->created_by == \Auth::user()->creatorId())
            {
                // log will be here
                Utility::makeActivityLog(\Auth::user()->id,'Travel',$travel->id,'Delete Travel',$travel->purpose_of_visit);
                $travel->delete();

                return redirect()->route('travel.index')->with('success', __('Travel successfully deleted.'));
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

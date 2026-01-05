<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Utility;
use App\Models\Warning;
use App\Models\WorkFlow;
use App\Models\Notification;
use App\Models\WorkFlowAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WarningController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage warning'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $emp      = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $warnings = Warning::where('warning_by', '=', $emp->id)->with(['warningTo'])->get();
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $warnings = Warning::where($column, '=', $ownerId)->with(['warningTo'])->get();
            }

            return view('warning.index', compact('warnings'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create warning'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('user_id', '!=', $user->id) ->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());}) ->get()->pluck('name', 'id');;;;;
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where($column, $ownerId) ->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());}) ->get()->pluck('name', 'id');;
            }

            return view('warning.create', compact('employees', 'current_employee'));
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
            if(!\Auth::user()->can('create warning')) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            
            // Validation
            $validationRules = [
                'warning_to' => 'required',
                'subject' => 'required',
                'warning_date' => 'required',
            ];
            
            if(\Auth::user()->type != 'Employee') {
                $validationRules['warning_by'] = 'required';
            }
            
            $validator = \Validator::make($request->all(), $validationRules);
            
            if($validator->fails()) {
                $messages = $validator->getMessageBag();
                if($request->ajax()) {
                    \DB::rollback();
                    return response()->json(['success' => false, 'message' => $messages->first()], 422);
                }
                return redirect()->back()->with('error', $messages->first());
            }
            
            // Create warning
            $warning = new Warning();
            
            if(\Auth::user()->type == 'Employee') {
                $emp = Employee::where('user_id', '=', \Auth::user()->id)->first();
                if(!$emp) {
                    \DB::rollback();
                    if($request->ajax()) {
                        return response()->json(['success' => false, 'message' => __('Employee record not found.')], 404);
                    }
                    return redirect()->back()->with('error', __('Employee record not found.'));
                }
                $warning->warning_by = $emp->id;
            } else {
                $warning->warning_by = $request->warning_by;
            }
            
            $warning->warning_to = $request->warning_to;
            $warning->subject = $request->subject;
            $warning->warning_date = $request->warning_date;
            $warning->description = $request->description;
            $warning->created_by = \Auth::user()->creatorId();
            $warning->owned_by = \Auth::user()->ownedId();
            $warning->save();
            
            if($warning->warning_to) {
                $emp = Employee::find($warning->warning_to);
                if($emp) {
                    $emp->termination_date = $warning->warning_date;
                    $emp->save();
                }
            }
            
            // Get the warning with relationships for notifications
            $warning = Warning::with('warningTo', 'warningBy')->where('id', $warning->id)->first();
            
            // Prepare notification recipients
            $userarr = array_filter([
                \Auth::user()->id,
                $warning->warningTo->report_to ?? null,
                $warning->warningTo->id ?? null,
            ]);

            // Remove empty values
            $userarr = array_filter($userarr, function ($value) {
                return !is_null($value) && $value !== '';
            });
            
            // Prepare notification data
            $dataarr = [
                "updated_by" => Auth::user()->id,
                "data_id" => $warning->id,
                "name" => $warning->warningTo->name ?? __('Unnamed'),
            ];
            
            // Send notifications
            foreach($userarr as $notifyto) {
                Utility::makeNotification($notifyto, 'warning', $dataarr, $warning->id, 'Warning By', \Auth::user()->name);
            }
            
            // Send email notification
            $setings = Utility::settings();
            if($setings['warning_sent'] == 1) {
                $employee = Employee::find($warning->warning_to);
                if($employee && $employee->email) {
                    $warningArr = [
                        'employee_warning_name' => $employee->name,
                        'warning_subject' => $warning->subject,
                        'warning_description' => $warning->description,
                    ];
                    
                    $resp = Utility::sendEmailTemplate('warning_sent', [$employee->id => $employee->email], $warningArr);
                    \DB::commit();
                    
                    Utility::makeActivityLog(\Auth::user()->id, 'Warning', $warning->id, 'Create Warning', $employee->name);
                    
                    if($request->ajax()) {
                        $html = view('warning.appendrow', compact('warning'))->render();
                        $data = [
                            'datarow' => $html,
                            'table_id' => "warning-table",
                            'action' => 'add',
                            'row_id' => $warning->id,
                        ];
                        return response()->json(['success' => true, 'message' => __('Warning successfully created.'), 'data' => $data]);
                    }
                    
                    return redirect()->route('warning.index')->with('success', __('Warning successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                }
            }
            
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id, 'Warning', $warning->id, 'Create Warning', $warning->subject);
            
            if($request->ajax()) {
                $html = view('warning.appendrow', compact('warning'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "warning-table",
                    'action' => 'add',
                    'row_id' => $warning->id,
                ];
                return response()->json(['success' => true, 'message' => __('Warning successfully created.'), 'data' => $data]);
            }
            
            return redirect()->route('warning.index')->with('success', __('Warning successfully created.'));
            
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Warning creation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('An error occurred: ') . $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
        }
    }

    public function show(Warning $warning)
    {
        return redirect()->route('warning.index');
    }

    public function edit(Warning $warning)
    {

        if(\Auth::user()->can('edit warning'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('user_id', '!=', $user->id)->   where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());}) ->get()->pluck('name', 'id');
            }
            else
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $employees        = Employee::where($column, $ownerId) ->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());}) ->get()->pluck('name', 'id');
            }
            if($warning->created_by == \Auth::user()->creatorId())
            {
                return view('warning.edit', compact('warning', 'employees', 'current_employee'));
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

    public function update(Request $request, Warning $warning)
    {
        // dd($request->all());
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit warning'))
        {
            if($warning->created_by == \Auth::user()->creatorId())
            {
                if(\Auth::user()->type != 'employee')
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'warning_by' => 'required',
                                       ]
                    );
                }

                $validator = \Validator::make(
                    $request->all(), [
                                       'warning_to' => 'required',
                                       'subject' => 'required',
                                       'warning_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if(\Auth::user()->type == 'Employee')
                {
                    $emp                 = Employee::where('user_id', '=', \Auth::user()->id)->first();
                    $warning->warning_by = $emp->id;
                }
                else
                {
                    $warning->warning_by = $request->warning_by;
                }

                $warning->warning_to   = $request->warning_to;
                $warning->subject      = $request->subject;
                $warning->warning_date = $request->warning_date;
                $warning->description  = $request->description;
                $warning->save();
                if($warning->warning_to) {
                    $emp = Employee::find($warning->warning_to);
                    if($emp) {
                        $emp->termination_date = $warning->warning_date;
                        $emp->save();
                    }
                }
                \DB::commit();
                // log will be here
                Utility::makeActivityLog(\Auth::user()->id,'Warning',$warning->id,'Update Warning',$warning->subject);
                $html = view('warning.appendrow', compact('warning'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "warning-table",
                    'action' => 'edit',
                    'row_id' => $warning->id,
                ];
                return response()->json(['success' => true, 'message' => __('Warning successfully updated.'), 'data' => $data]);;
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
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e);
        }        
    }

    public function destroy(Warning $warning)
    {
        if(\Auth::user()->can('delete warning'))
        {
            if($warning->created_by == \Auth::user()->creatorId())
            {
                // log will be here
                Utility::makeActivityLog(\Auth::user()->id,'Warning',$warning->id,'Delete Warning',$warning->subject);
                $warning->delete();

                return redirect()->route('warning.index')->with('success', __('Warning successfully deleted.'));
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

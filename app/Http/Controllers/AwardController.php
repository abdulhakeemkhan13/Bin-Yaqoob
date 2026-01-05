<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\AwardType;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AwardController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if($user->can('manage award'))
        {
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';

            $employees  = Employee::where($column, '=', $ownerId)->get();
            $awardtypes = AwardType::where($column, '=', $ownerId)->get();

            if(Auth::user()->type == 'Employee')
            {
                $emp    = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $awards = Award::where('employee_id', '=', $emp->id)->with(['employee' ,'awardType'])->get();
            }
            else
            {
                $awards = Award::where($column, '=', $ownerId)->with(['employee' ,'awardType'])->get();
            }

            return view('award.index', compact('awards', 'employees', 'awardtypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

     public function create()
    {
        if(\Auth::user()->can('create award'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $employees  = Employee::where($column, '=',$ownerId)->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now());})->get()->pluck('name', 'id');
            $awardtypes = AwardType::where($column, '=',$ownerId)->get()->pluck('name', 'id');
            $employees  = Employee::where($column, '=',$ownerId)->get()->pluck('name', 'id');

            return view('award.create', compact('employees', 'awardtypes'));
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
        if(\Auth::user()->can('create award'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'award_type' => 'required',
                                   'date' => 'required',
                                   'gift' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $award              = new Award();
            $award->employee_id = $request->employee_id;
            $award->award_type  = $request->award_type;
            $award->date        = $request->date;
            $award->gift        = $request->gift;
            $award->description = $request->description;
            $award->created_by  = \Auth::user()->creatorId();
            $award->owned_by  = \Auth::user()->ownedId();
            $award->save();
            if($award->employee_id){
                $emp = Employee::find($award->employee_id);
                $emp->termination_date = $award->date;
                $emp->save();
            }

            $userarr = array_filter([
                \Auth::user()->id,
                $award->employee->report_to,
                $award->employee->id,
            ]);            
            // Remove empty values
            $userarr = array_filter ($userarr, function ($value) {
                return !is_null($value) && $value !== ''; 
            });
            $dataarr = [
                "updated_by" => Auth::user()->id,
                "data_id" => $award->id,
                "name" => @$award->employee->name,
            ];
            foreach($userarr as $key => $notifyto){
                Utility::makeNotification($notifyto,'award',$dataarr,$award->id,'Awarded a gift by',\Auth::user()->name);
            }
            //For Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());

            $emp = Employee::find($request->employee_id);
            $award = AwardType::find($request->award_type);
            $awardNotificationArr = [
                'award_name' =>  $award->name,
                'employee_name' =>  $emp->name,
                'award_date' =>  $request->date,
            ];
            //Slack Notification
            if(isset($setting['award_notification']) && $setting['award_notification'] ==1)
            {
                Utility::send_slack_msg('new_award', $awardNotificationArr);
            }
            //Telegram Notification
            if(isset($setting['telegram_award_notification']) && $setting['telegram_award_notification'] ==1)
            {
                Utility::send_telegram_msg('new_award', $awardNotificationArr);
            }


            // Send Email
            $setings = Utility::settings();

            if($setings['new_award'] == 1)
            {
                $employee     = Employee::find($request->employee_id);
                $awardArr = [
                    'award_name' => $employee->name,
                    'award_email' => $employee->email,
                ];

                $resp = Utility::sendEmailTemplate('new_award', [$employee->id => $employee->email], $awardArr);
                \DB::commit();


            }
            //webhook
            $module ='New Award';
            $webhook =  Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($award);
                $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                if($status == true)
                {
                }
                else
                {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }
            Utility::makeActivityLog(\Auth::user()->id,'Award',$award->id,'Create Award',$award->gift);
            \DB::commit();
            $html = view('award.appendrow', compact('award'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "award-table",
                'action' => 'add',
                'row_id' => $award->id,
            ];
            return response()->json(['success' => true, 'message' => __('Award successfully created.'), 'data' => $data]);
            // return redirect()->route('award.index')->with('success', __('Award successfully created.') . ((!empty ($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
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

    public function show(Award $award)
    {
        return redirect()->route('award.index');
    }

    public function edit(Award $award)
    {
        if(\Auth::user()->can('edit award'))
        {
            if($award->created_by == \Auth::user()->creatorId())
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $employees  = Employee::where($column, '=',$ownerId)->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get()->pluck('name', 'id');;                 
                $awardtypes = AwardType::where($column, '=',$ownerId)->get()->pluck('name', 'id');

                return view('award.edit', compact('award', 'awardtypes', 'employees'));
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

    public function update(Request $request, Award $award)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit award'))
        {
            if($award->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'employee_id' => 'required',
                                       'award_type' => 'required',
                                       'date' => 'required',
                                       'gift' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $award->employee_id = $request->employee_id;
                $award->award_type  = $request->award_type;
                $award->date        = $request->date;
                $award->gift        = $request->gift;
                $award->description = $request->description;
                $award->save();
                if($award->employee_id){
                    $emp = Employee::find($award->employee_id);
                    $emp->termination_date = $award->date;
                    $emp->save();
                }
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Award',$award->id,'Update Award',$award->gift);
                $html = view('award.appendrow', compact('award'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "award-table",
                    'action' => 'edit',
                    'row_id' => $award->id,
                ];
                return response()->json(['success' => true, 'message' => __('Award successfully updated.'), 'data' => $data]);
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

    public function destroy(Award $award)
    {
        if(\Auth::user()->can('delete award'))
        {
            if($award->created_by == \Auth::user()->creatorId())
            {
                Utility::makeActivityLog(\Auth::user()->id,'Award',$award->id,'Delete Award',$award->gift);
                $award->delete();

                return redirect()->route('award.index')->with('success', __('Award successfully deleted.'));
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

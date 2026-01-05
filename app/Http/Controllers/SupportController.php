<?php

namespace App\Http\Controllers;

use App\Models\Resignation;
use App\Models\Employee;
use App\Models\Support;
use App\Models\SupportReply;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $supports = Support::where('created_by', \Auth::user()->creatorId())->with(['createdBy','assignUser'])->get();
            $countTicket      = Support::where('created_by', '=', \Auth::user()->creatorId())->count();
            $countOpenTicket  = Support::where('status', '=', 'open')->where('created_by', '=', \Auth::user()->creatorId())->count();
            $countonholdTicket  = Support::where('status', '=', 'on hold')->where('created_by', '=', \Auth::user()->creatorId())->count();
            $countCloseTicket = Support::where('status', '=', 'close')->where('created_by', '=', \Auth::user()->creatorId())->count();
            return view('support.index', compact('supports','countTicket','countOpenTicket','countonholdTicket','countCloseTicket'));
        }
        else {

            $supports = Support::where('user', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->with(['createdBy','assignUser'])->get();
            $countTicket      = Support::where('user', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->count();
            $countOpenTicket  = Support::where('status', '=', 'open')->where('user', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->count();
            $countonholdTicket  = Support::where('status', '=', 'on hold')->where('user', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->count();
            $countCloseTicket = Support::where('status', '=', 'close')->where('user', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->count();
            return view('support.index', compact('supports','countTicket','countOpenTicket','countonholdTicket','countCloseTicket'));
        }

    }

    public function create()
    {
        $priority = [
            __('Low'),
            __('Medium'),
            __('High'),
            __('Critical'),
        ];
        //$status = Support::$status;
        $status = Support::status();
        $users = User::select('users.*')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->where('users.created_by', \Auth::user()->creatorId())
            ->where('users.type', '!=', 'client')
            ->where(function($query) {
                $query->whereNull('employees.termination_date')
                    ->orWhere('employees.termination_date', '>', now());
            })
            ->get()
            ->pluck('name', 'id');

        return view('support.create', compact('priority', 'users','status'));
    }


    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        $validator = \Validator::make(
            $request->all(), [
                               'subject' => 'required',
                               'priority' => 'required',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $support              = new Support();
        $support->subject     = $request->subject;
        $support->priority    = $request->priority;
        $support->end_date    = $request->end_date;
        $support->ticket_code = date('hms');
        $support->status      = 'Open';

        if(!empty($request->attachment))
        {
            //storage limit
            $image_size = $request->file('attachment')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);

            if($result==1)
            {
                if($support->attachment)
                {
                    $path = storage_path('uploads/supports' . $support->attachment);
                    if(file_exists($path))
                    {
                        \File::delete($path);
                    }
                }
                $fileName = time() . "_" . $request->attachment->getClientOriginalName();
                $support->attachment = $fileName;
                $dir        = 'uploads/supports';
                $path = Utility::upload_file($request,'attachment',$fileName,$dir,[]);
                if($path['flag']==0){
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }


        }

        $support->description    = $request->description;
        $support->created_by     = \Auth::user()->creatorId();
        $support->ticket_created = \Auth::user()->id;
        if(\Auth::user()->type == 'company')
        {
            $support->user= $request->user;
        }
        else
        {
            $support->user = \Auth::user()->id;;
        }

        $support->save();
        
        $resignation = Resignation::where('employee_id','=',$request->user)->first();
                if($resignation){
                    $emp = Employee::find($resignation->employee_id);
                    $emp->termination_date = $resignation->resignation_date;
                    $emp->save();
                }

        //For Notification
        $setting  = Utility::settings(\Auth::user()->creatorId());
        $support_priority = \App\Models\Support::$priority[$support->priority];
        $user = User::find($support->user);
        $supportNotificationArr = [
            'support_priority' =>  $support_priority,
            'support_user_name' =>  $user->name,
        ];
        //Slack Notification
        if(isset($setting['support_notification']) && $setting['support_notification'] ==1)
        {
            Utility::send_slack_msg('new_support_ticket', $supportNotificationArr);
        }

        //Telegram Notification
        if(isset($setting['telegram_support_notification']) && $setting['telegram_support_notification'] ==1)
        {
            Utility::send_telegram_msg('new_support_ticket', $supportNotificationArr);
        }

        // send mail
        $id =!empty($request->user )? $request->user: \Auth::user()->id;
        $employee             = User::find($id);
        $support_priority = \App\Models\Support::$priority[$support->priority];
        $supportArr = [
            'support_name'=> $employee->name,
            'support_title' => $support->subject,
            'support_priority' =>  $support_priority,
            'support_end_date' => $support->end_date,
            'support_description' => $support->description,

        ];
        $resp = Utility::sendEmailTemplate('new_support_ticket', [$employee->id => $employee->email], $supportArr);

        //webhook
        $module ='New Support Ticket';
        $webhook=  Utility::webhookSetting($module);
        if($webhook)
        {
            $parameter = json_encode($support);
            $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
            if($status == true)
            {
                return redirect()->route('support.index')->with('success', __('Support successfully added.').((!empty ($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            else
            {
                return redirect()->back()->with('error', __('Webhook call failed.'));
            }
        }

        \DB::commit();
        $html = view('support.appendrow', compact('support'))->render();
        $data = [
            'datarow' => $html,
            'table_id' => "support-table",
            'action' => 'add',
            'row_id' => $support->id,
        ];
        return response()->json(['success' => true, 'message' => __('Support successfully created.'), 'data' => $data]);
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }


    }


    public function show(Support $support)
    {
        //
    }


    public function edit(Support $support)
    {
        $priority = [
            __('Low'),
            __('Medium'),
            __('High'),
            __('Critical'),
        ];
        //$status = Support::$status;
        $status = Support::status();
        $users = User::select('users.*')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->where('users.created_by', \Auth::user()->creatorId())
            ->where('users.type', '!=', 'client')
            ->where(function($query) {
                $query->whereNull('employees.termination_date')
                    ->orWhere('employees.termination_date', '>', now());
            })
            ->get()
            ->pluck('name', 'id');

        return view('support.edit', compact('priority', 'users', 'support','status'));
    }


    public function update(Request $request, Support $support)
    {
        \DB::beginTransaction();
        try {
        $validator = \Validator::make(
            $request->all(), [
                               'subject' => 'required',
                               'priority' => 'required',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $support->subject  = $request->subject;
        $support->user     = $request->user;
        $support->priority = $request->priority;
        $support->status  = $request->status;
        $support->end_date = $request->end_date;
        if(!empty($request->attachment))
        {
            //storage limit
            $file_path = '/uploads/supports/'.$support->attachment;
            $image_size = $request->file('attachment')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);

            if($result==1)
            {
                if($support->attachment)
                {
                    Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

                    $path = storage_path('uploads/supports' . $support->attachment);
                    if(file_exists($path))
                    {
                        \File::delete($path);
                    }
                }
                $fileName = time() . "_" . $request->attachment->getClientOriginalName();
                $support->attachment = $fileName;
                $dir        = 'uploads/supports';
                $path = Utility::upload_file($request,'attachment',$fileName,$dir,[]);
                if($path['flag']==0){
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }

        }
        $support->description = $request->description;

        if(\Auth::user()->type == 'company')
        {
            $support->user= $request->user;
        }
        else
        {
            $support->user = \Auth::user()->id;;
        }
        $support->save();

        $resignation = Resignation::where('employee_id','=',$request->user)->first();
                if($resignation){
                    $emp = Employee::find($resignation->employee_id);
                    $emp->termination_date = $resignation->resignation_date;
                    $emp->save();
                }

        \DB::commit();
        $html = view('support.appendrow', compact('support'))->render();
        $data = [
            'datarow' => $html,
            'table_id' => "support-table",
            'action' => 'edit',
            'row_id' => $support->id,
        ];
        return response()->json(['success' => true, 'message' => __('Support successfully updated.'), 'data' => $data]);
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }

    }


    public function destroy(Support $support)
    {
        $support->delete();
        if($support->attachment)
        {
            //storage limit
            $file_path = '/uploads/supports/'.$support->attachment;
            $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

            \File::delete(storage_path('uploads/supports/' . $support->attachment));
        }

        return redirect()->route('support.index')->with('success', __('Support successfully deleted.'));

    }

    public function reply($ids)
    {
        try {
            $id              = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Support Not Found.'));
        }
        $id      = \Crypt::decrypt($ids);
        $replyes = SupportReply::where('support_id', $id)->with(['users'])->get();
        $support = Support::with(['assignUser','createdBy'])->find($id);

        foreach($replyes as $reply)
        {
            $supportReply          = SupportReply::find($reply->id);
            $supportReply->is_read = 1;
            $supportReply->save();
        }

        return view('support.reply', compact('support', 'replyes'));
    }

    public function replyAnswer(Request $request, $id)
    {
        $supportReply              = new SupportReply();
        $supportReply->support_id  = $id;
        $supportReply->user        = \Auth::user()->id;
        $supportReply->description = $request->description;
        $supportReply->created_by  = \Auth::user()->creatorId();
        $supportReply->save();

        return redirect()->back()->with('success', __('Support reply successfully send.'));
    }

    public function grid()
    {

        if(\Auth::user()->type == 'company')
        {
            $supports = Support::where('created_by', \Auth::user()->creatorId())->with(['assignUser','createdBy'])->get();

            return view('support.grid', compact('supports'));
        }
        elseif(\Auth::user()->type == 'client')
        {
            // $supports = Support::where('user', \Auth::user()->id)->orWhere('ticket_created', \Auth::user()->id)->with(['assignUser','createdBy'])->get();
            $supports = Support::where('user', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->with(['createdBy','assignUser'])->get();

            return view('support.grid', compact('supports'));
        }
        else
        {

            // $supports = Support::where('user', \Auth::user()->id)->orWhere('ticket_created', \Auth::user()->id)->with(['assignUser','createdBy'])->get();
            $supports = Support::where('user', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->with(['createdBy','assignUser'])->get();

            return view('support.grid', compact('supports'));
        }

    }
}

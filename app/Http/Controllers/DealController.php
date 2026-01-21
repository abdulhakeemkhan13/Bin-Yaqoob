<?php

namespace App\Http\Controllers;

use App\Mail\SendDealEmail;
use App\Models\ActivityLog;
use App\Models\ClientDeal;
use App\Models\ClientPermission;
use App\Models\CustomField;
use App\Models\Deal;
use App\Models\DealCall;
use App\Models\DealDiscussion;
use App\Models\Employee;
use App\Models\DealEmail;
use App\Models\DealFile;
use App\Models\DealTask;
use App\Models\Label;
use App\Models\Pipeline;
use App\Models\ProductService;
use App\Models\Source;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserDeal;
use App\Models\WorkFlow;
use App\Models\WorkFlowAction;
use App\Models\Utility;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Exports\DealExport;
use App\Imports\DealImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\ContractOwner;
use App\Models\FrozenInstallmentPlan;
use App\Models\ReProject;
use App\Models\ReTower;
use App\Models\ReFloor;
use App\Models\ReUnit;
use Auth;

class DealController extends Controller
{
    /**
     * Display a listing of the redeal.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $usr = \Auth::user();

        if ($usr->can('manage deal')) {
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->first();
            }

            if (\Auth::user()->type == 'company') {
                $pipelines = Pipeline::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $pipelines = Pipeline::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }

            $labels = Label::where('created_by', '=', \Auth::user()->creatorId())->where('pipeline_id', $pipeline->id)->get();
            $query=Deal::query();
            if (!empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('price', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%');
            }
            if (!empty($request->date)) {
                if (count(explode('to', $request->date)) > 1) {
                    $date_range = explode(' to ', $request->date);
                    $query->whereBetween('date', $date_range);
                } elseif (!empty($request->date)) {
                    $date_range = [$request->date, $request->date];
                    $query->whereBetween('date', $date_range);
                }
            }
            if (!empty($request->date)) {
                $label = is_array($request->labels) ? $request->labels : explode(',', $request->labels);

                $query->where(function ($query) use ($label) {
                    foreach ($label as $label_1) {
                        $query->orWhere('labels', 'LIKE', '%'.$label_1.'%');
                    }
                });
            }
            if($usr->type == 'client')
            {

                $id_deals = $usr->clientDeals->pluck('id');
            } else {
                $id_deals = $usr->deals->pluck('id');
            }

            $deals = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->get();
            $curr_month = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereMonth('created_at', '=', date('m'))->get();
            $curr_week = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereBetween(
                'created_at',
                [
                    \Carbon\Carbon::now()->startOfWeek(),
                    \Carbon\Carbon::now()->endOfWeek(),
                ]
            )->get();
            $last_30days = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereDate('created_at', '>', \Carbon\Carbon::now()->subDays(30))->get();
            // Deal Summary
            $cnt_deal = [];
            $cnt_deal['total'] = Deal::getDealSummary($deals);
            $cnt_deal['this_month'] = Deal::getDealSummary($curr_month);
            $cnt_deal['this_week'] = Deal::getDealSummary($curr_week);
            $cnt_deal['last_30days'] = Deal::getDealSummary($last_30days);

            return view('deals.index', compact('pipelines', 'pipeline', 'cnt_deal','labels','query'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function deal_list()
    {
        $usr = \Auth::user();
        if ($usr->can('manage deal')) {
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->first();
            }

            $pipelines = Pipeline::where('created_by', '=', $usr->ownerId())->get()->pluck('name', 'id');

            if ($usr->type == 'client') {
                $id_deals = $usr->clientDeals->pluck('id');
            } else {
                $id_deals = $usr->deals->pluck('id');
            }

            $deals = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->get();
            $curr_month = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereMonth('created_at', '=', date('m'))->get();
            $curr_week = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereBetween(
                'created_at',
                [
                    \Carbon\Carbon::now()->startOfWeek(),
                    \Carbon\Carbon::now()->endOfWeek(),
                ]
            )->get();
            $last_30days = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereDate('created_at', '>', \Carbon\Carbon::now()->subDays(30))->get();

            // Deal Summary
            $cnt_deal = [];
            $cnt_deal['total'] = Deal::getDealSummary($deals);
            $cnt_deal['this_month'] = Deal::getDealSummary($curr_month);
            $cnt_deal['this_week'] = Deal::getDealSummary($curr_week);
            $cnt_deal['last_30days'] = Deal::getDealSummary($last_30days);

            // Deals
            if ($usr->type == 'client') {
                $deals = Deal::select('deals.*')->join('client_deals', 'client_deals.deal_id', '=', 'deals.id')->where('client_deals.client_id', '=', $usr->id)->where('deals.pipeline_id', '=', $pipeline->id)->orderBy('deals.order')->get();
            } else {
                $deals = Deal::select('deals.*')->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')->where('user_deals.user_id', '=', $usr->id)->where('deals.pipeline_id', '=', $pipeline->id)->orderBy('deals.order')->get();
            }

            return view('deals.list', compact('pipelines', 'pipeline', 'deals', 'cnt_deal'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new redeal.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('create deal')) {
    if (\Auth::user()->type == 'company') {
        $clients = User::where('created_by', '=', \Auth::user()->ownerId())
            ->where('type', 'client')
            ->where(function($query) {
                $query->whereDoesntHave('employee') // Clients without employee records
                      ->orWhereHas('employee', function($q) {
                          $q->whereNull('termination_date')
                            ->orWhere('termination_date', '>', now()->endOfMonth());
                      });
            })
            ->get()
            ->pluck('name', 'id');
    } else {
        $clients = User::where('owned_by', '=', \Auth::user()->ownedId())
            ->where('type', 'client')
            ->where(function($query) {
                $query->whereDoesntHave('employee') // Clients without employee records
                      ->orWhereHas('employee', function($q) {
                          $q->whereNull('termination_date')
                            ->orWhere('termination_date', '>', now()->endOfMonth());
                      });
            })
            ->get()
            ->pluck('name', 'id');
    }
    
    $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())
        ->where('module', '=', 'deal')
        ->get();
    
    // Get projects for unit selection
    $projects = \App\Models\ReProject::where('created_by', \Auth::user()->creatorId())
        ->pluck('name', 'id');
        
    return view('deals.create', compact('clients', 'customFields', 'projects'));
} else {
    return response()->json(['error' => __('Permission Denied.')], 401);
}
    }

    /**
     * Store a newly created redeal in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        $usr = \Auth::user();
        if ($usr->can('create deal')) {
            $countDeal = Deal::where('created_by', '=', $usr->ownerId())->count();
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Default Field Value
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $usr->ownerId())->first();
            }

            $stage = Stage::where('pipeline_id', '=', $pipeline->id)->first();
            // End Default Field Value

            // Check if stage are available or not in pipeline.
            if (empty($stage)) {
                return redirect()->back()->with('error', __('Please Create Stage for This Pipeline.'));
            } else {
                $deal = new Deal();
                $deal->name = $request->name;
                $deal->phone = $request->phone;
                $deal->email = $request->email;
                if (empty($request->price)) {
                    $deal->price = 0;
                } else {
                    $deal->price = $request->price;
                }
                $deal->pipeline_id = $pipeline->id;
                $deal->stage_id = $stage->id;
                $deal->status = 'Active';
                $deal->created_by = $usr->ownerId();
                $deal->owned_by = $usr->ownedId();

                // Unit details
                $deal->re_project_id = $request->re_project_id;
                $deal->re_tower_id = $request->re_tower_id;
                $deal->re_floor_id = $request->re_floor_id;
                $deal->re_unit_id = $request->re_unit_id;
                $deal->offered_price = $request->offered_price;
                $deal->discount = $request->discount;
                $deal->expected_closing_date = $request->expected_closing_date;

                // $deal->date = date('Y-m-d');
                $deal->save();

                // Reserve the unit if selected
                if ($deal->re_unit_id) {
                    $unit = \App\Models\ReUnit::find($deal->re_unit_id);
                    if ($unit && $unit->status == 'Available') {
                        $unit->status = 'Reserved';
                        $unit->save();
                    }
                }

                if($deal){
                    $emp = Employee::find($deal->user_id);
                    if($emp) {
                        $emp->termination_date = $deal->date;
                        $emp->save();
                    }
                }



                //send email - using deal email instead of client dropdown
                $clients = [];
                if ($deal->email) {
                    $clients = [$deal->email];
                }
                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                $userarr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];
                $userarr = array_filter ($userarr, function ($value) {
                return !is_null($value) && $value !== ''; 
            });
                $dArr = [
                    'deal_name' => $deal->name,
                    'deal_pipeline' => $pipeline->name,
                    'deal_stage' => $stage->name,
                    'deal_status' => $deal->status,
                    'deal_price' => $usr->priceFormat($deal->price),
                ];

                // Client deals removed - now using email field directly

                if ($usr->type == 'company') {
                    $usrDeals = [
                        $usr->id,

                    ];
                } else {
                    $usrDeals = [
                        $usr->id,
                        $usr->ownerId()
                    ];
                }

                $usr_Deals=[];
                $newStage = Stage::find($deal->stage_id);
                // WorkFlow get which is active
                $us_mail= 'false';
                $us_notify= 'false';
                $workflow = WorkFlow::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=','crm')->where('status',1)->first();
                // if($workflow){
                //     $workflowaction = WorkFlowAction::where('workflow_id',$workflow->id)->where('status',1)->where('level_id',2)->get();
                //     foreach(@$workflowaction as $action){
                //         $useraction = json_decode($action->assigned_users);
                //         if(strtolower('create-deal') == $action->node_id){
                //             // Pick that stage user assign or change on Deal
                //             if(@$useraction != ''){
                //                 $useraction = json_decode($useraction);
                //                 foreach($useraction as $anyaction){
                //                     // make new user array
                //                     if($anyaction->type == 'user'){
                //                         $usr_Deals[] = $anyaction->id;
                //                     }
                //                 }
                //             }
                //             //  if user assign on this stage then check for mail and notification conditions

                //             $raw_json = trim($action->applied_conditions, '"');
                //             $cleaned_json = stripslashes($raw_json);
                //             $applied_conditions = json_decode($cleaned_json, true);

                //             if (isset($applied_conditions['conditions']) && is_array($applied_conditions['conditions'])) {
                //                 $arr = [
                //                     'products' => 'App\Models\ProductService',
                //                     'sources' => 'App\Models\Source',
                //                     'label' => 'App\Models\Label',
                //                 ];
                //                 foreach ($applied_conditions['conditions'] as $conditionGroup) {
                //                     if (in_array($conditionGroup['action'], ['send_email', 'send_notification','send_approval'])) {
                //                         $query = Deal::where('id',$deal->id);
                //                         foreach ($conditionGroup['conditions'] as $condition) {
                //                             $field = $condition['field'];
                //                             $operator = $condition['operator'];
                //                             $value = $condition['value'];
                //                             if (Schema::hasColumn('deals', $field)) {
                //                                 if (array_key_exists($field, $arr)) {
                //                                     $a =$arr[$field]::where('name',$value)->pluck('id')->toArray();
                //                                     if(isset($a) && count($a) > 0){
                //                                         $query->where($field,$operator,$a);
                //                                     }
                //                                 }else{
                //                                     $query->where($field, $operator, $value);
                //                                 }
                //                             }
                //                         }
                //                         $result = $query->first();
                //                         if (!empty($result)) {
                //                             if ($conditionGroup['action'] === 'send_email') {
                //                                 $us_mail = 'true';
                //                             } elseif ($conditionGroup['action'] === 'send_notification') {
                //                                 $us_notify = 'true';
                //                             }
                //                             elseif ($conditionGroup['action'] === 'send_approval') {
                //                                 $us_approve = 'true';
                //                             }
                //                         }
                //                     }
                //                 }
                //             }

                //             if($us_mail == 'true'){
                //                 // email send
                //             }
                //             if($us_notify == 'true' || $us_approve == 'true'){
                //                 // notification generate
                //                 if(count($usr_Deals) > 0){
                //                     $usr_Deals[] =  Auth::user()->creatorId();
                //                     foreach($usr_Deals as $usrDeal1)
                //                     {
                //                         $data = [
                //                             "updated_by" => Auth::user()->id,
                //                             "data_id" => $deal->id,
                //                             "name" => $deal->name,
                //                         ];

                //                         if($us_notify == 'true'){
                //                             Utility::makeNotification($usrDeal1,'create_deal',$data,$deal->id,'create Deal');
                //                         }elseif($us_approve == 'true'){
                //                             Utility::makeNotification($usrDeal1,'approve_deal',$data,$deal->id,'For Approval Deal');
                //                         }
                //                     }
                //                 }else{
                //                     foreach($usrDeals as $usr)
                //                     {
                //                         $data = [
                //                             "updated_by" => Auth::user()->id,
                //                             "data_id" => $deal->id,
                //                             "name" => $deal->name,
                //                         ];
                //                         if($us_notify == 'true'){
                //                             Utility::makeNotification($usr,'assign_deal',$data,$deal->id,'Assign Deal');
                //                         }
                //                     }
                //                 }

                //             }
                //         }
                //     }
                // }
                if(count($usr_Deals) > 0){
                    foreach($usr_Deals as $usr_Deal)
                    {
                        UserDeal::create(
                            [
                                'user_id' => $usr_Deal,
                                'deal_id' => $deal->id,
                            ]
                        );
                    }
                }else{
                    foreach($usrDeals as $usrDeal)
                    {
                        UserDeal::create(
                            [
                                'user_id' => $usrDeal,
                                'deal_id' => $deal->id,
                            ]
                        );
                    }

                }

                CustomField::saveData($deal, $request->customField);

                // Send Email
                $setings = Utility::settings();

                // if ($setings['deal_assigned'] == 1) {
                //     $clients = User::whereIN('id', array_filter($request->clients))->get()->pluck('email', 'id')->toArray();
                //     $dealAssignArr = [
                //         'deal_name' => $deal->name,
                //         'deal_pipeline' => $pipeline->name,
                //         'deal_stage' => $stage->name,
                //         'deal_status' => $deal->status,
                //         'deal_price' => $usr->priceFormat($deal->price),
                //     ];
                //     $resp = Utility::sendEmailTemplate('deal_assigned', $clients, $dealAssignArr);
                //     //                    return redirect()->back()->with('success', __('Deal successfully created!')  .(($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

                // }

                //For Notification
                $setting = Utility::settings(\Auth::user()->creatorId());
                $dealNotificationArr = [
                    'user_name' => \Auth::user()->name,
                    'deal_name' => $deal->name,
                ];
                //Slack Notification
                if (isset($setting['deal_notification']) && $setting['deal_notification'] == 1) {
                    Utility::send_slack_msg('new_deal', $dealNotificationArr);
                }
                //Telegram Notification
                if (isset($setting['telegram_deal_notification']) && $setting['telegram_deal_notification'] == 1) {
                    Utility::send_telegram_msg('new_deal', $dealNotificationArr);
                }

                //webhook
                $module = 'New Deal';
                $webhook = Utility::webhookSetting($module);
                if ($webhook) {
                    $parameter = json_encode($deal);
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status == true) {
                        Utility::makeActivityLog(\Auth::user()->id, 'Deal', $deal->id, 'Create Deal', $deal->name);
                        return redirect()->back()->with('success', __('Deal successfully created!') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                    } else {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }
                Utility::makeActivityLog(\Auth::user()->id, 'Deal', $deal->id, 'Create Deal', $deal->name);
                \DB::commit();
                $cardHtml = view('deals.appendrow', compact('deal'))->render();
                $data = [
                    'card_html' => $cardHtml,
                    'card_container' => "task-list-" . $deal->stage_id,
                    'action' => 'add',
                    'card_id' => $deal->id,
                ];
                return response()->json(['success' => true, 'message' => __('Deal successfully created.'), 'data' => $data]);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified redeal.
     *
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Deal $deal)
    {
        if ($deal->is_active) {
            $calenderTasks = [];
            if (\Auth::user()->can('view task')) {
                foreach ($deal->tasks as $task) {
                    $calenderTasks[] = [
                        'title' => $task->name,
                        'start' => $task->date,
                        'url' => route(
                            'deals.tasks.show',
                            [
                                $deal->id,
                                $task->id,
                            ]
                        ),
                        'className' => ($task->status) ? 'bg-primary border-primary' : 'bg-warning border-warning',
                    ];
                }

            }
            $permission = [];
            $customFields = CustomField::where('module', '=', 'deal')->get();
            $deal->customField = CustomField::getData($deal, 'deal')->toArray();

            return view('deals.show', compact('deal', 'customFields', 'calenderTasks', 'permission'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified redeal.
     *
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Deal $deal)
    {
        if (\Auth::user()->can('edit deal')) {
            if ($deal->created_by == \Auth::user()->ownerId()) {
                if (\Auth::user()->type == 'company') {
                    $pipelines = Pipeline::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                    $sources = Source::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                    $products = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                } else {
                    $pipelines = Pipeline::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                    $sources = Source::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                    $products = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                }
                $deal->customField = CustomField::getData($deal, 'deal');
                $customFields = CustomField::where('created_by', '=', \Auth::user()->ownerId())->where('module', '=', 'deal')->get();

                $deal->sources = explode(',', $deal->sources);
                $deal->products = explode(',', $deal->products);
                
                // Get projects for unit selection
                $projects = \App\Models\ReProject::where('created_by', \Auth::user()->creatorId())
                    ->pluck('name', 'id')
                    ->toArray();

                return view('deals.edit', compact('deal', 'pipelines', 'sources', 'products', 'customFields', 'projects'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update the specified redeal in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Deal $deal)
    {
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('edit deal')) {
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|max:20',
                        'pipeline_id' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json(['error' => $messages->first()], 422);
                }

                $deal->name = $request->name;
                $deal->phone = $request->phone;
                if (empty($request->price)) {
                    $deal->price = 0;
                } else {
                    $deal->price = $request->price;
                }
                
                // Unit reservation logic - handle status changes
                $oldUnitId = $deal->re_unit_id;
                $newUnitId = $request->re_unit_id;
                
                // If unit changed, update statuses
                if ($oldUnitId != $newUnitId) {
                    // Revert old unit to Available (if it was reserved by this deal)
                    if ($oldUnitId) {
                        $oldUnit = \App\Models\ReUnit::find($oldUnitId);
                        if ($oldUnit && $oldUnit->status == 'Reserved') {
                            $oldUnit->status = 'Available';
                            $oldUnit->save();
                        }
                    }
                    // Mark new unit as Reserved
                    if ($newUnitId) {
                        $newUnit = \App\Models\ReUnit::find($newUnitId);
                        if ($newUnit && $newUnit->status == 'Available') {
                            $newUnit->status = 'Reserved';
                            $newUnit->save();
                        }
                    }
                }
                
                // Unit details
                $deal->re_project_id = $request->re_project_id;
                $deal->re_tower_id = $request->re_tower_id;
                $deal->re_floor_id = $request->re_floor_id;
                $deal->re_unit_id = $request->re_unit_id;
                $deal->offered_price = $request->offered_price;
                $deal->discount = $request->discount;
                $deal->expected_closing_date = $request->expected_closing_date;

                // Client details
                $deal->customer_type = $request->customer_type;
                $deal->full_name = $request->full_name;
                $deal->father_or_company_name = $request->father_or_company_name;
                $deal->cnic_or_ntn = $request->cnic_or_ntn;
                $deal->mobile_primary = $request->mobile_primary;
                $deal->mobile_secondary = $request->mobile_secondary;
                $deal->current_address = $request->current_address;
                $deal->permanent_address = $request->permanent_address;
                $deal->nationality = $request->nationality;

                // Nominee details
                $deal->nominee_name = $request->nominee_name;
                $deal->nominee_relation = $request->nominee_relation;
                $deal->nominee_cnic = $request->nominee_cnic;
                $deal->nominee_contact = $request->nominee_contact;

                $deal->status = $request->status;
                
                $deal->pipeline_id = $request->pipeline_id;
                $deal->stage_id = $request->stage_id;
                $deal->sources = implode(",", array_filter($request->sources ?? []));
                $deal->products = implode(",", array_filter($request->products ?? []));
                $deal->notes = $request->notes;
                $deal->save();

                CustomField::saveData($deal, $request->customField);
                Utility::makeActivityLog(\Auth::user()->id, 'Deal', $deal->id, 'Update Deal', $deal->name);
                \DB::commit();
                $cardHtml = view('deals.appendrow', compact('deal'))->render();
                $data = [
                    'card_html' => $cardHtml,
                    'card_container' => "task-list-" . $deal->stage_id,
                    'action' => 'edit',
                    'card_id' => $deal->id,
                ];
                return response()->json(['success' => true, 'message' => __('Deal successfully updated.'), 'data' => $data]);
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified redeal from storage.
     *
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Deal $deal)
    {
        if (\Auth::user()->can('delete deal')) {
            if ($deal->created_by == \Auth::user()->ownerId()) {
                DealDiscussion::where('deal_id', '=', $deal->id)->delete();
                DealFile::where('deal_id', '=', $deal->id)->delete();
                ClientDeal::where('deal_id', '=', $deal->id)->delete();
                UserDeal::where('deal_id', '=', $deal->id)->delete();
                DealTask::where('deal_id', '=', $deal->id)->delete();
                ActivityLog::where('deal_id', '=', $deal->id)->delete();
                //                ClientPermission::where('deal_id', '=', $deal->id)->delete();
                Utility::makeActivityLog(\Auth::user()->id, 'Deal', $deal->id, 'Delete Deal', $deal->name);
                $deal->delete();

                return redirect()->route('deals.index')->with('success', __('Deal successfully deleted!'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function order(Request $request)
    {
        $usr = \Auth::user();

        if ($usr->can('move deal')) {
            $post = $request->all();
            $deal = $this->deal($post['deal_id']);
            $clients = ClientDeal::select('client_id')->where('deal_id', '=', $deal->id)->get()->pluck('client_id')->toArray();
            $deal_users = $deal->users->pluck('id')->toArray();
            $usrs = User::whereIN('id', array_merge($deal_users, $clients))->get()->pluck('email', 'id')->toArray();


            $usrDeal = [];
            if($deal->stage_id != $post['stage_id'])
            {

                $newStage = Stage::find($post['stage_id']);
                    // WorkFlow get which is active
                    $us_mail= 'false';
                    $us_notify= 'false';
                    $workflow = WorkFlow::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=','crm')->where('status',1)->first();
                    if($workflow){
                        $workflowaction = WorkFlowAction::where('workflow_id',$workflow->id)->where('status',1)->where('level_id',2)->get();
                        foreach($workflowaction as $action){
                            $useraction = json_decode($action->assigned_users);
                            if(strtolower($newStage->name . '-2') == $action->node_id){
                                // Pick that stage user assign or change on deal
                                if(@$useraction != ''){
                                    $useraction = json_decode($useraction);
                                    foreach($useraction as $anyaction){
                                        // make new user array
                                        if($anyaction->type == 'user'){
                                            $usrDeal[] = $anyaction->id;
                                        }
                                    }
                                }
                                //  if user assign on this stage then check for mail and notification conditions

                                $raw_json = trim($action->applied_conditions, '"');
                                $cleaned_json = stripslashes($raw_json);
                                $applied_conditions = json_decode($cleaned_json, true);

                                if (isset($applied_conditions['conditions']) && is_array($applied_conditions['conditions'])) {
                                    $arr = [
                                        'products' => 'App\Models\ProductService',
                                        'sources' => 'App\Models\Source',
                                        'label' => 'App\Models\Label',
                                    ];
                                    foreach ($applied_conditions['conditions'] as $conditionGroup) {
                                        if (in_array($conditionGroup['action'], ['send_email', 'send_notification','send_approval'])) {
                                            $query = Deal::where('id',$post['deal_id']);
                                            foreach ($conditionGroup['conditions'] as $condition) {
                                                $field = $condition['field'];
                                                $operator = $condition['operator'];
                                                $value = $condition['value'];
                                                if (Schema::hasColumn('deals', $field)) {
                                                    if (array_key_exists($field, $arr)) {
                                                        $a =$arr[$field]::where('name',$value)->pluck('id')->toArray();
                                                        if(isset($a) && count($a) > 0){
                                                            $query->where($field,$operator,$a);
                                                        }
                                                    }else{
                                                        $query->where($field, $operator, $value);
                                                    }
                                                }
                                            }
                                            $result = $query->first();
                                            if (!empty($result)) {
                                                if ($conditionGroup['action'] === 'send_email') {
                                                    $us_mail = 'true';
                                                } elseif ($conditionGroup['action'] === 'send_notification') {
                                                    $us_notify = 'true';
                                                }
                                                elseif ($conditionGroup['action'] === 'send_approval') {
                                                    $us_approve = 'true';
                                                }
                                            }
                                        }
                                    }
                                }

                                if($us_mail == 'true'){
                                    // email send
                                }
                                if($us_notify == 'true' || $us_approve == 'true'){
                                    // notification generate
                                    if(count($usrDeal) > 0){
                                        $usrDeal[] = Auth::user()->created_by;
                                        foreach($usrDeal as $usrDeal1)
                                        {
                                            $data = [
                                                "updated_by" => Auth::user()->id,
                                                "data_id" => $request->deal_id,
                                                "name" => $deal->name,
                                            ];
                                            if($us_notify == 'true'){
                                                Utility::makeNotification($usrDeal1,'assign_Deal',$data,$request->deal_id,'Assign Deal');
                                            }elseif($us_approve == 'true'){
                                                Utility::makeNotification($usrDeal1,'approve_deal_stage',$data,$request->deal_id,'For Approval Deal Stage');
                                            }
                                        }
                                    }else{
                                        $olduser=UserDeal::where('deal_id',$request->deal_id)->get();
                                        foreach($olduser as $usr)
                                        {
                                            $data = [
                                                "updated_by" => Auth::user()->id,
                                                "data_id" => $request->deal_id,
                                                "name" => $deal->name,
                                            ];
                                            if($us_notify == 'true'){
                                                Utility::makeNotification($usr,'deal_stage_update',$data,$request->deal_id,'Deal Stage Update');
                                            }
                                        }
                                    }

                                }
                                $output = json_decode($action->outputs);
                                $triger_mail= 'false';
                                $triger_notify= 'false';
                                $send_approval= 'false';
                                // For pick its Child nodes or connected nodes
                                if (isset($output->output_1->connections)) {
                                    foreach($output->output_1->connections as $out){
                                        //  if any of its child have trigger
                                        $rows = WorkFlowAction::where('workflow_id',$workflow->id)->where('status',1)->where('node_actual_id', 'node-' . $out->node)->where('type','trigger')->get();
                                        if($rows){
                                            foreach($rows as $row){
                                                $rowdata = json_decode($row->assigned_users);
                                                if(@$rowdata != ''){
                                                    $rowdata = json_decode($rowdata);
                                                    foreach($rowdata as $us_action){
                                                        if($us_action->type == 'user'){
                                                            $rowusrDeals[] = $us_action->id;
                                                        }
                                                    }
                                                }
                                                $triger_mail= 'true';
                                                $triger_notify= 'true';
                                                $send_approval= 'true';
                                                //  if user assign on this stage then check for mail and notification conditions

                                                // $raw_json = trim($row->applied_conditions, '"');
                                                // $cleaned_json = stripslashes($raw_json);
                                                // $applied_conditions = json_decode($cleaned_json, true);

                                                // if (isset($applied_conditions['conditions']) && is_array($applied_conditions['conditions'])) {
                                                //     foreach ($applied_conditions['conditions'] as $conditionGroup) {
                                                //         $arr = [
                                                //             'products' => 'App\Models\ProductService',
                                                //             'sources' => 'App\Models\Source',
                                                //         ];
                                                //         if ($conditionGroup['action'] === 'send_email') {
                                                //             $query = Deal::where('id',$post['deal_id']);
                                                //             foreach ($conditionGroup['conditions'] as $condition) {
                                                //                 $field = $condition['field'];
                                                //                 $operator = $condition['operator'];
                                                //                 $value = $condition['value'];
                                                //                 if (Schema::hasColumn('deals', $field)) {
                                                //                     if (array_key_exists($field, $arr)) {
                                                //                         $a =$arr[$field]::where('name',$value)->pluck('id')->toArray();
                                                //                         if(isset($a) && count($a) > 0){
                                                //                             $query->where($field,$operator,$a);
                                                //                         }
                                                //                     }else{
                                                //                         $query->where($field, $operator, $value);
                                                //                     }
                                                //                 }
                                                //             }
                                                //             $dealmail = $query->first();
                                                //             if(!empty($dealmail)){
                                                //                 $triger_mail = 'true';
                                                //             }
                                                //         }elseif($conditionGroup['action'] === 'send_notification'){
                                                //             $query = Deal::where('id',$post['deal_id']);
                                                //             foreach ($conditionGroup['conditions'] as $condition) {
                                                //                 $field = $condition['field'];
                                                //                 $operator = $condition['operator'];
                                                //                 $value = $condition['value'];
                                                //                 if (Schema::hasColumn('deals', $field)) {
                                                //                     if (array_key_exists($field, $arr)) {
                                                //                         $a =$arr[$field]::where('name',$value)->pluck('id')->toArray();
                                                //                         if(isset($a) && count($a) > 0){
                                                //                             $query->where($field,$operator,$a);
                                                //                         }
                                                //                     }else{
                                                //                         $query->where($field, $operator, $value);
                                                //                     }
                                                //                 }
                                                //             }
                                                //             $dealnotification = $query->first();
                                                //             if(!empty($dealnotification)){
                                                //                 $triger_notify = 'true';
                                                //             }
                                                //         }
                                                //     }
                                                // }

                                                if($triger_mail == 'true'){
                                                    // email send
                                                }

                                                if($triger_notify == 'true' || $send_approval == 'true'){
                                                    if($row->node_id =='create-contract'){
                                                        $type ='create_contract';
                                                    }else if($row->node_id =='create-invoice'){
                                                        $type ='create_invoice';
                                                    }else{
                                                        $type ='other';
                                                    }
                                                    // notification generate
                                                    if(count($rowusrDeals) > 0){
                                                        $rowusrDeals[] = Auth::user()->creatorId();

                                                        foreach($rowusrDeals as $usrDeal2)
                                                        {

                                                            $data = [
                                                                "updated_by" => Auth::user()->id,
                                                                "data_id" => $request->deal_id,
                                                                "name" => $deal->name,
                                                            ];
                                                            if($triger_notify == 'true'){
                                                                Utility::makeNotification($usrDeal1,$type,$data,$request->deal_id,$type);
                                                            }
                                                        }
                                                    }

                                                }
                                            }
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                    if(count($usrDeal) > 0){
                        $olduser=UserDeal::where('deal_id',$request->deal_id)->delete();
                        $rowusrDeals = Auth::user()->creatorId();
                        foreach($usrDeal as $usrDeal)
                        {
                            UserDeal::create(
                                [
                                    'user_id' => $usrDeal,
                                    'deal_id' => $request->deal_id,
                                ]
                            );
                        }
                    }

                ActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Move',
                        'remark' => json_encode(
                            [
                                'title' => $deal->name,
                                'old_status' => $deal->stage->name,
                                'new_status' => $newStage->name,
                            ]
                        ),
                    ]
                );

                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                    'old_status' => $deal->stage->name,
                    'new_status' => $newStage->name,
                ];

                $dArr = [
                    'deal_name' => $deal->name,
                    'deal_pipeline' => $deal->email,
                    'deal_stage' => $deal->stage->name,
                    'deal_status' => $deal->status,
                    'deal_price' => $usr->priceFormat($deal->price),
                    'deal_old_stage' => $deal->stage->name,
                    'deal_new_stage' => $newStage->name,
                ];

                // Send Email
                Utility::sendEmailTemplate('Move Deal', $usrs, $dArr);
            }

            foreach ($post['order'] as $key => $item) {
                $deal = $this->deal($item);
                $deal->order = $key;
                $deal->stage_id = $post['stage_id'];
                $deal->save();
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    private static $dealData = NUll;
    public function deal($item)
    {
        if (self::$dealData == null) {
            $deal = Deal::find($item);
            self::$dealData = $deal;
        }
        return self::$dealData;
    }

    public function labels($id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $labels = Label::where('pipeline_id', '=', $deal->pipeline_id)->where('created_by', \Auth::user()->creatorId())->get();
                $selected = $deal->labels();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                } else {
                    $selected = [];
                }

                return view('deals.labels', compact('deal', 'labels', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function labelStore($id, Request $request)
    { 
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                if ($request->labels) {
                    $deal->labels = implode(',', $request->labels);
                } else {
                    $deal->labels = $request->labels;
                }
                $deal->save();

                \DB::commit();
                // Make sure we're passing the deal variable correctly
                $deal = $deal; // Ensure variable name matches what the view expects
                $cardHtml = view('deals.appendrow', compact('deal'))->render();
                $data = [
                    'card_html' => $cardHtml,
                    'card_container' => "task-list-" . $deal->stage_id,
                    'action' => 'edit',
                    'card_id' => $deal->id,
                ];
                return response()->json(['success' => true, 'message' => __('Labels successfully updated.'), 'data' => $data]);
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function userEdit($id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $users = User::where('created_by', '=', \Auth::user()->ownerId())->where('type', '!=', 'client')->whereNOTIn(
                    'id',
                    function ($q) use ($deal) {
                        $q->select('user_id')->from('user_deals')->where('deal_id', '=', $deal->id);
                    }
                )->get();

                foreach ($users as $key => $user) {
                    if (!$user->can('manage deal')) {
                        $users->forget($key);
                    }
                }
                $users = $users->pluck('name', 'id');

                $users->prepend(__('Select Users'), '');

                return view('deals.users', compact('deal', 'users'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function userUpdate($id, Request $request)
    {
        $usr = \Auth::user();
        if ($usr->can('edit deal')) {
            $deal = Deal::find($id);
            $resp = '';

            if ($deal->created_by == $usr->ownerId()) {
                if (!empty($request->users)) {
                    $users = User::whereIN('id', array_filter($request->users))->get()->pluck('email', 'id')->toArray();

                    $dealArr = [
                        'deal_id' => $deal->id,
                        'name' => $deal->name,
                        'updated_by' => $usr->id,
                    ];

                    $dArr = [
                        'deal_name' => $deal->name,
                        'deal_pipeline' => $deal->pipeline->name,
                        'deal_stage' => $deal->stage->name,
                        'deal_status' => $deal->status,
                        'deal_price' => $usr->priceFormat($deal->price),
                    ];

                    foreach (array_keys($users) as $user) {
                        UserDeal::create(
                            [
                                'deal_id' => $deal->id,
                                'user_id' => $user,
                            ]
                        );
                    }

                    // Send Email
                    $resp = Utility::sendEmailTemplate('Assign Deal', $users, $dArr);
                }

                if (!empty($users) && !empty($request->users)) {
                    return redirect()->back()->with('success', __('Users successfully updated!') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                } else {
                    return redirect()->back()->with('error', __('Please Select Valid User!'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userDestroy($id, $user_id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                UserDeal::where('deal_id', '=', $deal->id)->where('user_id', '=', $user_id)->delete();

                return redirect()->back()->with('success', __('User successfully deleted!'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function clientEdit($id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $clients = User::where('created_by', '=', \Auth::user()->ownerId())->where('type', 'client')->whereNOTIn(
                    'id',
                    function ($q) use ($deal) {
                        $q->select('client_id')->from('client_deals')->where('deal_id', '=', $deal->id);
                    }
                )->get()->pluck('name', 'id');

                return view('deals.clients', compact('deal', 'clients'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function clientUpdate($id, Request $request)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                if (!empty($request->clients)) {
                    $clients = array_filter($request->clients);
                    foreach ($clients as $client) {
                        ClientDeal::create(
                            [
                                'deal_id' => $deal->id,
                                'client_id' => $client,
                            ]
                        );
                    }
                }

                if (!empty($clients) && !empty($request->clients)) {
                    return redirect()->back()->with('success', __('Clients successfully updated!'))->with('status', 'clients');
                } else {
                    return redirect()->back()->with('error', __('Please Select Valid Clients!'))->with('status', 'clients');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
        }
    }

    public function clientDestroy($id, $client_id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                ClientDeal::where('deal_id', '=', $deal->id)->where('client_id', '=', $client_id)->delete();

                return redirect()->back()->with('success', __('Client successfully deleted!'))->with('status', 'clients');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
        }
    }

    public function productEdit($id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $products = ProductService::where('created_by', '=', \Auth::user()->ownerId())->whereNOTIn('id', explode(',', $deal->products))->get()->pluck('name', 'id');

                return view('deals.products', compact('deal', 'products'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function productUpdate($id, Request $request)
    {
        $usr = \Auth::user();
        if ($usr->can('edit deal')) {
            $deal = Deal::find($id);
            $clients = ClientDeal::select('client_id')->where('deal_id', '=', $id)->get()->pluck('client_id')->toArray();
            $deal_users = $deal->users->pluck('id')->toArray();

            if ($deal->created_by == $usr->ownerId()) {
                if (!empty($request->products)) {
                    $products = array_filter($request->products);
                    $old_products = explode(',', $deal->products);
                    $deal->products = implode(',', array_merge($old_products, $products));
                    $deal->save();

                    $objProduct = ProductService::whereIN('id', $products)->get()->pluck('name', 'id')->toArray();
                    ActivityLog::create(
                        [
                            'user_id' => $usr->id,
                            'deal_id' => $deal->id,
                            'log_type' => 'Add Product',
                            'remark' => json_encode(['title' => implode(",", $objProduct)]),
                        ]
                    );

                    $productArr = [
                        'deal_id' => $deal->id,
                        'name' => $deal->name,
                        'updated_by' => $usr->id,
                    ];

                }

                if (!empty($products) && !empty($request->products)) {
                    return redirect()->back()->with('success', __('Products successfully updated!'))->with('status', 'products');
                } else {
                    return redirect()->back()->with('error', __('Please Select Valid Product!'))->with('status', 'general');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    public function productDestroy($id, $product_id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $products = explode(',', $deal->products);
                foreach ($products as $key => $product) {
                    if ($product_id == $product) {
                        unset($products[$key]);
                    }
                }
                $deal->products = implode(',', $products);
                $deal->save();

                return redirect()->back()->with('success', __('Products successfully deleted!'))->with('status', 'products');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    public function fileUpload($id, Request $request)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $request->validate(['file' => 'required']);

                //storage limit
                $image_size = $request->file('file')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                $file_name = $request->file->getClientOriginalName();
                $file_path = $request->deal_id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();

                $file = DealFile::create(
                    [
                        'deal_id' => $request->deal_id,
                        'file_name' => $file_name,
                        'file_path' => $file_path,
                    ]
                );

                if ($result == 1) {
                    $request->file->storeAs('deal_files', $file_path);

                    $return = [];
                    $return['is_success'] = true;
                    $return['download'] = route(
                        'deals.file.download',
                        [
                            $deal->id,
                            $file->id,
                        ]
                    );
                    $return['delete'] = route(
                        'deals.file.delete',
                        [
                            $deal->id,
                            $file->id,
                        ]
                    );
                } else {
                    $return = [];
                    $return['is_success'] = true;
                    $return['status'] = 1;
                    $return['success_msg'] = ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '');
                }


                ActivityLog::create(
                    [
                        'user_id' => \Auth::user()->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Upload File',
                        'remark' => json_encode(['file_name' => $file_name]),
                    ]
                );

                return response()->json($return);
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function fileDownload($id, $file_id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $file = DealFile::find($file_id);
                if ($file) {
                    $file_path = storage_path('deal_files/' . $file->file_path);
                    $filename = $file->file_name;

                    return \Response::download(
                        $file_path,
                        $filename,
                        [
                            'Content-Length: ' . filesize($file_path),
                        ]
                    );
                } else {
                    return redirect()->back()->with('error', __('File is not exist.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $file = DealFile::find($file_id);
                if ($file) {

                    //storage limit
                    $file_path = 'deal_files/' . $file->file_path;
                    $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

                    $path = storage_path('deal_files/' . $file->file_path);
                    if (file_exists($path)) {
                        \File::delete($path);
                    }
                    $file->delete();

                    return response()->json(['is_success' => true], 200);
                } else {
                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => __('File is not exist.'),
                        ],
                        200
                    );
                }
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function noteStore($id, Request $request)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $deal->notes = $request->notes;
                $deal->save();

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('Note successfully saved!'),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskCreate($id)
    {
        if (\Auth::user()->can('create task')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $priorities = DealTask::$priorities;
                $status = DealTask::$status;

                return view('deals.tasks', compact('deal', 'priorities', 'status'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskStore($id, Request $request)
    {
        $usr = \Auth::user();
        if ($usr->can('create task')) {
            $deal = Deal::find($id);
            $clients = ClientDeal::select('client_id')->where('deal_id', '=', $id)->get()->pluck('client_id')->toArray();
            $deal_users = $deal->users->pluck('id')->toArray();
            $usrs = User::whereIN('id', array_merge($deal_users, $clients))->get()->pluck('email', 'id')->toArray();

            if ($deal->created_by == $usr->ownerId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'date' => 'required',
                        'time' => 'required',
                        'priority' => 'required',
                        'status' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $dealTask = DealTask::create(
                    [
                        'deal_id' => $deal->id,
                        'name' => $request->name,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                    ]
                );

                ActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Create Task',
                        'remark' => json_encode(['title' => $dealTask->name]),
                    ]
                );

                $taskArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                $tArr = [
                    'deal_name' => $deal->name,
                    'deal_pipeline' => $deal->pipeline->name,
                    'deal_stage' => $deal->stage->name,
                    'deal_status' => $deal->status,
                    'deal_price' => $usr->priceFormat($deal->price),
                    'task_name' => $dealTask->name,
                    'task_priority' => DealTask::$priorities[$dealTask->priority],
                    'task_status' => DealTask::$status[$dealTask->status],
                ];

                // Send Email
                Utility::sendEmailTemplate('Create Task', $usrs, $tArr);

                return redirect()->back()->with('success', __('Task successfully created!'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskShow($id, $task_id)
    {
        if (\Auth::user()->can('view task')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $task = DealTask::find($task_id);

                return view('deals.tasksShow', compact('task', 'deal'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskEdit($id, $task_id)
    {
        if (\Auth::user()->can('edit task')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $priorities = DealTask::$priorities;
                $status = DealTask::$status;
                $task = DealTask::find($task_id);

                return view('deals.tasks', compact('task', 'deal', 'priorities', 'status'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskUpdate($id, $task_id, Request $request)
    {
        if (\Auth::user()->can('edit task')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'date' => 'required',
                        'time' => 'required',
                        'priority' => 'required',
                        'status' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $task = DealTask::find($task_id);

                $task->update(
                    [
                        'name' => $request->name,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                    ]
                );

                return redirect()->back()->with('success', __('Task successfully updated!'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskUpdateStatus($id, $task_id, Request $request)
    {
        if (\Auth::user()->can('edit task')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'status' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => $messages->first(),
                        ],
                        401
                    );
                }

                $task = DealTask::find($task_id);
                if ($request->status) {
                    $task->status = 0;
                } else {
                    $task->status = 1;
                }
                $task->save();

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('Task successfully updated!'),
                        'status' => $task->status,
                        'status_label' => __(DealTask::$status[$task->status]),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskDestroy($id, $task_id)
    {
        if (\Auth::user()->can('delete task')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $task = DealTask::find($task_id);
                $task->delete();

                return redirect()->back()->with('success', __('Task successfully deleted!'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function sourceEdit($id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $sources = Source::where('created_by', '=', \Auth::user()->ownerId())->get();
                $selected = $deal->sources();

                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                }

                return view('deals.sources', compact('deal', 'sources', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function sourceUpdate($id, Request $request)
    {
        $usr = \Auth::user();

        if ($usr->can('edit deal')) {
            $deal = Deal::find($id);
            $clients = ClientDeal::select('client_id')->where('deal_id', '=', $id)->get()->pluck('client_id')->toArray();
            $deal_users = $deal->users->pluck('id')->toArray();

            if ($deal->created_by == $usr->ownerId()) {
                if (!empty($request->sources) && count($request->sources) > 0) {
                    $deal->sources = implode(',', $request->sources);
                } else {
                    $deal->sources = "";
                }

                $deal->save();
                ActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Update Sources',
                        'remark' => json_encode(['title' => 'Update Sources']),
                    ]
                );

                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                return redirect()->back()->with('success', __('Sources successfully updated!'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function sourceDestroy($id, $source_id)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $sources = explode(',', $deal->sources);
                foreach ($sources as $key => $source) {
                    if ($source_id == $source) {
                        unset($sources[$key]);
                    }
                }
                $deal->sources = implode(',', $sources);
                $deal->save();

                return redirect()->back()->with('success', __('Sources successfully deleted!'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function permission($id, $clientId)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            $client = User::find($clientId);
            $selected = $client->clientPermission($deal->id);
            if ($selected) {
                $selected = explode(',', $selected->permissions);
            } else {
                $selected = [];
            }
            $permissions = Deal::$permissions;

            return view('deals.permissions', compact('deal', 'client', 'selected', 'permissions'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
        }
    }

    public function permissionStore($id, $clientId, Request $request)
    {
        if (\Auth::user()->can('edit deal')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $client = User::find($clientId);
                $permissions = $client->clientPermission($deal->id);
                if ($permissions) {
                    if (!empty($request->permissions) && count($request->permissions) > 0) {
                        $permissions->permissions = implode(',', $request->permissions);
                    } else {
                        $permissions->permissions = "";
                    }
                    $permissions->save();

                    return redirect()->back()->with('success', __('Permissions successfully updated!'))->with('status', 'clients');
                } elseif (!empty($request->permissions) && count($request->permissions) > 0) {
                    ClientPermission::create(
                        [
                            'client_id' => $clientId,
                            'deal_id' => $deal->id,
                            'permissions' => implode(',', $request->permissions),
                        ]
                    );

                    return redirect()->back()->with('success', __('Permissions successfully updated!'))->with('status', 'clients');
                } else {
                    return redirect()->back()->with('error', __('Invalid Permission.'))->with('status', 'clients');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
        }
    }

    public function jsonUser(Request $request)
    {
        $users = [];
        if (!empty($request->deal_id)) {
            $deal = Deal::find($request->deal_id);
            $users = $deal->users->pluck('name', 'id');
        }

        return response()->json($users, 200);
    }

    public function changePipeline(Request $request)
    {
        $user = \Auth::user();
        $user->default_pipeline = $request->default_pipeline_id;
        $user->save();

        return redirect()->back();
    }

    public function discussionCreate($id)
    {
        $deal = Deal::find($id);
        if ($deal->created_by == \Auth::user()->ownerId()) {
            return view('deals.discussions', compact('deal'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function discussionStore($id, Request $request)
    {
        $usr = \Auth::user();
        $deal = Deal::find($id);
        $clients = ClientDeal::select('client_id')->where('deal_id', '=', $id)->get()->pluck('client_id')->toArray();
        $deal_users = $deal->users->pluck('id')->toArray();

        if ($deal->created_by == \Auth::user()->ownerId()) {
            $discussion = new DealDiscussion();
            $discussion->comment = $request->comment;
            $discussion->deal_id = $deal->id;
            $discussion->created_by = \Auth::user()->id;
            $discussion->save();

            $dealArr = [
                'deal_id' => $deal->id,
                'name' => $deal->name,
                'updated_by' => $usr->id,
            ];

            return redirect()->back()->with('success', __('Message successfully added!'))->with('status', 'discussion');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'discussion');
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $deal = Deal::where('id', '=', $id)->first();
        $deal->status = $request->deal_status;
        $deal->save();

        return redirect()->back();
    }

    // Deal Calls
    public function callCreate($id)
    {
        if (\Auth::user()->can('create deal call')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $users = UserDeal::where('deal_id', '=', $deal->id)->get();

                return view('deals.calls', compact('deal', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callStore($id, Request $request)
    {
        $usr = \Auth::user();

        if ($usr->can('create deal call')) {
            $deal = Deal::find($id);
            if ($deal->created_by == $usr->ownerId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject' => 'required',
                        'call_type' => 'required',
                        'user_id' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                DealCall::create(
                    [
                        'deal_id' => $deal->id,
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                ActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Create Deal Call',
                        'remark' => json_encode(['title' => 'Create new Deal Call']),
                    ]
                );

                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                return redirect()->back()->with('success', __('Call successfully created!'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    public function callEdit($id, $call_id)
    {
        if (\Auth::user()->can('edit deal call')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $call = DealCall::find($call_id);
                $users = UserDeal::where('deal_id', '=', $deal->id)->get();

                return view('deals.calls', compact('call', 'deal', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callUpdate($id, $call_id, Request $request)
    {
        if (\Auth::user()->can('edit deal call')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject' => 'required',
                        'call_type' => 'required',
                        'user_id' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $call = DealCall::find($call_id);

                $call->update(
                    [
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                return redirect()->back()->with('success', __('Call successfully updated!'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function callDestroy($id, $call_id)
    {
        if (\Auth::user()->can('delete deal call')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                $task = DealCall::find($call_id);
                $task->delete();

                return redirect()->back()->with('success', __('Call successfully deleted!'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    // Deal email
    public function emailCreate($id)
    {
        if (\Auth::user()->can('create deal email')) {
            $deal = Deal::find($id);
            if ($deal->created_by == \Auth::user()->ownerId()) {
                return view('deals.emails', compact('deal'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function emailStore($id, Request $request)
    {
        if (\Auth::user()->can('create deal email')) {
            $deal = Deal::find($id);

            if ($deal->created_by == \Auth::user()->ownerId()) {
                $settings = Utility::settings();
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'to' => 'required|email',
                        'subject' => 'required',
                        'description' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                DealEmail::create(
                    [
                        'deal_id' => $deal->id,
                        'to' => $request->to,
                        'subject' => $request->subject,
                        'description' => $request->description,
                    ]
                );

                $dealEmail =
                    [
                        'deal_name' => $deal->name,
                        'to' => $request->to,
                        'subject' => $request->subject,
                        'description' => $request->description,
                    ];

                //                dd($deal->name);


                try {
                    Mail::to($request->to)->send(new SendDealEmail($dealEmail, $settings));
                } catch (\Exception $e) {
                    //                    dd($e);
                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                }


                ActivityLog::create(
                    [
                        'user_id' => \Auth::user()->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Create Deal Email',
                        'remark' => json_encode(['title' => 'Create new Deal Email']),
                    ]
                );

                return redirect()->back()->with('success', __('Email successfully created!') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''))->with('status', 'emails');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
        }
    }

    public function export()
    {
        $name = 'Deal_' . date('Y-m-d i:h:s');
        $data = Excel::download(new DealExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function importFile()
    {
        return view('deals.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $deals = (new DealImport())->toArray(request()->file('file'))[0];

        $totalDeal = count($deals) - 1;
        $errorArray = [];
        for ($i = 1; $i <= count($deals) - 1; $i++) {

            $deal = $deals[$i];

            $dealData = new Deal();

            $user = User::where('name', $deal[5])->where('type', 'client')->where('created_by', \Auth::user()->creatorId())->first();
            $pipeline = PipeLine::where('name', $deal[3])->where('created_by', \Auth::user()->creatorId())->first();
            $stage = Stage::where('name', $deal[4])->where('created_by', \Auth::user()->creatorId())->first();

            $dealData->name = $deal[0];
            $dealData->phone = $deal[1];
            $dealData->price = $deal[2];
            // $dealData->user_id     = !empty($user) ? $user->id : 3;
            $dealData->pipeline_id = !empty($pipeline) ? $pipeline->id : 1;
            $dealData->stage_id = !empty($stage) ? $stage->id : 1;
            $dealData->created_by = \Auth::user()->creatorId();
            $dealData->status = 'Active';

            if (empty($dealData)) {
                $errorArray[] = $dealData;
            } else {
                $dealData->save();

                $clientData = new ClientDeal();
                $clientData->client_id = $user->id;
                $clientData->deal_id = $dealData->id;
                $clientData->save();

                $userData = new UserDeal();
                $userData->user_id = \Auth::user()->creatorId();
                $userData->deal_id = $dealData->id;
                $userData->save();

            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg'] = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg'] = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalDeal . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);

            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    /**
     * Get towers by project (AJAX)
     */
    public function getTowersByProject(Request $request)
    {
        $towers = \App\Models\ReTower::where('re_project_id', $request->project_id)
            ->pluck('tower_name', 'id')
            ->toArray();
        return response()->json($towers);
    }

    /**
     * Get floors by tower (AJAX)
     */
    public function getFloorsByProject(Request $request)
    {
        $query = \App\Models\ReFloor::where('re_project_id', $request->project_id);
        
        if ($request->has('tower_id') && !empty($request->tower_id)) {
            $query->where('re_tower_id', $request->tower_id);
        }
        
        $floors = $query->pluck('floor_name', 'id')->toArray();
        return response()->json($floors);
    }

    /**
     * Get units by floor (AJAX)
     */
    public function getUnitsByFloor(Request $request)
    {
        $units = \App\Models\ReUnit::where('re_floor_id', $request->floor_id)
            ->where('status', '!=', 'Sold')
            ->get()
            ->mapWithKeys(function($u) {
                return [$u->id => $u->unit_number . ' (' . $u->status . ')'];
            });
        return response()->json($units);
    }

    /**
     * Get unit detail (AJAX)
     */
    public function getUnitDetail(Request $request)
    {
        $unit = \App\Models\ReUnit::find($request->unit_id);
        if ($unit) {
            return response()->json([
                'success' => true,
                'price' => $unit->price,
                'area' => $unit->area,
                'status' => $unit->status
            ]);
        }
        return response()->json(['success' => false]);
    }

    /**
     * Show convert to contract form
     */
    public function showConvertToContract($id)
    {
        if (\Auth::user()->can('create contract')) {
            $deal = Deal::findOrFail($id);
            
            if (\Auth::user()->type == 'company') {
                $contractTypes = \App\Models\ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $clients = User::where('type', 'client')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $contractTypes = \App\Models\ContractType::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $clients = User::where('type', 'client')->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            $clients->prepend(__('Select Client'), 0);
            
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'contract')->get();
            
            // Find or create client from deal email/name
            $selectedClient = 0;
            if (!empty($deal->email)) {
                $existingClient = User::where('email', $deal->email)->where('type', 'client')->first();
                if ($existingClient) {
                    $selectedClient = $existingClient->id;
                }
            }
            
            // Pass deal data to prefill the form
            $prefill = [
                'subject' => $deal->name,
                'value' => $deal->offered_price ?? $deal->price ?? 0,
                'start_date' => $deal->created_at ? $deal->created_at->format('Y-m-d') : date('Y-m-d'),
                'end_date' => $deal->expected_closing_date ?? now()->addMonths(1)->format('Y-m-d'),
                'description' => $deal->notes,
                'client_id' => $selectedClient,
                'deal_id' => $deal->id,
                // real estate specific pre-fill
                'full_name' => $deal->full_name ?? $deal->name,
                'client_type' => $deal->customer_type ?? 'Individual',
                'father_or_spouse_name' => $deal->father_or_company_name,
                'cnic_number' => $deal->cnic_or_ntn,
                'mobile_primary' => $deal->mobile_primary ?? $deal->phone,
                'mobile_secondary' => $deal->mobile_secondary,
                'nationality' => $deal->nationality,
                'current_address' => $deal->current_address,
                'permanent_address' => $deal->permanent_address,
                'email' => $deal->email,
                'project_name' => $deal->project->name ?? '',
                'tower_name' => $deal->tower->tower_name ?? '',
                'floor_name' => $deal->floor->floor_name ?? '',
                'unit_name' => $deal->unit->unit_number ?? '',
            ];

            $projects = \App\Models\ReProject::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $all_customers = \App\Models\Customer::where('created_by', \Auth::user()->creatorId())->get();
            
            $payment_plans = [];
            if ($deal->re_project_id) {
                $payment_plans = \App\Models\RePaymentPlan::whereHas('projects', function($q) use ($deal) {
                    $q->where('re_projects.id', $deal->re_project_id);
                })->where('is_active', true)->get();
            }

            return view('deals.convert_to_contract_form', compact('deal', 'contractTypes', 'clients', 'customFields', 'prefill', 'projects', 'all_customers', 'payment_plans'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Convert deal to contract
     */
    public function convertToContract(Request $request, $id)
    {
        if (\Auth::user()->can('create contract')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'full_name' => 'required|string|max:191',
                    'cnic_number' => 'required|string|max:191',
                    'email' => 'required|email|max:191',
                    'mobile_primary' => 'required|string|max:191',
                    'subject' => 'required|string|max:191',
                    'project_id' => 'required',
                    'unit_id' => 'required',
                    'sale_price' => 'required|numeric|min:0',
                    'booking_date' => 'required|date',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()], 422);
            }

            $deal = Deal::findOrFail($id);
            $user = \Auth::user();
            $creatorId = $user->creatorId();
            $ownedId = $user->ownedId();

            try {
                DB::beginTransaction();

                // 1. Handle Primary Customer
                $customer = Customer::where('cnic_number', $request->cnic_number)
                    ->where('created_by', $creatorId)
                    ->first();

                if (!$customer) {
                    $customer = Customer::where('email', $request->email)
                        ->where('created_by', $creatorId)
                        ->first();
                }

                if (!$customer) {
                    $customer = new Customer();
                    $latest = Customer::where('created_by', $creatorId)->latest()->first();
                    $customer->customer_id = $latest ? $latest->customer_id + 1 : 1;
                }

                $customer->fill($request->only([
                    'client_type', 'full_name', 'father_or_spouse_name', 'date_of_birth',
                    'nationality', 'cnic_number', 'mobile_primary', 'email',
                    'current_address', 'permanent_address'
                ]));
                $customer->name = $request->full_name;
                $customer->contact = $request->mobile_primary;
                $customer->created_by = $creatorId;
                $customer->owned_by = $ownedId;
                $customer->is_active = 1;
                $customer->save();

                // 2. Handle User (Client) linkage
                $clientUser = User::where('email', $request->email)->where('type', 'client')->first();
                if (!$clientUser) {
                    $clientUser = new User();
                    $clientUser->name = $request->full_name;
                    $clientUser->email = $request->email;
                    $clientUser->password = \Hash::make('password123');
                    $clientUser->type = 'client';
                    $clientUser->lang = 'en';
                    $clientUser->created_by = $creatorId;
                    $clientUser->owned_by = $ownedId;
                    $clientUser->save();

                    $role = \Spatie\Permission\Models\Role::where('name', 'client')->first();
                    if ($role) $clientUser->assignRole($role);
                }
                $customer->client_id = $clientUser->id;
                $customer->save();

                // 3. Create Contract
                $contract = new \App\Models\Contract();
                $contract->fill($request->only([
                    'subject', 'tower_id', 'floor_id', 'unit_id',
                    'sale_price', 'booking_date', 'agreement_date', 'possession_due_date',
                    'payment_plan_type'
                ]));
                
                $contract->re_project_id = $request->project_id;
                $contract->project_id = $deal->project_id; // compatibility
                $contract->customer_id = $customer->id;
                $contract->client_name = $clientUser->id;
                $contract->value = $request->sale_price;
                $contract->net_sale_price = $request->sale_price;
                $contract->deal_id = $id;
                $contract->status = 'pending';
                $contract->start_date = $request->booking_date;
                $contract->end_date = $request->possession_due_date ?? now()->addYears(2)->format('Y-m-d');
                $contract->created_by = $creatorId;
                $contract->owned_by = $ownedId;
                $contract->type = $request->type ?? 1; // fallback
                $contract->save();

                // 4. Handle Joint Owners
                if ($request->has('joint_owners')) {
                    foreach ($request->joint_owners as $jo) {
                        if (empty($jo['name']) || empty($jo['cnic'])) continue;

                        $jCustomer = Customer::where('cnic_number', $jo['cnic'])
                            ->where('created_by', $creatorId)
                            ->first();
                        
                        if (!$jCustomer) {
                            $jCustomer = new Customer();
                            $latestJ = Customer::where('created_by', $creatorId)->latest()->first();
                            $jCustomer->customer_id = $latestJ ? $latestJ->customer_id + 1 : 1;
                        }

                        $jCustomer->full_name = $jo['name'];
                        $jCustomer->name = $jo['name'];
                        $jCustomer->cnic_number = $jo['cnic'];
                        $jCustomer->mobile_primary = $jo['phone'];
                        $jCustomer->created_by = $creatorId;
                        $jCustomer->owned_by = $ownedId;
                        $jCustomer->is_active = 1;
                        $jCustomer->save();

                        ContractOwner::create([
                            'contract_id' => $contract->id,
                            'customer_id' => $jCustomer->id,
                            'ownership_percent' => $jo['percent'] ?? 0,
                            'role' => 'Secondary'
                        ]);
                    }
                }

                ContractOwner::create([
                    'contract_id' => $contract->id,
                    'customer_id' => $customer->id,
                    'ownership_percent' => 100 - (collect($request->joint_owners)->sum('percent') ?? 0),
                    'role' => 'Primary'
                ]);

                // 5. Freeze Installment Plan
                $installmentPlan = FrozenInstallmentPlan::create([
                    'contract_id' => $contract->id,
                    'down_payment_amount' => $request->down_payment_amount ?? 0,
                    'down_payment_due_date' => $request->booking_date,
                    'installment_count' => $request->installment_count ?? 1,
                    'installment_frequency' => $request->installment_frequency ?? 'Monthly',
                    'installment_amount' => $request->installment_amount ?? 0,
                    'total_payable' => $request->sale_price,
                    'plan_start_date' => $request->booking_date,
                ]);

                // 6. Documents Upload
                $docTypes = [
                    'doc_cnic_front' => 'CNIC_FRONT',
                    'doc_cnic_back' => 'CNIC_BACK',
                    'doc_photo' => 'PHOTO',
                    'doc_signature' => 'SIGNATURE'
                ];

                if (!file_exists(public_path('uploads/customer_documents'))) {
                    mkdir(public_path('uploads/customer_documents'), 0777, true);
                }

                foreach ($docTypes as $inputName => $type) {
                    if ($request->hasFile($inputName)) {
                        $file = $request->file($inputName);
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('uploads/customer_documents'), $fileName);
                        
                        CustomerDocument::create([
                            'customer_id' => $customer->id,
                            'document_type' => $type,
                            'file_path' => 'uploads/customer_documents/' . $fileName
                        ]);
                    }
                }

                // 7. Update Deal and Unit Status
                $deal->status = 'Won';
                $deal->contract_id = $contract->id;
                $deal->save();

                $unit = ReUnit::find($request->unit_id);
                if ($unit) {
                    $unit->status = 'Sold';
                    $unit->save();
                }

                // 8. Generate Installment Schedule
                if ($request->payment_plan_type === 'Installment' && $installmentPlan) {
                    $this->generateInstallmentSchedule($contract, $installmentPlan, $request->unit_id);
                }

                if ($request->has('customField')) {
                    CustomField::saveData($contract, $request->customField);
                }

                DB::commit();
                if($request->ajax()){
                    return response()->json([
                        'success' => true,
                        'message' => __('Deal successfully converted to contract.'),
                        'redirect' => route('contract.show', $contract->id)
                    ]);
                }
                return redirect()->route('contract.show', $contract->id)->with('success', __('Deal successfully converted to contract.'));

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Get payment plans by project (AJAX)
     */
    public function getPaymentPlansByProject(Request $request)
    {
        $projectId = $request->project_id;
        
        if (!$projectId) {
            return response()->json([]);
        }
        
        $project = \App\Models\ReProject::find($projectId);
        
        if (!$project) {
            return response()->json([]);
        }
        
        $plans = $project->paymentPlans()->where('is_active', true)->get()->map(function($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->plan_name,
                'down_payment_percentage' => $plan->down_payment_percentage,
                'down_payment_amount' => $plan->down_payment_amount,
                'num_installments' => $plan->num_installments,
                'frequency' => $plan->frequency,
                'possession_charges' => $plan->possession_charges,
                'discount' => $plan->discount,
                'extra_charges' => $plan->extra_charges,
            ];
        });
        
        return response()->json($plans);
    }

    /**
     * Generate installment schedule records for a contract
     */
    private function generateInstallmentSchedule($contract, $installmentPlan, $unitId)
    {
        $creatorId = \Auth::user()->creatorId();
        $ownedId = \Auth::user()->ownedId();
        
        // Determine frequency in months
        $frequency = $installmentPlan->installment_frequency ?? 'Monthly';
        $frequencyMonths = match($frequency) {
            'Monthly' => 1,
            'Quarterly' => 3,
            'Half-Yearly', '6 Months' => 6,
            'Yearly' => 12,
            default => 1,
        };
        
        $startDate = $installmentPlan->plan_start_date 
            ? \Carbon\Carbon::parse($installmentPlan->plan_start_date)
            : \Carbon\Carbon::now();
        
        $installmentNumber = 1;
        
        // 1. First installment is Down Payment
        if ($installmentPlan->down_payment_amount > 0) {
            \App\Models\ContractInstallment::create([
                'contract_id' => $contract->id,
                'unit_id' => $unitId,
                'installment_number' => $installmentNumber,
                'installment_type' => 'down_payment',
                'amount' => $installmentPlan->down_payment_amount,
                'issue_date' => $startDate->copy(),
                'due_date' => $installmentPlan->down_payment_due_date 
                    ? \Carbon\Carbon::parse($installmentPlan->down_payment_due_date)
                    : $startDate->copy()->addDays(7),
                'status' => 'pending',
                'description' => 'Down Payment',
                'created_by' => $creatorId,
                'owned_by' => $ownedId,
            ]);
            $installmentNumber++;
        }
        
        // 2. Generate remaining installments
        $numInstallments = $installmentPlan->installment_count ?? 0;
        $totalPayable = $installmentPlan->total_payable ?? 0;
        $downPayment = $installmentPlan->down_payment_amount ?? 0;
        
        // Calculate remaining amount after down payment
        $remainingAmount = $totalPayable - $downPayment;
        
        // Calculate per installment amount (rounded to 2 decimals)
        $installmentAmount = $numInstallments > 0 ? round($remainingAmount / $numInstallments, 2) : 0;
        
        // Track total disbursed for rounding adjustment
        $totalDisbursed = $downPayment;
        
        for ($i = 0; $i < $numInstallments; $i++) {
            $issueDate = $startDate->copy()->addMonths($frequencyMonths * $i);
            $dueDate = $issueDate->copy()->addDays(15); // 15 days grace period
            
            // For last installment, adjust for rounding difference
            $amount = $installmentAmount;
            if ($i == $numInstallments - 1) {
                // Last installment gets the remaining balance to ensure exact total
                $amount = round($totalPayable - $totalDisbursed, 2);
            }
            $totalDisbursed += $amount;
            
            \App\Models\ContractInstallment::create([
                'contract_id' => $contract->id,
                'unit_id' => $unitId,
                'installment_number' => $installmentNumber,
                'installment_type' => 'installment',
                'amount' => $amount,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => 'pending',
                'description' => 'Installment #' . ($i + 1),
                'created_by' => $creatorId,
                'owned_by' => $ownedId,
            ]);
            $installmentNumber++;
        }
    }
}

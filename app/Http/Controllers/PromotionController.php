<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Employee;
use App\Models\Promotion;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage promotion'))
        {
            if(Auth::user()->type == 'Employee')
            {
                $emp        = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $promotions = Promotion::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', '=', $emp->id)->with(['designation','employee'])->get();
            }
            else
            {
                $user = \Auth::user();
                $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
                $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
                $promotions = Promotion::where($column, '=',$ownerId)->with(['designation','employee'])->get();
            }

            return view('promotion.index', compact('promotions'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create promotion'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $designations = Designation::where($column,$ownerId)->get()->pluck('name', 'id');
            $employees    = Employee::where($column,$ownerId)-> where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get()->pluck('name', 'id');

            return view('promotion.create', compact('employees', 'designations'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if(\Auth::user()->can('create promotion'))
            {
                $validator = \Validator::make(
                    $request->all(), [
                                    'employee_id' => 'required',
                                    'designation_id' => 'required',
                                    'promotion_title' => 'required',
                                    'promotion_date' => 'required',
                                ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    if ($request->ajax()) {
                        return response()->json(['error' => $messages->first()], 400);
                    }
                    return redirect()->back()->with('error', $messages->first());
                }

                $promotion                  = new Promotion();
                $promotion->employee_id     = $request->employee_id;
                $promotion->designation_id  = $request->designation_id;
                $promotion->promotion_title = $request->promotion_title;
                $promotion->promotion_date  = $request->promotion_date;
                $promotion->description     = $request->description;
                $promotion->created_by      = \Auth::user()->creatorId();
                $promotion->owned_by        = \Auth::user()->ownedId();
                $promotion->save();
                if($promotion->employee_id){
                    $emp = Employee::find($promotion->employee_id);
                    $emp->termination_date = $promotion->promotion_date;
                    $emp->save();
                }
                
                DB::commit();

                // Load relationships for notifications
                $promotion->load('employee', 'designation');
                $employeeName = $promotion->employee->name ?? '';

                // Create activity log
                Utility::makeActivityLog(\Auth::user()->id, 'Promotion', $promotion->id, 'Create Promotion', $employeeName);

                // Send notifications
                $userarr = array_filter([
                    \Auth::user()->id,
                    $promotion->employee->report_to ?? null,
                    $promotion->employee->id,
                ]);

                // Remove empty values
                $userarr = array_filter($userarr, function ($value) {
                    return !is_null($value) && $value !== '';
                });
                
                $dataarr = [
                    "updated_by" => Auth::user()->id,
                    "data_id" => $promotion->id,
                    "promotion" => $promotion->promotion_title,
                    "name" => $employeeName,
                ];
                
                foreach($userarr as $notifyto) {
                    Utility::makeNotification($notifyto, 'promotion', $dataarr, $promotion->id, 'Promoted as ' . $promotion->promotion_title, \Auth::user()->name);
                }

                // Handle email notification
                $emailStatus = '';
                $setings = Utility::settings();
                if(isset($setings['promotion_sent']) && $setings['promotion_sent'] == 1) {
                    if ($promotion->employee && $promotion->employee->email) {
                        $promotionArr = [
                            'employee_name' => $employeeName,
                            'promotion_designation' => $promotion->designation->name ?? '',
                            'promotion_title' => $promotion->promotion_title,
                            'promotion_date' => $promotion->promotion_date,
                        ];

                        $resp = Utility::sendEmailTemplate('promotion_sent', [$promotion->employee->email], $promotionArr);
                        if(!$resp['is_success']) {
                            $emailStatus = '<br> <span class="text-danger">' . $resp['error'] . '</span>';
                        }
                    } else {
                        $emailStatus = '<br> <span class="text-danger">' . __('Employee email not found') . '</span>';
                    }
                }

                // Handle AJAX response
                if ($request->ajax()) {
                    $html = view('promotion.appendrow', compact('promotion'))->render();
                    $data = [
                        'datarow' => $html,
                        'table_id' => "promotion-table",
                        'action' => 'add',
                        'row_id' => $promotion->id,
                    ];
                    $message = __('Promotion successfully created.') . $emailStatus;
                    return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
                }

                return redirect()->route('promotion.index')->with('success', __('Promotion successfully created.') . $emailStatus);
            }
            else
            {
                if ($request->ajax()) {
                    return response()->json(['error' => __('Permission denied.')], 401);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(Promotion $promotion)
    {
        return redirect()->route('promotion.index');
    }

    public function edit(Promotion $promotion)
    {
        $user = \Auth::user();
        $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
        $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
        $designations = Designation::where($column,$ownerId)->get()->pluck('name', 'id');
        $employees    = Employee::where($column,$ownerId)-> where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get()->pluck('name', 'id');
        if(\Auth::user()->can('edit promotion'))
        {
            if($promotion->created_by == \Auth::user()->creatorId())
            {
                return view('promotion.edit', compact('promotion', 'employees', 'designations'));
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

    public function update(Request $request, Promotion $promotion)
    {
        DB::beginTransaction();
        try {
            if(\Auth::user()->can('edit promotion')) {
                if($promotion->created_by == \Auth::user()->creatorId()) {
                    $validator = \Validator::make( $request->all(), [
                        'employee_id' => 'required',
                        'designation_id' => 'required',
                        'promotion_title' => 'required',
                        'promotion_date' => 'required',
                    ]);
                    
                    if($validator->fails()) {
                        $messages = $validator->getMessageBag();
                        if ($request->ajax()) {
                            return response()->json(['error' => $messages->first()], 400);
                        }
                        return redirect()->back()->with('error', $messages->first());
                    }

                    $promotion->employee_id     = $request->employee_id;
                    $promotion->designation_id  = $request->designation_id;
                    $promotion->promotion_title = $request->promotion_title;
                    $promotion->promotion_date  = $request->promotion_date;
                    $promotion->description     = $request->description;
                    $promotion->save();
                    if($promotion->employee_id){
                        $emp = Employee::find($promotion->employee_id);
                        $emp->termination_date = $promotion->promotion_date;
                        $emp->save();
                    }
                    
                    DB::commit();
                    
                    // Create activity log
                    Utility::makeActivityLog(
                        \Auth::user()->id,
                        'Promotion',
                        $promotion->id,
                        'Update Promotion',
                        $promotion->employee->name ?? ''
                    );

                    // Handle AJAX response
                    if ($request->ajax()) {
                        $html = view('promotion.appendrow', compact('promotion'))->render();
                        $data = [
                            'datarow' => $html,
                            'table_id' => "promotion-table",
                            'action' => 'edit',
                            'row_id' => $promotion->id,
                        ];
                        return response()->json(['success' => true, 'message' => __('Promotion successfully updated.'), 'data' => $data]);
                    }

                    return redirect()->route('promotion.index')->with('success', __('Promotion successfully updated.'));
                }
                else {
                    if ($request->ajax()) {
                        return response()->json(['error' => __('Permission denied.')], 401);
                    }
                    return redirect()->back()->with('error', __('Permission denied.')); 
                }
            }
            else
            {
                if ($request->ajax()) {
                    return response()->json(['error' => __('Permission denied.')], 401);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Promotion $promotion)
    {
        if(\Auth::user()->can('delete promotion'))
        {
            if($promotion->created_by == \Auth::user()->creatorId())
            {
                $employeeName = $promotion->employee->name ?? '';
                Utility::makeActivityLog(\Auth::user()->id, 'Promotion', $promotion->id, 'Delete Promotion', $employeeName);
                $promotion->delete();

                return redirect()->route('promotion.index')->with('success', __('Promotion successfully deleted.'));
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
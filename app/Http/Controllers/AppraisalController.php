<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\Branch;
use App\Models\Competencies;
use App\Models\Employee;
use App\Models\Indicator;
use App\Models\Performance_Type;
use App\Models\PerformanceType;
use App\Models\Utility;
use Illuminate\Http\Request;
use Auth;

class AppraisalController extends Controller
{

    public function index()
{
    if (\Auth::user()->can('manage appraisal')) {
        $user = \Auth::user();
        $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
        $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();
        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $competencyCount = Competencies::where($column, '=', $ownerId)->count();
            $appraisals = Appraisal::where($column, '=', $ownerId)
                ->where('branch', $employee->branch_id)
                ->where('employee', $employee->id)
                ->with(['employees', 'branches'])
                ->get();
        } else {
            $competencyCount = Competencies::where($column, '=', $ownerId)->count();
            $appraisals = Appraisal::where($column, '=', $ownerId)
                ->with(['employees', 'branches'])
                ->get();
        }
        return view('appraisal.index', compact('appraisals', 'competencyCount'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}


public function create()
{
    if (\Auth::user()->can('create appraisal')) {
        $user = \Auth::user();
        $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
        $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();
        $performance = PerformanceType::where($column, '=', $ownerId)->get();
        $brances = Branch::where($column, '=', $ownerId)->get();

        return view('appraisal.create', compact('brances', 'performance'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}


    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            if(!\Auth::user()->can('create appraisal')) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.')
                ], 403);
            }
            
            $validator = \Validator::make(
                $request->all(), [
                    'branch' => 'required',
                    'employee' => 'required',
                ]
            );
            
            if($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json([
                    'success' => false,
                    'message' => $messages->first()
                ], 400);
            }
            
            $appraisal = new Appraisal();
            $appraisal->branch = $request->branch;
            $appraisal->employee = $request->employee;
            $appraisal->appraisal_date = $request->appraisal_date;
            $appraisal->rating = json_encode($request->rating, true);
            $appraisal->remark = $request->remark;
            $appraisal->created_by = \Auth::user()->creatorId();
            $appraisal->owned_by = \Auth::user()->ownedId();
            $appraisal->save();

            if($appraisal->employee){
                $emp = Employee::find($appraisal->employee);
                $emp->termination_date = $appraisal->appraisal_date;
                $emp->save();
            }
            
            \DB::commit();
            
            Utility::makeActivityLog(\Auth::user()->id, 'Appraisal', $appraisal->id, 'Create Appraisal', $appraisal->remark);
            
            // Get the competency count - this is what's missing
            $user = \Auth::user();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();
            $competencyCount = Competencies::where($column, '=', $ownerId)->count();
            
            $html = view('appraisal.appendrow', compact('appraisal', 'competencyCount'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "appraisal-table",
                'action' => 'add',
                'row_id' => $appraisal->id,
            ];
            
            return response()->json([
                'success' => true,
                'message' => __('Appraisal successfully created.'),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Appraisal $appraisal)
{
    $user = \Auth::user();
    $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
    $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();
    $rating = json_decode($appraisal->rating, true);
    $performance_types = PerformanceType::where($column, '=', $ownerId)->get();
    $employee = Employee::find($appraisal->employee);
    $indicator = Indicator::where('branch', $employee->branch_id)
        ->where('department', $employee->department_id)
        ->where('designation', $employee->designation_id)
        ->where($column, '=', $ownerId) 
        ->first();

    $ratings = $indicator ? json_decode($indicator->rating, true) : null;

    return view('appraisal.show', compact('appraisal', 'performance_types', 'ratings', 'rating'));
}


public function edit(Appraisal $appraisal)
{
    if (\Auth::user()->can('edit appraisal')) {
        $user = \Auth::user();
        $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
        $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();
        $performance_types = PerformanceType::where($column, '=', $ownerId)->get();
        $brances = Branch::where($column, '=', $ownerId)->get();
        $ratings = json_decode($appraisal->rating, true);

        return view('appraisal.edit', compact('brances', 'appraisal', 'performance_types', 'ratings'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}


    public function update(Request $request, Appraisal $appraisal)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit appraisal'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'branch' => 'required',
                                   'employee' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $appraisal->branch         = $request->branch;
            $appraisal->employee       = $request->employee;
            $appraisal->appraisal_date = $request->appraisal_date;
            $appraisal->rating         = json_encode($request->rating, true);
            $appraisal->remark         = $request->remark;
            $appraisal->save();
            if($appraisal->employee){
                $emp = Employee::find($appraisal->employee);
                $emp->termination_date = $appraisal->appraisal_date;
                $emp->save();
            }
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id,'Appraisal',$appraisal->id,'Update Appraisal',$appraisal->remark);
            $html = view('appraisal.appendrow', compact('appraisal'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "appraisal-table",
                'action' => 'edit',
                'row_id' => $appraisal->id,
            ];
            return response()->json(['success' => true, 'message' => __('Appraisal successfully updated.'), 'data' => $data]);

            // return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully updated.'));
        }
    } catch (\Exception $e) {
        \DB::rollback();
        dd($e);
        return redirect()->back()->with('error', $e);
    }
    }
    
    public function destroy(Appraisal $appraisal)
    {
        if(\Auth::user()->can('delete appraisal'))
        {
            if($appraisal->created_by == \Auth::user()->creatorId())
            {
                Utility::makeActivityLog(\Auth::user()->id,'Appraisal',$appraisal->id,'Update Appraisal',$appraisal->remark);
                $appraisal->delete();

                return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully deleted.'));
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

    public function empByStar(Request $request)
    {
        $employee = Employee::find($request->employee);

        $indicator = Indicator::where('branch',$employee->branch_id)->where('department',$employee->department_id)->where('designation',$employee->designation_id)->first();

        $ratings = !empty($indicator)? json_decode($indicator->rating, true):[];
        $user = \Auth::user();
        $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
        $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();

        $performance_types = PerformanceType::where($column, '=', $ownerId)->get();

        $viewRender = view('appraisal.star', compact('ratings','performance_types'))->render();
        // dd($viewRender);
        return response()->json(array('success' => true, 'html'=>$viewRender));

    }

    public function empByStar1(Request $request)
    {
        $employee = Employee::find($request->employee);

        $appraisal = Appraisal::find($request->appraisal);

        $indicator = Indicator::where('branch',$employee->branch_id)->where('department',$employee->department_id)->where('designation',$employee->designation_id)->first();

        if ($indicator != null) {
            $ratings = json_decode($indicator->rating, true);
        }else {
            $ratings = null;
        }
        $rating = json_decode($appraisal->rating,true);
        $user = \Auth::user();
        $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
        $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();
        $performance_types = PerformanceType::where($column, '=', $ownerId)->get();
        $viewRender = view('appraisal.staredit', compact('ratings','rating','performance_types'))->render();
        // dd($viewRender);
        return response()->json(array('success' => true, 'html'=>$viewRender));

    }

    public function getemployee(Request $request)
    {
        $data['employee'] = Employee::where('branch_id',$request->branch_id)->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get();;
        return response()->json($data);
    }

    public function appraisal_list()
    {

        $user = \Auth::user();

            $emp = Employee::where('report_to',@Auth::user()->employee->id)->where(function($query) {
    $query->whereNull('termination_date')
          ->orWhere('termination_date', '>', now()->endOfMonth());})->get()->pluck('id');
            $user = \Auth::user();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $ownerId = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();
            $competencyCount = Competencies::where($column, '=', $ownerId)->count();

            $appraisals = Appraisal::with(['employees','branches'])->where($column, '=', $ownerId)->whereHas('employees', function ($query) use ($emp) {
                $query->whereIn('id', $emp);
            })->get();

        return view('appraisal.index', compact('appraisals','competencyCount'));
    }
}

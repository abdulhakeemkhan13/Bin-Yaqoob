<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Utility;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage leave type'))
        {
            $user = \Auth::user();
            
            if ($user->type == 'company') {
                $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
            } else {
                $leavetypes = LeaveType::where(function ($query) use ($user) {
                    $query->where('created_by', $user->creatorId())  
                        ->orWhere('owned_by', $user->ownedId()); 
                })->get();
            }
            return view('leavetype.index', compact('leavetypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {

        if(\Auth::user()->can('create leave type'))
        {
            return view('leavetype.create');
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
        if(\Auth::user()->can('create leave type'))
        {

            $validator = \Validator::make(
                $request->all(), [
                'title' => 'required',
                'days' => 'required',
            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $leavetype             = new LeaveType();
            $leavetype->title      = $request->title;
            $leavetype->days       = $request->days;
            $leavetype->created_by = \Auth::user()->creatorId();
            $leavetype->owned_by = \Auth::user()->ownedId();
            $leavetype->save();
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id,'Leave Type',$leavetype->id,'Create Leave Type',$leavetype->name);
            $html = view('leavetype.appendrow', compact('leavetype'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "leavetype-table",
                'action' => 'add',
                'row_id' => $leavetype->id,
            ];
            return response()->json(['success' => true, 'message' => __('LeaveType  successfully created.'), 'data' => $data]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }

    public function show(LeaveType $leavetype)
    {
        return redirect()->route('leavetype.index');
    }

    public function edit(LeaveType $leavetype)
    {
        if(\Auth::user()->can('edit leave type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {

                return view('leavetype.edit', compact('leavetype'));
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

    public function update(Request $request, LeaveType $leavetype)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit leave type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                    'title' => 'required',
                    'days' => 'required',
                ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leavetype->title = $request->title;
                $leavetype->days  = $request->days;
                $leavetype->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Leave Type',$leavetype->id,'Update Leave Type',$leavetype->name);
                $html = view('leavetype.appendrow', compact('leavetype'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "leavetype-table",
                    'action' => 'edit',
                    'row_id' => $leavetype->id,
                ];
                return response()->json(['success' => true, 'message' => __('LeaveType successfully updated.'), 'data' => $data]);
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
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }

    public function destroy(LeaveType $leavetype)
    {
        if(\Auth::user()->can('delete leave type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {
                Utility::makeActivityLog(\Auth::user()->id,'Leave Type',$leavetype->id,'Delete Leave Type',$leavetype->name);
                $leavetype->delete();

                return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully deleted.'));
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

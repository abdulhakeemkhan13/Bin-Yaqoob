<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{

    public function index()
    {
        if(Auth::user()->can('manage goal'))
        {
            $golas = Goal::where('created_by', '=', Auth::user()->creatorId())->get();

            return view('goal.index', compact('golas'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function create()
    {
        if(Auth::user()->can('create goal'))
        {
            $types = Goal::$goalType;

            return view('goal.create', compact('types'));
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
        if(Auth::user()->can('create goal'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'type' => 'required',
                                   'from' => 'required',
                                   'to' => 'required',
                                   'amount' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()
                    ->json(['error' => $messages->first()], 400);
            }

            $goal             = new Goal();
            $goal->name       = $request->name;
            $goal->type       = $request->type;
            $goal->from       = $request->from;
            $goal->to         = $request->to;
            $goal->amount     = $request->amount;
            $goal->is_display = isset($request->is_display) ? 1 : 0;
            $goal->created_by = Auth::user()->creatorId();
            $goal->save();
            \DB::commit();
            $html = view('goal.appendrow', compact('goal'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "goal-table",
                'action' => 'add',
                'row_id' => $goal->id,
            ];
            return response()->json(['success' => true, 'message' => __('Goal successfully created.'), 'data' => $data]);
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }


    public function show(Goal $goal)
    {
        //
    }


    public function edit(Goal $goal)
    {
        if(Auth::user()->can('create goal'))
        {
            $types = Goal::$goalType;

            return view('goal.edit', compact('types', 'goal'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, Goal $goal)
    {
        \DB::beginTransaction();
        try {
        if(Auth::user()->can('edit goal'))
        {
            if($goal->created_by == Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required',
                                       'type' => 'required',
                                       'from' => 'required',
                                       'to' => 'required',
                                       'amount' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return response()
                        ->json(['error' => $messages->first()], 400);
                }

                $goal->name       = $request->name;
                $goal->type       = $request->type;
                $goal->from       = $request->from;
                $goal->to         = $request->to;
                $goal->amount     = $request->amount;
                $goal->is_display = isset($request->is_display) ? 1 : 0;
                $goal->save();
                \DB::commit();
                $html = view('goal.appendrow', compact('goal'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "goal-table",
                    'action' => 'edit',
                    'row_id' => $goal->id,
                ];
                return response()->json(['success' => true, 'message' => __('Goal successfully updated.'), 'data' => $data]);
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
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }


    public function destroy(Goal $goal)
    {
        if(Auth::user()->can('delete goal'))
        {
            if($goal->created_by == Auth::user()->creatorId())
            {
                $goal->delete();

                return redirect()->route('goal.index')->with('success', __('Goal successfully deleted.'));
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

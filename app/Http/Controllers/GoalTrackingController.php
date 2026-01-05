<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\GoalTracking;
use App\Models\GoalType;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GoalTrackingController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage goal tracking')) {
            $user = Auth::user();
            // Determine the column to use (created_by or owned_by) based on user type
            $column = ($user->type == 'Employee' || $user->type == 'company') ? 'created_by' : 'owned_by';
            $ownerId = ($user->type == 'Employee' || $user->type == 'company') ? $user->creatorId() : $user->ownedId();

            // Debug information
            \Log::info('User type: ' . $user->type);
            \Log::info('Column used: ' . $column);
            \Log::info('Owner ID: ' . $ownerId);

            // Simplified query to get all goal trackings
            $goalTrackings = GoalTracking::where(function($query) use ($column, $ownerId) {
                $query->where($column, '=', $ownerId)
                      ->orWhere('created_by', '=', $ownerId);
            })
            ->with(['goalType', 'branches'])
            ->get();
            
            // Log the count for debugging
            \Log::info('Goal Trackings count: ' . $goalTrackings->count());
            
            return view('goaltracking.index', compact('goalTrackings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('create goal tracking')) {
            $user = Auth::user();
            // Determine the column for fetching data
            $column = ($user->type == 'Employee'  || $user->type == 'company') ? 'created_by' : 'owned_by';
            $ownerId = ($user->type == 'Employee'  || $user->type == 'company') ? $user->creatorId() : $user->ownedId();

            $brances = Branch::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $brances->prepend('Select Branch', '');
            $goalTypes = GoalType::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $goalTypes->prepend('Select Goal Type', '');
            $status = GoalTracking::$status;

            return view('goaltracking.create', compact('brances', 'goalTypes', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            if (Auth::user()->can('create goal tracking')) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'branch' => 'required',
                        'goal_type' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'subject' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return response()->json(['success' => false, 'message' => $messages->first()], 422);
                }

                $goalTracking = new GoalTracking();
                $goalTracking->branch = $request->branch;
                $goalTracking->goal_type = $request->goal_type;
                $goalTracking->start_date = $request->start_date;
                $goalTracking->end_date = $request->end_date;
                $goalTracking->subject = $request->subject;
                $goalTracking->target_achievement = $request->target_achievement;
                $goalTracking->description = $request->description;
                $goalTracking->created_by = Auth::user()->creatorId();
                $goalTracking->owned_by = Auth::user()->ownedId();
                $goalTracking->save();
                \DB::commit();
                Utility::makeActivityLog(Auth::user()->id, 'Goal Tracking', $goalTracking->id, 'Create Goal Tracking', $goalTracking->subject);
                $html = view('goaltracking.appendrow', compact('goalTracking'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "goaltracking-table",
                    'action' => 'add',
                    'row_id' => $goalTracking->id,
                ];
                return response()->json(['success' => true, 'message' => __('Goal tracking successfully created.'), "data" => $data]);
            } else {
                return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        if (\Auth::user()->can('edit goal tracking')) {
            $user = \Auth::user();
            // Determine the column for fetching data
            $column = ($user->type == 'Employee'  || $user->type == 'company') ? 'created_by' : 'owned_by';
            $ownerId = ($user->type == 'Employee'  || $user->type == 'company') ? $user->creatorId() : $user->ownedId();

            $goalTracking = GoalTracking::find($id);
            $brances = Branch::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $brances->prepend('Select Branch', '');
            $goalTypes = GoalType::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $goalTypes->prepend('Select Goal Type', '');
            $status = GoalTracking::$status;

            $ratings = json_decode($goalTracking->rating, true);

            return view('goaltracking.edit', compact('brances', 'goalTypes', 'goalTracking', 'ratings', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            if (\Auth::user()->can('edit goal tracking')) {
                $goalTracking = GoalTracking::find($id);
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'branch' => 'required',
                        'goal_type' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'subject' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $goalTracking->branch = $request->branch;
                $goalTracking->goal_type = $request->goal_type;
                $goalTracking->start_date = $request->start_date;
                $goalTracking->end_date = $request->end_date;
                $goalTracking->subject = $request->subject;
                $goalTracking->target_achievement = $request->target_achievement;
                $goalTracking->status = $request->status;
                $goalTracking->progress = $request->progress;
                $goalTracking->description = $request->description;
                $goalTracking->rating = json_encode($request->rating, true);
                $goalTracking->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id, 'Goal Tracking', $goalTracking->id, 'Update Goal Tracking', $goalTracking->subject);
                $html = view('goaltracking.appendrow', compact('goalTracking'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "goaltracking-table",
                    'action' => 'edit',
                    'row_id' => $goalTracking->id,
                ];
                return response()->json(['success' => true, 'message' => __('Goal tracking successfully updated.'), 'data' => $data]);
                // return redirect()->route('goaltracking.index')->with('success', __('Goal tracking successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete goal tracking')) {
            $goalTracking = GoalTracking::find($id);
            if ($goalTracking->created_by == \Auth::user()->creatorId()) {
                Utility::makeActivityLog(\Auth::user()->id, 'Goal Tracking', $goalTracking->id, 'Delete Goal Tracking', $goalTracking->subject);
                $goalTracking->delete();
                return redirect()->route('goaltracking.index')->with('success', __('GoalTracking successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

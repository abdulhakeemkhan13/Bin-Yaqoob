<?php

namespace App\Http\Controllers;

use App\Models\AwardType;
use App\Models\Utility;
use Illuminate\Http\Request;

class AwardTypeController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage award type')) {
            $user = \Auth::user();
            if ($user->type == 'company') {
                $awardtypes = AwardType::where('created_by', '=', $user->creatorId())->get();
            } else {
                $awardtypes = AwardType::where(function ($query) use ($user) {
                    $query->where('created_by', $user->creatorId())
                          ->orWhere('owned_by', $user->ownedId());
                })->get();
            }

            return view('awardtype.index', compact('awardtypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create award type')) {
            return view('awardtype.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('create award type')) {
            $validator = \Validator::make(
                $request->all(),
                ['name' => 'required|max:20']
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $awardtype = new AwardType();
            $awardtype->name = $request->name;
            $awardtype->created_by = \Auth::user()->creatorId();
            $awardtype->owned_by = \Auth::user()->ownedId();
            $awardtype->save();
            \DB::commit();

            Utility::makeActivityLog(\Auth::user()->id, 'Award Type', $awardtype->id, 'Create Award Type', $awardtype->name);

            $html = view('awardtype.appendrow', compact('awardtype'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "awardtype-table",
                'action' => 'add',
                'row_id' => $awardtype->id,
            ];
            return response()->json(['success' => true, 'message' => __('AwardType successfully created.'), 'data' => $data]);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }

    public function show(AwardType $awardType)
    {
        return redirect()->route('awardtype.index');
    }

    public function edit($id)
    {
        if (\Auth::user()->can('edit award type')) {
            $awardtype = AwardType::find($id); // Changed variable name from $awardType to $awardtype
            if ($awardtype->created_by == \Auth::user()->creatorId() || $awardtype->owned_by == \Auth::user()->ownedId()) {
                return view('awardtype.edit', compact('awardtype')); // Using the correct variable name
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('edit award type')) {
            $awardtype = AwardType::find($id); // Changed variable name from $awardType to $awardtype
            if ($awardtype->created_by == \Auth::user()->creatorId() || $awardtype->owned_by == \Auth::user()->ownedId()) {
                $validator = \Validator::make(
                    $request->all(),
                    ['name' => 'required|max:20']
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $awardtype->name = $request->name;
                $awardtype->save();
                \DB::commit();

                Utility::makeActivityLog(\Auth::user()->id, 'Award Type', $awardtype->id, 'Update Award Type', $awardtype->name);

                $html = view('awardtype.appendrow', compact('awardtype'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "awardtype-table",
                    'action' => 'edit',
                    'row_id' => $awardtype->id,
                ];
                return response()->json(['success' => true, 'message' => __('AwardType successfully updated.'), 'data' => $data]);
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete award type')) {
            $awardType = AwardType::find($id);
            if ($awardType->created_by == \Auth::user()->creatorId()) {
                Utility::makeActivityLog(\Auth::user()->id, 'Award Type', $awardType->id, 'Delete Award Type', $awardType->name);

                $awardType->delete();

                return redirect()->route('awardtype.index')->with('success', __('AwardType successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

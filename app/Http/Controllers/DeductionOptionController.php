<?php

namespace App\Http\Controllers;

use App\Models\DeductionOption;
use App\Models\Utility;
use Illuminate\Http\Request;

class DeductionOptionController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage deduction option')) {
            $user = \Auth::user();
            if ($user->type == 'company') {
                $deductionoptions = DeductionOption::where('created_by', '=', $user->creatorId())->get();
            } else {
                $deductionoptions = DeductionOption::where(function ($query) use ($user) {
                    $query->where('created_by', $user->creatorId())
                        ->orWhere('owned_by', $user->ownedId());
                })->get();
            }
            return view('deductionoption.index', compact('deductionoptions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create deduction option')) {
            return view('deductionoption.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('create deduction option')) {
            $validator = \Validator::make(
                $request->all(),
                ['name' => 'required']
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $deductionoption = new DeductionOption();
            $deductionoption->name = $request->name;
            $deductionoption->created_by = \Auth::user()->creatorId();
            $deductionoption->owned_by = \Auth::user()->ownedId();
            $deductionoption->save();
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id, 'Deduction Option', $deductionoption->id, 'Create Deduction Option', $deductionoption->name);
            $html = view('deductionoption.appendrow', compact('deductionoption'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "deductionoption-table",
                'action' => 'add',
                'row_id' => $deductionoption->id,
            ];
            return response()->json(['success' => true, 'message' => __('Deduction Option successfully created.'), 'data' => $data]);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }

    public function show(DeductionOption $deductionoption)
    {
        return redirect()->route('deductionoption.index');
    }

    public function edit($deductionoption)
    {
        $deductionoption = DeductionOption::find($deductionoption);

        if (\Auth::user()->can('edit deduction option')) {
            if ($deductionoption->created_by == \Auth::user()->creatorId()) {
                return view('deductionoption.edit', compact('deductionoption'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, DeductionOption $deductionoption)
    {
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('edit deduction option')) {
            if ($deductionoption->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    ['name' => 'required']
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $deductionoption->name = $request->name;
                $deductionoption->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id, 'Deduction Option', $deductionoption->id, 'Update Deduction Option', $deductionoption->name);
                $html = view('deductionoption.appendrow', compact('deductionoption'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "deductionoption-table",
                    'action' => 'edit',
                    'row_id' => $deductionoption->id,
                ];
                return response()->json(['success' => true, 'message' => __('Deduction Option successfully updated.'), 'data' => $data]);
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

    public function destroy(DeductionOption $deductionoption)
    {
        if (\Auth::user()->can('delete deduction option')) {
            if ($deductionoption->created_by == \Auth::user()->creatorId()) {
                Utility::makeActivityLog(\Auth::user()->id, 'Deduction Option', $deductionoption->id, 'Delete Deduction Option', $deductionoption->name);

                $deductionoption->delete();

                return redirect()->route('deductionoption.index')->with('success', __('Deduction Option successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

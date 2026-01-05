<?php

namespace App\Http\Controllers;

use App\Models\PerformanceType;
use Illuminate\Http\Request;
use App\Models\Utility;

class PerformanceTypeController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage performance type'))
        {
            if(\Auth::user()->type == 'company')
            {
                $types = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
                return view('performanceType.index', compact('types'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }

    public function create()
    {
        return view('performanceType.create');
    }


    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            if(\Auth::user()->can('create performance type'))
            {
                if(\Auth::user()->type == 'company')
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'name' => 'required',
                                       ]
                    );
                    if($validator->fails())
                    {
                        $messages = $validator->getMessageBag();

                        return redirect()->back()->with('error', $messages->first());
                    }

                    $type = new PerformanceType(); // Changed variable name from $types to $type
                    $type->name = $request->name;
                    $type->created_by = \Auth::user()->creatorId();
                    $type->owned_by = \Auth::user()->ownedId();
                    $type->save();
                    \DB::commit();
                    
                    Utility::makeActivityLog(\Auth::user()->id, 'Performance Type', $type->id, 'Create Performance Type', $type->name);
                    
                    // Pass the variable as 'type' to match what the view expects
                    $html = view('performanceType.appendrow', compact('type'))->render();
                    $data = [
                        'datarow' => $html,
                        'table_id' => "performanceType-table",
                        'action' => 'add',
                        'row_id' => $type->id,
                    ];
                    return response()->json(['success' => true, 'message' => __('Performance Type successfully created.'), 'data' => $data]);
                }
                else
                {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }


    public function show(PerformanceType $performanceType)
    {
        //
    }


    public function edit(PerformanceType $performanceType)
    {
        return view('performanceType.edit', compact('performanceType'));
    }


    public function update(Request $request, PerformanceType $performanceType)
    {
        \DB::beginTransaction();
        try {
            if(\Auth::user()->can('edit performance type'))
            {
                if(\Auth::user()->type == 'company')
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'name' => 'required',
                                       ]
                    );
                    if($validator->fails())
                    {
                        $messages = $validator->getMessageBag();

                        return redirect()->back()->with('error', $messages->first());
                    }

                    $performanceType->name = $request->name;
                    $performanceType->created_by = \Auth::user()->creatorId();
                    $performanceType->owned_by = \Auth::user()->ownedId();
                    $performanceType->save();
                    \DB::commit();
                    
                    Utility::makeActivityLog(\Auth::user()->id, 'Performance Type', $performanceType->id, 'Update Performance Type', $performanceType->name);
                    
                    // Pass the variable as 'type' to match what the view expects
                    $type = $performanceType; // Rename to match view expectation
                    $html = view('performanceType.appendrow', compact('type'))->render();
                    $data = [
                        'datarow' => $html,
                        'table_id' => "performanceType-table",
                        'action' => 'edit',
                        'row_id' => $performanceType->id,
                    ];
                    return response()->json(['success' => true, 'message' => __('Performance Type successfully updated.'), 'data' => $data]);
                }
                else
                {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }


    public function destroy(PerformanceType $performanceType)
    {

        if(\Auth::user()->can('delete performance type'))
        {
            if(\Auth::user()->type == 'company')
            {

                $performanceType->delete();

                return redirect()->route('performanceType.index')->with('success', __('Performance Type successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }


    }
}

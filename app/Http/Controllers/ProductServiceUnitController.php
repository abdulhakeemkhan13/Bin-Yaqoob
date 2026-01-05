<?php

namespace App\Http\Controllers;

use App\Models\ProductService;
use App\Models\ProductServiceUnit;
use App\Models\Utility;
use Illuminate\Http\Request;

class ProductServiceUnitController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage constant unit'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $units = ProductServiceUnit::where($column, '=',$ownerId)->get();

            return view('productServiceUnit.index', compact('units'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create constant unit'))
        {
            return view('productServiceUnit.create');
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
        if(\Auth::user()->can('create constant unit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $unit = new ProductServiceUnit();
            $unit->name = $request->name;
            $unit->created_by = \Auth::user()->creatorId();
            $unit->owned_by = \Auth::user()->ownedId();
            $unit->save();
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id,'Product Service Unit',$unit->id,'Create Product Service Unit',$unit->name);
            $html = view('productServiceUnit.appendrow', compact('unit'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "unit-table",
                'action' => 'add',
                'row_id' => $unit->id,
            ];
            return response()->json(['success' => true, 'message' => __('Unit successfully created.'), 'data' => $data]);
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


    public function edit($id)
    {
        if(\Auth::user()->can('edit constant unit'))
        {
            $unit = ProductServiceUnit::find($id);

            return view('productServiceUnit.edit', compact('unit'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit constant unit'))
        {
            $unit = ProductServiceUnit::find($id);
            if($unit->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required|max:20',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $unit->name = $request->name;
                $unit->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Product Service Unit',$unit->id,'Update Product Service Unit',$unit->name);
                $html = view('productServiceUnit.appendrow', compact('unit'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "unit-table",
                    'action' => 'edit',
                    'row_id' => $unit->id,
                ];
                return response()->json(['success' => true, 'message' => __('Unit successfully updated.'), 'data' => $data]);
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

    public function destroy($id)
    {
        if(\Auth::user()->can('delete constant unit'))
        {
            $unit = ProductServiceUnit::find($id);
            if($unit->created_by == \Auth::user()->creatorId())
            {
                $units = ProductService::where('unit_id', $unit->id)->first();
                if(!empty($units))
                {
                    return redirect()->back()->with('error', __('this unit is already assign so please move or remove this unit related data.'));
                }
                
                Utility::makeActivityLog(\Auth::user()->id,'Product Service Unit',$unit->id,'Delete Product Service Unit',$unit->name);
                $unit->delete();

                return redirect()->route('product-unit.index')->with('success', __('Unit successfully deleted.'));
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

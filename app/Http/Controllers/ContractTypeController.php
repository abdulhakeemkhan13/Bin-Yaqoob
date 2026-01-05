<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Utility;
use Illuminate\Http\Request;

class ContractTypeController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage contract type'))
        {
            if(\Auth::user()->type == 'company')
            {
                $types = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get();
                return view('contractType.index', compact('types'));
            }else if (\Auth::user()->type == 'branch'){ 
                $types = ContractType::where('owned_by', '=', \Auth::user()->ownedId())->get();
                return view('contractType.index', compact('types'));
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

    public function create()
    {
        return view('contractType.create');
    }


    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            if(\Auth::user()->type == 'company' || \Auth::user()->type == 'branch')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();
                    return response()->json(['error' => $messages->first()], 422);
                }

                $contractType = new ContractType();
                $contractType->name = $request->name;
                $contractType->created_by = \Auth::user()->creatorId();
                $contractType->owned_by = \Auth::user()->ownedId();
                $contractType->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Contract Type',$contractType->id,'Create Contract Type',$contractType->name);
                
                // Pass the contract type to the view with the variable name 'contractType'
                $html = view('contractType.appendrow', ['contractType' => $contractType])->render();
                
                $data = [
                    'datarow' => $html,
                    'table_id' => "contracttype-table",
                    'action' => 'add',
                    'row_id' => $contractType->id,
                ];
                return response()->json(['success' => true, 'message' => __('ContractType successfully created.'), 'data' => $data]);
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


    public function show(ContractType $contractType)
    {
        //
    }


    public function edit(ContractType $contractType)
    {
        return view('contractType.edit', compact('contractType'));
    }


    public function update(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            if(\Auth::user()->type == 'company' || \Auth::user()->type == 'branch')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();
                    return response()->json(['error' => $messages->first()], 422);
                }

                $contractType = ContractType::find($id);
                $contractType->name = $request->name;
                $contractType->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Contract Type',$contractType->id,'Update Contract Type',$contractType->name);
                
                // Pass the contract type to the view with the variable name 'contractType'
                $html = view('contractType.appendrow', ['contractType' => $contractType])->render();
                
                $data = [
                    'datarow' => $html,
                    'table_id' => "contracttype-table",
                    'action' => 'edit',
                    'row_id' => $contractType->id,
                ];
                return response()->json(['success' => true, 'message' => __('Contract Type successfully updated.'), 'data' => $data]);
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


    public function destroy(ContractType $contractType)
    {
        if(\Auth::user()->can('delete contract type'))
        {
            $data = Contract::where('type', $contractType->id)->first();
            if(!empty($data))
            {
                return redirect()->back()->with('error', __('this type is already use so please transfer or delete this type related data.'));
            }
            Utility::makeActivityLog(\Auth::user()->id,'Contract Type',$contractType->id,'Delete Contract Type',$contractType->name);
            $contractType->delete();

            return redirect()->route('contractType.index')->with('success', __('Contract Type successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

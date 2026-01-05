<?php

namespace App\Http\Controllers;

use App\Models\BillProduct;
use App\Models\InvoiceProduct;
use App\Models\ProposalProduct;
use App\Models\Tax;
use App\Models\Utility;
use Auth;
use Illuminate\Http\Request;

class TaxController extends Controller
{


    public function index()
    {
        if(\Auth::user()->can('manage constant tax'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $taxes = Tax::where($column, '=',$ownerId)->get();

            return view('taxes.index')->with('taxes', $taxes);
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create constant tax'))
        {
            return view('taxes.create');
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
        if(\Auth::user()->can('create constant tax'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                                   'rate' => 'required|numeric',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()
                    ->json(['error' => $messages->first()], 400);
            }

            $tax             = new Tax();
            $tax->name       = $request->name;
            $tax->rate       = $request->rate;
            $tax->created_by = \Auth::user()->creatorId();
            $tax->owned_by = \Auth::user()->ownedId();
            $tax->save();
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id,'Tax',$tax->id,'Create Tax',$tax->name);
            $html = view('taxes.appendrow', compact('tax'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "tax-table",
                'action' => 'add',
                'row_id' => $tax->id,
            ];
            return response()->json(['success' => true, 'message' => __('Tax rate successfully created.'), 'data' => $data]);
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

    public function show(Tax $tax)
    {
        return redirect()->route('taxes.index');
    }


    public function edit(Tax $tax)
    {
        if(\Auth::user()->can('edit constant tax'))
        {
            if($tax->created_by == \Auth::user()->creatorId())
            {
                return view('taxes.edit', compact('tax'));
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


    public function update(Request $request, Tax $tax)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit constant tax')) {
            if($tax->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required|max:20',
                                       'rate' => 'required|numeric',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return response()
                        ->json(['error' => $messages->first()], 400);
                }

                $tax->name = $request->name;
                $tax->rate = $request->rate;
                $tax->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Tax',$tax->id,'Update Tax',$tax->name);
                $html = view('taxes.appendrow', compact('tax'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "tax-table",
                    'action' => 'edit',
                    'row_id' => $tax->id,
                ];
                return response()->json(['success' => true, 'message' => __('Tax rate successfully updated.'), 'data' => $data]);
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

    public function destroy(Tax $tax)
    {
        if(\Auth::user()->can('delete constant tax'))
        {
            if($tax->created_by == \Auth::user()->creatorId())
            {
                $proposalData = ProposalProduct::whereRaw("find_in_set('$tax->id',tax)")->first();
                $billData     = BillProduct::whereRaw("find_in_set('$tax->id',tax)")->first();
                $invoiceData  = InvoiceProduct::whereRaw("find_in_set('$tax->id',tax)")->first();

                if(!empty($proposalData) || !empty($billData) || !empty($invoiceData))
                {
                    return redirect()->back()->with('error', __('this tax is already assign to proposal or bill or invoice so please move or remove this tax related data.'));
                }
                
                Utility::makeActivityLog(\Auth::user()->id,'Tax',$tax->id,'Delete Tax',$tax->name);
                $tax->delete();

                return redirect()->route('taxes.index')->with('success', __('Tax rate successfully deleted.'));
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

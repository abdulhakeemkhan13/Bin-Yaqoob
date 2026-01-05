<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\DebitNote;
use App\Models\Utility;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(\Auth::user()->can('manage debit note'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $bills = Bill::where($column, $ownerId)->get();

            return view('debitNote.index', compact('bills'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create($bill_id)
    {
        if(\Auth::user()->can('create debit note'))
        {

            $billDue = Bill::where('id', $bill_id)->first();

            return view('debitNote.create', compact('billDue', 'bill_id'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request, $bill_id)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('create debit note'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric',
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error' => $messages->first()], 422);
            }
            $billDue = Bill::where('id', $bill_id)->first();

            if($request->amount > $billDue->getDue())
            {
                return response()->json(['error' => 'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.'], 422);
            }
            $bill               = Bill::where('id', $bill_id)->first();
            $debit              = new DebitNote();
            $debit->bill        = $bill_id;
            $debit->vendor      = $bill->vender_id;
            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->save();

            Utility::updateUserBalance('vendor', $bill->vender_id, $request->amount, 'credit');

            Utility::makeActivityLog(\Auth::user()->id,'Debit Note',$debit->id,'Create Debit Note',$debit->description);
            \DB::commit();
            $bill = Bill::find($debit->bill);
            $html = view('debitNote.appendrow', ['debitNote' => $debit, 'bill' => $bill])->render();
            $data = [
                'datarow' => $html,
                'table_id' => "debit-table-body",
                'action' => 'add',
                'row_id' => $debit->id,
            ];
            return response()->json(['success' => true, 'message' => __('Debit Note successfully created.'), 'data' => $data]);
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


    public function edit($bill_id, $debitNote_id)
    {
        if(\Auth::user()->can('edit debit note'))
        {

            $debitNote = DebitNote::find($debitNote_id);

            return view('debitNote.edit', compact('debitNote'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    // public function update(Request $request, $bill_id, $debitNote_id)
    // {
    //     \DB::beginTransaction();
    //     try {
    //     if(\Auth::user()->can('edit debit note'))
    //     {

    //         $validator = \Validator::make(
    //             $request->all(), [
    //                                'amount' => 'required|numeric',
    //                                'date' => 'required',
    //                            ]
    //         );
    //         if($validator->fails())
    //         {
    //             $messages = $validator->getMessageBag();

    //             return response()->json(['error' => $messages->first()], 422);
    //         }
    //         $billDue = Bill::where('id', $bill_id)->first();
    //         if($request->amount > $billDue->getDue())
    //         {
    //             return response()->json(['error' => 'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.'], 422);
    //         }


    //         $debit = DebitNote::find($debitNote_id);
    //         Utility::updateUserBalance('vendor', $billDue->vender_id, $debit->amount, 'debit');



    //         $debit->date        = $request->date;
    //         $debit->amount      = $request->amount;
    //         $debit->description = $request->description;
    //         $debit->save();
    //         Utility::updateUserBalance('vendor', $billDue->vender_id, $request->amount, 'credit');
    //         Utility::makeActivityLog(\Auth::user()->id,'Debit Note',$debitNote->id,'Update Debit Note',$debit->description);
    //         \DB::commit();
    //         $bill = Bill::find($debit->bill);
    //         $html = view('debitNote.appendrow', ['debitNote' => $debit, 'bill' => $bill])->render();
    //         $data = [
    //             'datarow' => $html,
    //             'table_id' => "debit-table",
    //             'action' => 'edit',
    //             'row_id' => $debit->id,
    //         ];
    //         return response()->json(['success' => true, 'message' => __('Debit Note successfully updated.'), 'data' => $data]);;
    //     }
    //     else
    //     {
    //         return response()->json(['error' => __('Permission denied.')], 401);
    //     }
    //     } catch (\Exception $e) {
    //         \DB::rollback();
    //         return response()->json(['error' => $e->getMessage()], 401);
    //     }
    // }

    public function update(Request $request, $bill_id, $debitNote_id)
{
    \DB::beginTransaction();
    try {
        if (\Auth::user()->can('edit debit note')) {

            $validator = \Validator::make($request->all(), [
                'amount' => 'required|numeric',
                'date'   => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()], 422);
            }

            $billDue = Bill::where('id', $bill_id)->first();
            $debit   = DebitNote::find($debitNote_id);

            // ✅ allow up to: current due + existing debit amount
            $maxAllowed = $billDue->getDue() + $debit->amount;
            if ($request->amount > $maxAllowed) {
                return response()->json([
                    'error' => 'Maximum ' . \Auth::user()->priceFormat($maxAllowed) . ' credit limit of this bill.'
                ], 422);
            }

            // revert old amount from vendor balance before applying new amount
            Utility::updateUserBalance('vendor', $billDue->vender_id, $debit->amount, 'debit');

            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->save();

            // apply new amount
            Utility::updateUserBalance('vendor', $billDue->vender_id, $request->amount, 'credit');

            // 🔧 fixed variable name here
            Utility::makeActivityLog(\Auth::user()->id, 'Debit Note', $debit->id, 'Update Debit Note', $debit->description);

            \DB::commit();

            // rebuild row html and return for ajax replace
            $bill = Bill::find($debit->bill);
            $html = view('debitNote.appendrow', ['debitNote' => $debit, 'bill' => $bill])->render();

            return response()->json([
                'success' => true,
                'message' => __('Debit Note successfully updated.'),
                'data'    => [
                    'datarow'  => $html,
                    // ⬅️ IMPORTANT: send the TABLE id, not the tbody id
                    'table_id' => 'debit-table',
                    'action'   => 'edit',
                    'row_id'   => $debit->id,
                ],
            ]);
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    } catch (\Exception $e) {
        \DB::rollback();
        return response()->json(['error' => $e->getMessage()], 401);
    }
}



    public function destroy($bill_id, $debitNote_id)
    {
        if(\Auth::user()->can('delete debit note'))
        {
            $debitNote = DebitNote::find($debitNote_id);
            $debitNote->delete();
            //log 
            Utility::makeActivityLog(\Auth::user()->id,'Debit Note',$debitNote->id,'Delete Debit Note',$debitNote->description);
            Utility::updateUserBalance('vendor', $debitNote->vendor, $debitNote->amount, 'debit');
            return redirect()->back()->with('success', __('Debit Note successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customCreate()
    {
        if(\Auth::user()->can('create debit note'))
        {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $bills = Bill::where($column, $ownerId)->where('type','Bill')->get()->pluck('bill_id', 'id');
            return view('debitNote.custom_create', compact('bills'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customStore(Request $request)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('create debit note'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'bill' => 'required|numeric',
                                   'amount' => 'required|numeric',
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error' => $messages->first()], 422);
            }
            $bill_id = $request->bill;
            $billDue = Bill::where('id', $bill_id)->first();
            
            if($request->amount > $billDue->getDue())
            {
                return response()->json(['error' => 'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.'], 422);
            }
            $bill               = Bill::where('id', $bill_id)->first();
            $debit              = new DebitNote();
            $debit->bill        = $bill_id;
            $debit->vendor      = $bill->vender_id;
            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->save();
            Utility::updateUserBalance('vendor', $bill->vender_id, $request->amount, 'credit');
            Utility::makeActivityLog(\Auth::user()->id,'Debit Note',$debit->id,'Create Debit Note',$debit->description);
            \DB::commit();
            $bill = Bill::find($debit->bill);
            $html = view('debitNote.appendrow', ['debitNote' => $debit, 'bill' => $bill])->render();
            $data = [
                'datarow' => $html,
                'table_id' => "debit-table",
                'action' => 'add',
                'row_id' => $debit->id,
            ];
            return response()->json(['success' => true, 'message' => __('Debit Note successfully created.'), 'data' => $data]);

        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);;
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }

    public function getbill(Request $request)
    {

        $bill = Bill::where('id', $request->bill_id)->first();
        echo json_encode($bill->getDue());
    }
}

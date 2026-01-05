<?php

namespace App\Http\Controllers;

use App\Models\MinutesSheet;
use App\Models\CheckList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MinutesSheetCheckController extends Controller
{
    /**
     * Show form to create a new check for a minutes sheet
     */
    public function create($minutesSheetId)
    {
        $minutesSheet = MinutesSheet::with('bankAccount')->findOrFail($minutesSheetId);
        
        if ($minutesSheet->status !== 'approved') {
            return response()->json(['error' => __('Checks can only be created for approved minutes sheets.')], 400);
        }

        $remainingAmount = $minutesSheet->getRemainingAmount();
        
        if ($remainingAmount <= 0) {
            return response()->json(['error' => __('No remaining amount available for checks.')], 400);
        }

        $checkNumber = CheckList::generateCheckNumber($minutesSheetId);

        return view('minutesSheet.checks.create', compact('minutesSheet', 'remainingAmount', 'checkNumber'));
    }

    /**
     * Store a new check
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = \Validator::make($request->all(), [
                'minutes_sheet_id' => 'required|exists:minutes_sheets,id',
                'payee_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()], 400);
            }

            $minutesSheet = MinutesSheet::findOrFail($request->minutes_sheet_id);

            if ($minutesSheet->status !== 'approved') {
                return response()->json(['error' => __('Checks can only be created for approved minutes sheets.')], 400);
            }

            // Check if amount exceeds remaining balance
            $remainingAmount = $minutesSheet->getRemainingAmount();
            if ($request->amount > $remainingAmount) {
                return response()->json(['error' => __('Check amount exceeds remaining balance. Available: ') . number_format($remainingAmount, 2)], 400);
            }

            $check = new CheckList();
            $check->minutes_sheet_id = $request->minutes_sheet_id;
            $check->cheque_number = CheckList::generateCheckNumber($request->minutes_sheet_id);
            $check->payee_name = $request->payee_name;
            $check->amount = $request->amount;
            $check->date = $request->date;
            $check->notes = $request->notes;
            $check->account_id = $minutesSheet->bank_account_id;
            $check->type = 'cheque';
            $check->created_by = Auth::user()->id;
            $check->owned_by = Auth::user()->creatorId();
            $check->status = 'Pending';
            $check->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Check created successfully.'),
                'redirect' => route('minutes-sheet.show', $minutesSheet->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => __('An error occurred: ') . $e->getMessage()], 500);
        }
    }

    /**
     * Approve a check
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $check = CheckList::findOrFail($id);

            if (strtolower($check->status) !== 'pending') {
                return response()->json(['error' => __('Only pending checks can be approved.')], 400);
            }

            // Verify amount still doesn't exceed remaining balance
            $minutesSheet = $check->minutesSheet;
            if ($minutesSheet) {
                $otherChecksTotal = $minutesSheet->checks()
                    ->where('id', '!=', $check->id)
                    ->whereNotIn('status', ['Rejected', 'rejected'])
                    ->sum('amount');
                
                $remainingAfterOthers = $minutesSheet->amount - $otherChecksTotal;
                
                if ($check->amount > $remainingAfterOthers) {
                    return response()->json(['error' => __('Check amount exceeds remaining balance.')], 400);
                }
            }

            $check->status = 'Approved';
            $check->approved_by = Auth::user()->id;
            $check->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Check approved successfully.'),
                'redirect' => route('minutes-sheet.show', $check->minutes_sheet_id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => __('An error occurred: ') . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a check
     */
    public function destroy($id)
    {
        $check = CheckList::findOrFail($id);
        $minutesSheetId = $check->minutes_sheet_id;

        // Only pending checks can be deleted
        if (strtolower($check->status) === 'approved') {
            return redirect()->back()->with('error', __('Approved checks cannot be deleted.'));
        }

        $check->delete();

        return redirect()->route('minutes-sheet.show', $minutesSheetId)->with('success', __('Check deleted successfully.'));
    }
}

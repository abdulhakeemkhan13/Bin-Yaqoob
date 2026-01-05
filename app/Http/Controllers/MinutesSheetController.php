<?php

namespace App\Http\Controllers;

use App\Models\MinutesSheet;
use App\Models\MinutesSheetLog;
use App\Models\BankAccount;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MinutesSheetController extends Controller
{
    /**
     * Get the current user's designation name
     */
    protected function getUserDesignation()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee && $employee->designation_id) {
            $designation = Designation::find($employee->designation_id);
            return $designation ? $designation->name : null;
        }
        
        if ($user->type == 'company') {
            return 'Company';
        }
        
        return null;
    }

    /**
     * Display a listing of minutes sheets.
     */
    public function index()
    {
   
        $userDesignation = $this->getUserDesignation();
        if (!$userDesignation || !in_array($userDesignation, MinutesSheet::APPROVAL_CHAIN)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Show ALL minutes sheets to all users in the approval chain
        // This includes pending, approved, and rejected sheets
        $minutesSheets = MinutesSheet::with(['bankAccount', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        $canCreate = MinutesSheet::canCreate($userDesignation);

        return view('minutesSheet.index', compact('minutesSheets', 'canCreate', 'userDesignation'));
    }

    /**
     * Show the form for creating a new minutes sheet.
     */
    public function create()
    {
        $userDesignation = $this->getUserDesignation();
        
        if (!$userDesignation || !MinutesSheet::canCreate($userDesignation)) {
            return response()->json(['error' => __('Permission denied. Chairman cannot create minutes sheets.')], 401);
        }

        $bankAccounts = BankAccount::where('created_by', Auth::user()->creatorId())->get()->mapWithKeys(function ($row) {
            return [
                $row->id => $row->bank_name . ' - ' . $row->holder_name,
            ];
        });
        // ->pluck('holder_name', 'id');
        $referenceNo = MinutesSheet::generateReferenceNo();

        return view('minutesSheet.create', compact('bankAccounts', 'referenceNo', 'userDesignation'));
    }

    /**
     * Store a newly created minutes sheet.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $userDesignation = $this->getUserDesignation();
            
            if (!$userDesignation || !MinutesSheet::canCreate($userDesignation)) {
                return response()->json(['error' => __('Permission denied. Chairman cannot create minutes sheets.')], 401);
            }

            $validator = \Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'reference_no' => 'required|string|unique:minutes_sheets,reference_no',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'bank_account_id' => 'required|exists:bank_accounts,id',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()], 400);
            }

            // Determine the next stage in the approval chain
            $nextStage = MinutesSheet::getNextStage($userDesignation);
            
            if (!$nextStage && Auth::user()->type != 'company') {
                return response()->json(['error' => __('Cannot determine next approval stage.')], 400);
            }

            $minutesSheet = new MinutesSheet();
            $minutesSheet->amount = $request->amount;
            $minutesSheet->date = $request->date;
            $minutesSheet->reference_no = $request->reference_no;
            $minutesSheet->subject = $request->subject;
            $minutesSheet->description = $request->description;
            $minutesSheet->bank_account_id = $request->bank_account_id;
            $minutesSheet->created_by = Auth::user()->id;
            $minutesSheet->designation = $userDesignation;
            $minutesSheet->current_stage = $nextStage; // Auto-forward to next stage
            $minutesSheet->status = 'pending';
            $minutesSheet->save();

            // Create log entry
            MinutesSheetLog::create([
                'minutes_sheet_id' => $minutesSheet->id,
                'action_by' => Auth::user()->id,
                'action' => 'created',
                'remarks' => 'Minutes sheet created and forwarded to ' . $nextStage,
                'details' => $request->details ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => __('Minutes sheet created successfully and forwarded to ' . $nextStage . '.'),
                'redirect' => route('minutes-sheet.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => __('An error occurred: ') . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified minutes sheet.
     */
    public function show($id)
    {
        $minutesSheet = MinutesSheet::with(['bankAccount', 'creator', 'logs.actionByUser'])->findOrFail($id);
        $userDesignation = $this->getUserDesignation();

        if (!$userDesignation) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Check if user can view
        if ($minutesSheet->created_by !== Auth::user()->id && !$minutesSheet->canBeViewedBy($userDesignation)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $canApprove = $minutesSheet->isCurrentApprover($userDesignation);
        $canEdit = $minutesSheet->created_by === Auth::user()->id 
                   && $minutesSheet->status === 'pending'
                   && $minutesSheet->designation === $minutesSheet->current_stage;

        return view('minutesSheet.show', compact('minutesSheet', 'canApprove', 'canEdit', 'userDesignation'));
    }

    /**
     * Show the form for editing the minutes sheet.
     */
    public function edit($id)
    {
        $minutesSheet = MinutesSheet::findOrFail($id);
        $userDesignation = $this->getUserDesignation();

        // Only creator can edit, when not approved, and still at early stages
        $canEdit = $minutesSheet->created_by === Auth::user()->id 
            && $minutesSheet->status !== 'approved'
            && in_array($minutesSheet->current_stage, ['Company', 'Senior Executive Finance']);
        
        if (!$canEdit) {
            return response()->json(['error' => __('Permission denied. Cannot edit after approval process has progressed beyond Senior Executive Finance.')], 401);
        }

        $bankAccounts = BankAccount::where('created_by', Auth::user()->creatorId())->get()->pluck('holder_name', 'id');

        return view('minutesSheet.edit', compact('minutesSheet', 'bankAccounts', 'userDesignation'));
    }

    /**
     * Update the specified minutes sheet.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $minutesSheet = MinutesSheet::findOrFail($id);

            // Only creator can update, when not approved, and still at early stages
            $canEdit = $minutesSheet->created_by === Auth::user()->id 
                && $minutesSheet->status !== 'approved'
                && in_array($minutesSheet->current_stage, ['Company', 'Senior Executive Finance']);
            
            if (!$canEdit) {
                return response()->json(['error' => __('Permission denied. Cannot edit after approval process has progressed beyond Senior Executive Finance.')], 401);
            }

            $validator = \Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'bank_account_id' => 'required|exists:bank_accounts,id',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()], 400);
            }

            // Capture old values before update
            $oldValues = [
                'amount' => $minutesSheet->amount,
                'date' => $minutesSheet->date ? $minutesSheet->date->format('Y-m-d') : null,
                'subject' => $minutesSheet->subject,
                'description' => $minutesSheet->description,
                'bank_account_id' => $minutesSheet->bank_account_id,
            ];

            // Update minutes sheet
            $minutesSheet->amount = $request->amount;
            $minutesSheet->date = $request->date;
            $minutesSheet->subject = $request->subject;
            $minutesSheet->description = $request->description;
            $minutesSheet->bank_account_id = $request->bank_account_id;
            $minutesSheet->save();

            // Capture new values after update
            $newValues = [
                'amount' => $request->amount,
                'date' => $request->date,
                'subject' => $request->subject,
                'description' => $request->description,
                'bank_account_id' => $request->bank_account_id,
            ];

            // Build change details
            // $changes = [];
            // foreach ($oldValues as $key => $oldValue) {
            //     if ($oldValue != $newValues[$key]) {
            //         $changes[$key] = [
            //             'old' => $oldValue,
            //             'new' => $newValues[$key],
            //         ];
            //     }
            // }
            $changes = 'Minutes Sheet Updated';

            // Create edit log entry
            MinutesSheetLog::create([
                'minutes_sheet_id' => $minutesSheet->id,
                'action_by' => Auth::user()->id,
                'action' => 'edited',
                'remarks' => 'Minutes sheet updated by ' . Auth::user()->name,
                'details' => json_encode([
                    'stage' => $minutesSheet->current_stage,
                    'changes' => $changes,
                ]),
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => __('Minutes sheet updated successfully.'),
                'redirect' => route('minutes-sheet.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => __('An error occurred: ') . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified minutes sheet.
     */
    public function destroy($id)
    {
        $minutesSheet = MinutesSheet::findOrFail($id);

        // Only creator can delete, and only when pending at first stage
        if ($minutesSheet->created_by !== Auth::user()->id 
            || $minutesSheet->status !== 'pending'
            || $minutesSheet->designation !== $minutesSheet->current_stage) {
            return redirect()->back()->with('error', __('Permission denied. Cannot delete after approval process has started.'));
        }

        $minutesSheet->delete();

        return redirect()->route('minutes-sheet.index')->with('success', __('Minutes sheet deleted successfully.'));
    }

    /**
     * Show approval action modal.
     */
    public function action($id)
    {
        $minutesSheet = MinutesSheet::with(['bankAccount', 'creator'])->findOrFail($id);
        $userDesignation = $this->getUserDesignation();

        // For pending sheets, only current approver can take action
        // For rejected sheets, the rejector (current_stage) can resubmit or send back
        $canTakeAction = false;
        $isRejected = $minutesSheet->status === 'rejected';
        
        if ($isRejected) {
            // For rejected sheets, the designation at current_stage can resubmit or send back
            $canTakeAction = $minutesSheet->current_stage === $userDesignation;
        } else {
            $canTakeAction = $minutesSheet->isCurrentApprover($userDesignation);
        }

        if (!$canTakeAction) {
            return response()->json(['error' => __('Permission denied. You are not authorized to take action.')], 401);
        }

        $nextStage = MinutesSheet::getNextStage($minutesSheet->current_stage);
        $previousStage = MinutesSheet::getPreviousStage($minutesSheet->current_stage);
        $isFinalStage = $minutesSheet->isFinalStage();
        $creatorDesignation = $minutesSheet->designation;

        return view('minutesSheet.action', compact(
            'minutesSheet', 
            'nextStage', 
            'previousStage',
            'isFinalStage', 
            'userDesignation',
            'isRejected',
            'creatorDesignation'
        ));
    }

    /**
     * Process approval or rejection action.
     */
    public function processAction(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = \Validator::make($request->all(), [
                'minutes_sheet_id' => 'required|exists:minutes_sheets,id',
                'action' => 'required|in:approve,reject,resubmit,send_back',
                'remarks' => 'required|string',
                'details' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json(['error' => $messages->first()], 400);
            }

            $minutesSheet = MinutesSheet::findOrFail($request->minutes_sheet_id);
            $userDesignation = $this->getUserDesignation();

            // Check authorization
            $isRejected = $minutesSheet->status === 'rejected';
            $canTakeAction = false;
            
            if ($isRejected) {
                $canTakeAction = $minutesSheet->current_stage === $userDesignation;
            } else {
                $canTakeAction = $minutesSheet->isCurrentApprover($userDesignation);
            }

            if (!$canTakeAction) {
                return response()->json(['error' => __('Permission denied. You are not authorized to take action.')], 401);
            }

            if ($request->action === 'approve') {
                // Check if this is the final stage
                if ($minutesSheet->isFinalStage()) {
                    $minutesSheet->status = 'approved';
                    $minutesSheet->approved_at = now();
                    $message = __('Minutes sheet has been fully approved by Chairman.');
                } else {
                    // Move to next stage
                    $nextStage = MinutesSheet::getNextStage($minutesSheet->current_stage);
                    $minutesSheet->current_stage = $nextStage;
                    $message = __('Minutes sheet approved and forwarded to ' . $nextStage . '.');
                }

                // Create log entry
                MinutesSheetLog::create([
                    'minutes_sheet_id' => $minutesSheet->id,
                    'action_by' => Auth::user()->id,
                    'action' => 'approved',
                    'remarks' => $request->remarks,
                    'details' => $request->details,
                ]);

            } elseif ($request->action === 'reject') {
                // Reject - send back to previous designation in the chain (step-by-step)
                $previousStage = MinutesSheet::getPreviousStage($minutesSheet->current_stage);
                
                if ($previousStage) {
                    // There's a previous stage - send back to them
                    $minutesSheet->current_stage = $previousStage;
                    $minutesSheet->status = 'pending'; // Keep as pending, just moved back
                    
                    // Create log entry
                    MinutesSheetLog::create([
                        'minutes_sheet_id' => $minutesSheet->id,
                        'action_by' => Auth::user()->id,
                        'action' => 'rejected',
                        'remarks' => 'Rejected and sent back to ' . $previousStage . ': ' . $request->remarks,
                        'details' => $request->details,
                    ]);

                    $message = __('Minutes sheet rejected and sent back to ' . $previousStage . '.');
                } else {
                    // No previous stage (we're at the first level after creator) - mark as rejected
                    $minutesSheet->status = 'rejected';
                    $minutesSheet->rejected_at = now();
                    
                    // Create log entry
                    MinutesSheetLog::create([
                        'minutes_sheet_id' => $minutesSheet->id,
                        'action_by' => Auth::user()->id,
                        'action' => 'rejected',
                        'remarks' => $request->remarks,
                        'details' => $request->details,
                    ]);

                    $message = __('Minutes sheet has been rejected. No previous level to send back to.');
                }

            } elseif ($request->action === 'resubmit') {
                // Resubmit - move to next stage in chain (re-approve and forward)
                $nextStage = MinutesSheet::getNextStage($minutesSheet->current_stage);
                
                if ($nextStage) {
                    $minutesSheet->current_stage = $nextStage;
                    $minutesSheet->status = 'pending';
                    $minutesSheet->rejected_at = null;
                    $message = __('Minutes sheet resubmitted and forwarded to ' . $nextStage . '.');
                } else {
                    // If at Chairman level, mark as approved
                    $minutesSheet->status = 'approved';
                    $minutesSheet->approved_at = now();
                    $minutesSheet->rejected_at = null;
                    $message = __('Minutes sheet has been approved by Chairman.');
                }

                // Create log entry
                MinutesSheetLog::create([
                    'minutes_sheet_id' => $minutesSheet->id,
                    'action_by' => Auth::user()->id,
                    'action' => 'approved',
                    'remarks' => 'Resubmitted: ' . $request->remarks,
                    'details' => $request->details,
                ]);

            } elseif ($request->action === 'send_back') {
                // Send back to creator's designation (restart workflow)
                $creatorDesignation = $minutesSheet->designation;
                $nextStageAfterCreator = MinutesSheet::getNextStage($creatorDesignation);
                
                $minutesSheet->current_stage = $nextStageAfterCreator ?? $creatorDesignation;
                $minutesSheet->status = 'pending';
                $minutesSheet->rejected_at = null;

                // Create log entry
                MinutesSheetLog::create([
                    'minutes_sheet_id' => $minutesSheet->id,
                    'action_by' => Auth::user()->id,
                    'action' => 'approved',
                    'remarks' => 'Sent back to start (from ' . $userDesignation . '): ' . $request->remarks,
                    'details' => $request->details,
                ]);

                $message = __('Minutes sheet sent back. Workflow restarted from ' . $minutesSheet->current_stage . '.');
            }

            $minutesSheet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('minutes-sheet.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => __('An error occurred: ') . $e->getMessage()], 500);
        }
    }

    /**
     * Print minutes sheet as Minute Sheet format
     */
    public function print($id)
    {
        $minutesSheet = MinutesSheet::with(['bankAccount', 'creator', 'logs.actionByUser'])->findOrFail($id);
        $userDesignation = $this->getUserDesignation();

        // Check if user can view
        if ($minutesSheet->created_by !== Auth::user()->id && !$minutesSheet->canBeViewedBy($userDesignation)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Get settings
        $settings = Utility::settings();

        // Get all who have approved from logs (in order)
        $approvedStages = [];
        $approvalLogs = $minutesSheet->logs()
            ->where('action', 'approved')
            ->orderBy('created_at', 'asc')
            ->get();
        
        foreach ($approvalLogs as $log) {
            // Get the designation of the user who approved
            $approverDesignation = null;
            if ($log->actionByUser) {
                $employee = Employee::where('user_id', $log->actionByUser->id)->first();
                if ($employee && $employee->designation_id) {
                    $designation = Designation::find($employee->designation_id);
                    $approverDesignation = $designation ? $designation->name : null;
                }
                if (!$approverDesignation && $log->actionByUser->type == 'company') {
                    $approverDesignation = 'Company';
                }
            }
            
            if ($approverDesignation && !in_array($approverDesignation, $approvedStages)) {
                $approvedStages[] = $approverDesignation;
            }
        }
        
        // Include the creator's designation as the first "From"
        if (!in_array($minutesSheet->designation, $approvedStages)) {
            array_unshift($approvedStages, $minutesSheet->designation);
        }

        // To designation is the current stage (who is now approving)
        // If approved, show Chairman as final approver
        if ($minutesSheet->status === 'approved') {
            $toDesignation = 'Chairman (Approved)';
        } else {
            $toDesignation = $minutesSheet->current_stage;
        }

        // Convert amount to words
        $amountInWords = $this->convertAmountToWords($minutesSheet->amount);

        return view('minutesSheet.print', compact('minutesSheet', 'settings', 'toDesignation', 'amountInWords', 'approvedStages'));
    }

    /**
     * Convert amount to words (Pakistani format)
     */
    private function convertAmountToWords($amount)
    {
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen'
        ];
        
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];
        
        $amount = round($amount, 2);
        $parts = explode('.', (string)$amount);
        $whole = (int)$parts[0];
        $decimal = isset($parts[1]) ? (int)str_pad($parts[1], 2, '0') : 0;
        
        $result = $this->numberToWords($whole, $ones, $tens);
        
        if ($decimal > 0) {
            $result .= ' and ' . $this->numberToWords($decimal, $ones, $tens) . ' Paisa';
        }
        
        return $result . ' Only';
    }

    private function numberToWords($number, $ones, $tens)
    {
        if ($number == 0) return 'Zero';
        
        $words = '';
        
        // Crores
        if ($number >= 10000000) {
            $words .= $this->numberToWords((int)($number / 10000000), $ones, $tens) . ' Crore ';
            $number = $number % 10000000;
        }
        
        // Lakhs
        if ($number >= 100000) {
            $words .= $this->numberToWords((int)($number / 100000), $ones, $tens) . ' Lakh ';
            $number = $number % 100000;
        }
        
        // Thousands
        if ($number >= 1000) {
            $words .= $this->numberToWords((int)($number / 1000), $ones, $tens) . ' Thousand ';
            $number = $number % 1000;
        }
        
        // Hundreds
        if ($number >= 100) {
            $words .= $ones[(int)($number / 100)] . ' Hundred ';
            $number = $number % 100;
        }
        
        // Tens and Ones
        if ($number >= 20) {
            $words .= $tens[(int)($number / 10)] . ' ';
            $number = $number % 10;
        }
        
        if ($number > 0) {
            $words .= $ones[$number] . ' ';
        }
        
        return trim($words);
    }
}


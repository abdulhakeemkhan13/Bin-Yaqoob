<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BankAccount;
use App\Models\CheckList;
use App\Models\BillPayment;
use App\Models\MinutesSheet;
use App\Models\Vender;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\TransactionLines;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ChequeController extends Controller
{
    /**
     * Display a listing of cheques with filters
     */
    public function index(Request $request)
    {
        // Filters for the UI
        $account = BankAccount::where('created_by', Auth::user()->creatorId())
            ->pluck('holder_name', 'id');
        $account->prepend(__('Select Account'), '');

        // Date range across full months from the month pickers
        $startMonth = $request->get('start_month', date('Y-m', strtotime('-5 month')));
        $endMonth = $request->get('end_month', date('Y-m'));

        $start = Carbon::createFromFormat('Y-m', $startMonth)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $endMonth)->endOfMonth();

        // Base query with relations
        $q = CheckList::query()
            ->with([
                'payment.bankAccount',
                'billPayment.bankAccount',
                'bankAccount',
            ])
            ->where('created_by', Auth::user()->creatorId());
            // ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        // Account filter
        if ($request->filled('account')) {
            $q->where('account_id', $request->account);
        }

        // Cheque number filter
        if ($request->filled('cheque_no')) {
            $q->where('cheque_number', 'like', '%' . trim($request->cheque_no) . '%');
        }

        $rows = $q->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        // Filter summary
        $filter = [
            'account' => __('All'),
            'startDateRange' => $start->format('M-Y'),
            'endDateRange' => $end->format('M-Y'),
        ];
        
        if ($request->filled('account')) {
            $ba = BankAccount::find($request->account);
            $filter['account'] = $ba
                ? (($ba->holder_name === 'Cash') ? 'Cash' : ($ba->holder_name . ' - ' . $ba->bank_name))
                : __('All');
        }

        return view('cheque.index', compact('rows', 'account', 'filter'));
    }

    /**
     * Show the form for creating a new cheque
     */
    public function create(Request $request)
    {
        // if (!\Auth::user()->can('create payment bill')) {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }

        // Get bill_id from request (optional, for bill-specific payment)
        $bill_id = $request->get('bill_id');
        $bill = null;
        
        if ($bill_id) {
            $bill = Bill::where('id', $bill_id)
                ->where('created_by', Auth::user()->creatorId())
                ->first();
        }

        // Get minutes_sheet_id from request (optional, for minutes sheet checks)
        $minutes_sheet_id = $request->get('minutes_sheet_id');
        $minutesSheet = null;
        $remainingAmount = null;
        
        if ($minutes_sheet_id) {
            $minutesSheet = MinutesSheet::with('bankAccount')->find($minutes_sheet_id);
            if ($minutesSheet && $minutesSheet->status === 'approved') {
                $remainingAmount = $minutesSheet->getRemainingAmount();
            } else {
                return redirect()->back()->with('error', __('Minutes sheet not found or not approved.'));
            }
        }

        // Get vendors
        $venders = Vender::where('created_by', Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');

        // Get categories
        $categories = ProductServiceCategory::where('created_by', Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');

        // Get accounts
        $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
            ->where('created_by', Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');

        // Payment types
        $types = [
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'direct_deposit' => 'Direct Deposit',
        ];
        
        // Get COA accounts for dropdown
        $coaAccounts = \App\Models\ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->prepend(__('Select COA Account'), '');
        
        // Get all approved minutes sheets for dropdown
        $approvedMinutesSheets = MinutesSheet::with('bankAccount.chartAccount')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($ms) {
                return [
                    'id' => $ms->id,
                    'label' => $ms->reference_no . ' - ' . $ms->subject . ' (' . number_format($ms->getRemainingAmount(), 2) . ' remaining)',
                    'remaining_amount' => $ms->getRemainingAmount(),
                    'bank_account_id' => $ms->bank_account_id,
                    'bank_name' => $ms->bankAccount->bank_name ?? '-',
                    'holder_name' => $ms->bankAccount->holder_name ?? '-',
                    'bank_coa' => $ms->bankAccount->chart_account_id ?? null,
                    'total_amount' => $ms->amount,
                ];
            });

        return view('cheque.create', compact('venders', 'categories', 'accounts', 'bill', 'types', 'minutesSheet', 'remainingAmount', 'approvedMinutesSheets', 'coaAccounts'));
    }

    /**
     * Store a newly created cheque
     */
    public function store(Request $request)
    {
    //    dd($request->all());
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'account_id' => 'required|exists:bank_accounts,id',
        ]);

        if ($validator->fails()) {
            dd('fgdg');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Check if this is a minutes sheet check (no BillPayment needed)
            if ($request->filled('minutes_sheet_id')) {
                $minutesSheet = MinutesSheet::find($request->minutes_sheet_id);
                
                if (!$minutesSheet || $minutesSheet->status !== 'approved') {
                    return redirect()->back()->with('error', __('Minutes sheet not approved.'))->withInput();
                }
                
                // Check if amount exceeds remaining balance
                $remainingAmount = $minutesSheet->getRemainingAmount();
                if ($request->amount > $remainingAmount) {
                    return redirect()->back()->with('error', __('Check amount exceeds remaining balance. Available: ') . number_format($remainingAmount, 2))->withInput();
                }
                
                // Create checklist entry directly for minutes sheet
                $checklist = new CheckList();
                $checklist->minutes_sheet_id = $request->minutes_sheet_id;
                $checklist->cheque_number = CheckList::generateCheckNumber($request->minutes_sheet_id);
                $checklist->payee_name = $request->payee_name;
                $checklist->account_title = $request->title_of_account;
                $checklist->account_number = $request->account_number;
                $checklist->date = $request->date;
                $checklist->amount = $request->amount;
                $checklist->account_id = $minutesSheet->bank_account_id;
                $checklist->bank_coa = $minutesSheet->bankAccount->chart_account_id ?? null;
                $checklist->other_coa = $request->other_coa;
                $checklist->notes = $request->description;
                $checklist->type = 'cheque';
                $checklist->status = 'Pending';
                $checklist->created_by = Auth::user()->creatorId();
                $checklist->owned_by = Auth::user()->ownedId();
                $checklist->save();
                
                return redirect()->route('cheque.index')
                    ->with('success', __('Check created successfully.'));
            }
            
            // Otherwise, create payment record first (existing bill flow)
            $payment = new BillPayment();
            
            if ($request->filled('bill_id')) {
                $bill = Bill::find($request->bill_id);
                if ($bill) {
                    $payment->bill_id = $bill->id;
                    $payment->vender_id = $bill->vender_id;
                }
            }
            
            $payment->date = $request->date;
            $payment->amount = $request->amount;
            $payment->account_id = $request->account_id;
            $payment->reference = $request->reference;
            $payment->description = $request->description;
            $payment->created_by = Auth::user()->creatorId();

            // Handle file upload
            if ($request->hasFile('add_receipt')) {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                $request->add_receipt->storeAs('uploads/payment', $fileName);
                $payment->add_receipt = $fileName;
            }

            $payment->save();

            // Create checklist entry for cheque type
                $checklist = new CheckList();
                $checklist->bill_payment_id = $payment->id;
                $checklist->date = $request->date;
                $checklist->amount = $request->amount;
                $checklist->account_id = $request->account_id;
                $checklist->notes = $request->description;
                $checklist->created_by = Auth::user()->creatorId();
                $checklist->save();

            return redirect()->route('cheque.index')
                ->with('success', __('Cheque created successfully.'));

        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()
                ->with('error', __('Something went wrong: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified cheque
     */
    public function show($id)
    {
        $cheque = CheckList::with(['payment', 'billPayment', 'bankAccount'])
            ->where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        return view('cheque.view', compact('cheque'));
    }

    /**
     * Show the form for editing the specified cheque
     */
    public function edit($id )
    {
        
        $cheque = CheckList::with(['payment', 'billPayment', 'bankAccount', 'minutesSheet'])
            ->where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        // Get accounts
        $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
            ->where('created_by', Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');

        // Payment types
        $types = [
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'direct_deposit' => 'Direct Deposit',
        ];
        
        // Get COA accounts for dropdown
        $coaAccounts = \App\Models\ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->prepend(__('Select COA Account'), '');

        return view('cheque.edit', compact('cheque', 'accounts', 'types', 'coaAccounts'));
    }

    /**
     * Update the specified cheque
     */
    public function update(Request $request, $id)
    {
        try {
            $cheque = CheckList::where('created_by', Auth::user()->creatorId())
                ->findOrFail($id);
            
            // Block editing if not pending
            if ($cheque->status != 'Pending') {
                return redirect()->back()->with('error', __('Cannot edit a cheque that has already been approved or rejected.'));
            }

            // Update checklist
            $cheque->date = $request->date;
            $cheque->amount = $request->amount;
            $cheque->account_id = $request->account_id;
            $cheque->notes = $request->description;
            
            // Update minutes sheet specific fields
            if ($cheque->minutes_sheet_id) {
                $cheque->payee_name = $request->payee_name;
                $cheque->account_title = $request->title_of_account;
                $cheque->account_number = $request->account_number;
                $cheque->other_coa = $request->other_coa;
                
                // Get bank COA from bank account
                $bankAccount = BankAccount::find($request->account_id);
                if ($bankAccount) {
                    $cheque->bank_coa = $bankAccount->chart_account_id;
                }
            }
            
            $cheque->save();

            // Update related payment if exists
            $pay = $cheque->invoice_payment_id ? $cheque->payment : $cheque->billPayment;
            if ($pay) {
                $pay->date = $request->date;
                $pay->amount = $request->amount;
                $pay->account_id = $request->account_id;
                $pay->reference = $request->reference;
                $pay->description = $request->description;

                // Handle file upload
                if ($request->hasFile('add_receipt')) {
                    // Delete old file if exists
                    if ($pay->add_receipt && \Storage::exists('uploads/payment/' . $pay->add_receipt)) {
                        \Storage::delete('uploads/payment/' . $pay->add_receipt);
                    }
                    
                    $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                    $request->add_receipt->storeAs('uploads/payment', $fileName);
                    $pay->add_receipt = $fileName;
                }

                $pay->save();
            }

            return redirect()->route('cheque.index')
                ->with('success', __('Cheque updated successfully.'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Something went wrong: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified cheque
     */
    public function destroy($id)
    {
        try {
            $cheque = CheckList::where('created_by', Auth::user()->creatorId())
                ->findOrFail($id);

            // Delete related payment if exists
            $pay = $cheque->invoice_payment_id ? $cheque->payment : $cheque->billPayment;
            if ($pay) {
                // Delete file if exists
                if ($pay->add_receipt && \Storage::exists('uploads/payment/' . $pay->add_receipt)) {
                    \Storage::delete('uploads/payment/' . $pay->add_receipt);
                }
                $pay->delete();
            }

            $cheque->delete();

            return redirect()->route('cheque.index')
                ->with('success', __('Cheque deleted successfully.'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Something went wrong: ') . $e->getMessage());
        }
    }

    public function approve(Request $request)
    {
        $request->validate([
            'cheque_id' => 'required',
            'status' => 'required|in:Approved,Rejected',
        ]);

        $cheque = CheckList::with('minutesSheet.bankAccount')->findOrFail($request->cheque_id);

        // Check if already approved or rejected
        if ($cheque->status !== 'Pending') {
            return back()->with('error', 'This check has already been ' . strtolower($cheque->status) . '.');
        }

        \DB::beginTransaction();
        try {
            $cheque->status = $request->status;
            $cheque->approved_by = Auth::user()->id;
            
            if ($request->status === 'Approved') {
                $cheque->approved_at = date('Y-m-d');
                
                // Create Bank Payment Voucher (Journal Entry) on approval
                if ($cheque->minutes_sheet_id && !$cheque->journal_entry_id) {
                    $latest = JournalEntry::where('voucher_type', 'BPV')->orderBy('id', 'Desc')->first();
                    if (!$latest) {
                        $latest = 1;
                    } else {
                        $latest = $latest->journal_id + 1;
                    }

                    $journalEntry = new \App\Models\JournalEntry();
                    $journalEntry->date = $cheque->approved_at ?? date('Y-m-d');
                    $journalEntry->journal_id = $latest;
                    $journalEntry->reference_id = $cheque->id;
                    $journalEntry->category = 'Cheque';
                    $journalEntry->reference = 'BPV-' . $cheque->cheque_number;
                    $journalEntry->description = 'Bank Payment Voucher for Check: ' . $cheque->cheque_number . ' - Payee: ' . $cheque->payee_name;
                    $journalEntry->voucher_type = 'BPV'; // Bank Payment Voucher
                    $journalEntry->owned_by = Auth::user()->ownedId();
                    $journalEntry->created_by = Auth::user()->creatorId();
                    $journalEntry->save();
                    $journalEntry->created_at = $cheque->approved_at ? date('Y-m-d', strtotime($cheque->approved_at)) . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
                    $journalEntry->updated_at = $cheque->approved_at ? date('Y-m-d', strtotime($cheque->approved_at)) . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
                    $journalEntry->save();
                    
                    // Get the bank COA account
                    $bankCoa = $cheque->bank_coa ?? ($cheque->minutesSheet->bankAccount->chart_account_id ?? null);
                    
                    // Create Journal Items (Debit the COA account, Credit the Bank account)
                    // Debit entry - other_coa (expense/payable account)
                    if ($cheque->other_coa) {
                        $journalItem1 = \App\Models\JournalItem::create([
                            'journal' => $journalEntry->id,
                            'account' => $cheque->other_coa,
                            'description' => 'Check payment to ' . $cheque->payee_name,
                            'debit' => $cheque->amount,
                            'credit' => 0,
                        ]);
                        $journalItem1->created_at = $journalEntry->created_at;
                        $journalItem1->updated_at = $journalEntry->updated_at;
                        $journalItem1->save();
                        // add transaction line
                        $transactionLine = new TransactionLines();
                        $transactionLine->reference_id = $journalEntry->id;
                        $transactionLine->reference_sub_id = $journalItem1->id;
                        $transactionLine->date = $cheque->date;
                        $transactionLine->reference = 'Check Payment';
                        $transactionLine->account_id = $cheque->other_coa;
                        $transactionLine->debit = $cheque->amount;
                        $transactionLine->credit = 0;
                        $transactionLine->product_type = 'Cheque';
                        $transactionLine->product_id = $cheque->id;
                        $transactionLine->created_by = Auth::user()->creatorId();
                        $transactionLine->save();
                        $transactionLine->created_at = $journalEntry->created_at;
                        $transactionLine->updated_at = $journalEntry->updated_at;
                        $transactionLine->save();
                    }
                    
                    // Credit entry - bank_coa (bank account)
                    if ($bankCoa) {
                        $journalItem2 = \App\Models\JournalItem::create([
                            'journal' => $journalEntry->id,
                            'account' => $bankCoa,
                            'description' => 'Bank payment for check ' . $cheque->cheque_number,
                            'debit' => 0,
                            'credit' => $cheque->amount,
                        ]);
                        $journalItem2->created_at = $journalEntry->created_at;
                        $journalItem2->updated_at = $journalEntry->updated_at;
                        $journalItem2->save();
                        // add transaction line
                        $transactionLine2 = new TransactionLines();
                        $transactionLine2->reference_id = $journalEntry->id;
                        $transactionLine2->reference_sub_id = $journalItem2->id;
                        $transactionLine2->date = $cheque->date;
                        $transactionLine2->reference = 'Check Payment';
                        $transactionLine2->account_id = $bankCoa;
                        $transactionLine2->debit = 0;
                        $transactionLine2->credit = $cheque->amount;
                        $transactionLine2->product_type = 'Cheque';
                        $transactionLine2->product_id = $cheque->id;
                        $transactionLine2->created_by = Auth::user()->creatorId();
                        $transactionLine2->save();
                        $transactionLine2->created_at = $journalEntry->created_at;
                        $transactionLine2->updated_at = $journalEntry->updated_at;
                        $transactionLine2->save();
                    }
                    
                    // Validate that we have both debit and credit entries
                    if (!$cheque->other_coa || !$bankCoa) {
                        \DB::rollback();
                        return back()->with('error', 'Cannot create voucher: Missing COA account. Please ensure both Expense/Payable account and Bank account are set.');
                    }
                    
                    // Link journal entry to checklist
                    $cheque->journal_entry_id = $journalEntry->id;
                }
                
                $message = 'Check Approved and Bank Voucher Created Successfully';
            } else {
                // Rejected
                $cheque->forward_to = null;
                $message = 'Check Rejected Successfully';
            }

            $cheque->save();
            
            \DB::commit();

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            
            \DB::rollback();
            return back()->with('error', 'Failed to process check: ' . $e->getMessage());
        }
    }

    /**
     * Print cheque as Minute Sheet
     */
    public function print($id)
    {
        $cheque = CheckList::with([ 'bankAccount', 'minutesSheet'])
            ->where('created_by', Auth::user()->creatorId())->where('id', $id)
            ->first();

        // Get settings
        $settings = \App\Models\Utility::settings();

        // Determine From and To designations based on approval chain
        $fromDesignation = 'Senior Executive Finance';
        $toDesignation = 'Chairman';

        // Get subject from minutes sheet or generate from cheque
        $subject = '';
        if ($cheque->minutesSheet) {
            $subject = $cheque->minutesSheet->subject;
        } else {
            $subject = 'Payment for ' . ($cheque->payee_name ?? 'Vendor Payment');
        }

        // Convert amount to words
        $amountInWords = $this->convertAmountToWords($cheque->amount);

        return view('cheque.print', compact('cheque', 'settings', 'fromDesignation', 'toDesignation', 'subject', 'amountInWords'));
    }

    /**
     * Convert amount to words
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
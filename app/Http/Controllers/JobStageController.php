<?php

namespace App\Http\Controllers;

use App\Models\JobStage;
use App\Models\Utility;
use App\Imports\FullJournalImport;
use Illuminate\Http\Request;
use Excel;

class JobStageController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage job stage')) {
            // Only fetch stages owned by the authenticated user
            $user = \Auth::user();
            if ($user->type == 'company') {
                $stages = JobStage::where('created_by', '=', \Auth::user()->creatorId())->orderBy('order', 'asc')->get();
            } else {
                $stages = JobStage::where(function ($query) use ($user) {
                    $query->where('created_by', $user->creatorId())
                          ->orWhere('owned_by', $user->ownedId());
                })->get();
            }
            return view('jobStage.index', compact('stages'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create job stage')) {
            return view('jobStage.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            if (\Auth::user()->can('create job stage')) {

                $validator = \Validator::make(
                    $request->all(),
                    ['title' => 'required']
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $jobStage = new JobStage();
                $jobStage->title = $request->title;
                $jobStage->created_by = \Auth::user()->creatorId();
                $jobStage->owned_by = \Auth::user()->ownedId();
                $jobStage->save();

                \DB::commit();
                // Log activity
                Utility::makeActivityLog(\Auth::user()->id, 'Job Stage', $jobStage->id, 'Create Job Stage', $jobStage->title);

                // Pass the variable as 'stage' to match what the view expects
                // $stage = $jobStage; // Rename to match view expectation
                // $html = view('jobStage.appendrow', compact('stage'))->render();
                // $data = [
                //     'datarow' => $html,
                //     'table_id' => "jobstage-table",
                //     'action' => 'add',
                //     'row_id' => $jobStage->id,
                // ];
                // return response()->json(['success' => true, 'message' => __('Job stage successfully created.'), 'data' => $data]);
                // return to index with succes message
                return redirect()->route('job-stage.index')->with('success', __('Job stage successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }

    public function edit(JobStage $jobStage)
    {
        if (\Auth::user()->can('edit job stage')) {
            if ($jobStage->created_by == \Auth::user()->creatorId()) {
                return view('jobStage.edit', compact('jobStage'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, JobStage $jobStage)
    {
        \DB::beginTransaction();
        try {
            if (\Auth::user()->can('edit job stage')) {
                if ($jobStage->created_by == \Auth::user()->creatorId()) {

                    $validator = \Validator::make(
                        $request->all(),
                        ['title' => 'required']
                    );

                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();
                        return redirect()->back()->with('error', $messages->first());
                    }

                    $jobStage->title = $request->title;
                    $jobStage->save();
                    \DB::commit();

                    // Log activity
                    Utility::makeActivityLog(\Auth::user()->id, 'Job Stage', $jobStage->id, 'Update Job Stage', $jobStage->title);

                    // Pass the variable as 'stage' to match what the view expects
                    $stage = $jobStage; // Rename to match view expectation
                    $html = view('jobStage.appendrow', compact('stage'))->render();
                    $data = [
                        'datarow' => $html,
                        'table_id' => "jobstage-table",
                        'action' => 'edit',
                        'row_id' => $jobStage->id,
                    ];
                    return response()->json(['success' => true, 'message' => __('Job stage successfully updated.'), 'data' => $data]);
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

    public function destroy(JobStage $jobStage)
    {
        if (\Auth::user()->can('delete job stage')) {
            if ($jobStage->created_by == \Auth::user()->creatorId()) {
                // Log activity
                Utility::makeActivityLog(\Auth::user()->id, 'Job Stage', $jobStage->id, 'Delete Job Stage', $jobStage->title);

                $jobStage->delete();

                return redirect()->back()->with('success', __('Job stage successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function order(Request $request)
    {
        $post = $request->all();
        foreach ($post['order'] as $key => $item) {
            $stage = JobStage::where('id', '=', $item)->first();
            $stage->order = $key;
            $stage->save();
        }
    }

       public function importFile()
    {
        return view('chartOfAccount.import');
    }
    // public function import(Request $request)
    // {
    //     $rules = [
    //         'file' => 'required|mimes:csv,txt',
    //     ];

    //     $validator = \Validator::make($request->all(), $rules);

    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();

    //         return redirect()->back()->with('error', $messages->first());
    //     }

    //     Excel::import(new FullJournalImport, $request->file('file'));
    //     // Excel::import(new ChartOfAccountsImport, $request->file('file'));
    //     // if (session('failed_file')) {
    //     //     $filePath = storage_path(session('failed_file'));

    //     //     if (file_exists($filePath)) {
    //     //         return response()->download($filePath)->deleteFileAfterSend(true);
    //     //     }
    //     // }

    //     return back()->with('success', 'Chart of Accounts imported successfully!');
    // }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        Excel::import(new FullJournalImport, $request->file('file'));
        try {
            $allAccounts = collect();

            // Sort accounts numerically by ID
            $allAccounts = $request->file('file');

            // 🧭 Import each account
            foreach ($allAccounts as $account) {
                $localAccount = $this->ensureChartOfAccount(
                    $account['Name'] ?? '',
                    $account['Classification'] ?? '',
                    $account['AccountSubType'] ?? 'Other',
                    $account
                );

                if (!$localAccount) {
                    continue; // Skip unmapped or invalid accounts
                }

                // Handle parent relationship
                $parentId = 0;
                if (isset($account['ParentRef']['value'])) {
                    $parentQBCode = $account['ParentRef']['value'];
                    $parentAccount = ChartOfAccount::where('code', $parentQBCode)
                        ->where('created_by', auth()->user()->creatorId())
                        ->first();
                    // dd($parentAccount->id);
                    if ($parentAccount) {
                        $parentRecord = ChartOfAccountParent::firstOrCreate(
                            [
                                'name' => $parentAccount->name,
                                'created_by' => auth()->user()->creatorId(),
                                'sub_type' => $parentAccount->sub_type ?? null,
                                'type' => $parentAccount->type ?? null,
                                'account' => $parentAccount->id,
                            ]
                        );

                        $parentId = $parentRecord->id;
                    }
                }

                // Update QuickBooks-specific info
                $localAccount->code = $account['Id'] ?? '';
                $localAccount->parent = $parentId;
                $localAccount->description = $account['AccountType'] ?? null;
                $localAccount->is_enabled = 1;
                $localAccount->save();

                $importedCount++;
            }

            return response()->json([
                'status' => 'success',
                'count' => $allAccounts->count(),
                'imported' => $importedCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function ensureChartOfAccount($fullName, $distributionAccountType, $detailType = 'Other', $qbAccountData = null)
    {
        // 🔹 Map QuickBooks account types to your system's main account categories
        $typeMapping = [
            // Liabilities
            'accounts payable (a/p)' => 'Liabilities',
            'accounts payable' => 'Liabilities',
            'credit card' => 'Liabilities',
            'long term liabilities' => 'Liabilities',
            'other current liabilities' => 'Liabilities',
            'loan payable' => 'Liabilities',
            'notes payable' => 'Liabilities',
            'board of equalization payable' => 'Liabilities',
            'Other Current Liability' => 'Liabilities',
            'Liability' => 'Liabilities',
            'liability' => 'Liabilities',

            // Assets
            'accounts receivable (a/r)' => 'Assets',
            'accounts receivable' => 'Assets',
            'bank' => 'Assets',
            'checking' => 'Assets',
            'savings' => 'Assets',
            'undeposited funds' => 'Assets',
            'inventory asset' => 'Assets',
            'other current assets' => 'Assets',
            'fixed assets' => 'Assets',
            'truck' => 'Assets',
            'Asset' => 'Assets',
            'asset' => 'Assets',
            'Other Current Asset' => 'Assets',

            // Equity
            'equity' => 'Equity',
            'opening balance equity' => 'Equity',
            'retained earnings' => 'Equity',
            'equity' => 'Equity',
            'Equity' => 'Equity',

            // Income
            'income' => 'Income',
            'other income' => 'Income',
            'sales of product income' => 'Income',
            'service/fee income' => 'Income',
            'sales' => 'Income',
            'revenue' => 'Income',
            'Revenue' => 'Income',

            // COGS
            'cost of goods sold' => 'Costs of Goods Sold',
            'cogs' => 'Costs of Goods Sold',

            // Expenses
            'expenses' => 'Expenses',
            'expense' => 'Expenses',
            'Expense' => 'Expenses',
            'other expense' => 'Expenses',
            'marketing' => 'Expenses',
            'insurance' => 'Expenses',
            'utilities' => 'Expenses',
            'rent or lease' => 'Expenses',
            'meals and entertainment' => 'Expenses',
            'bank charges' => 'Expenses',
            'depreciation' => 'Expenses',
        ];

        $typeName = strtolower(trim($distributionAccountType));
        $creatorId = \Auth::user()->creatorId();

        if (!isset($typeMapping[$typeName])) {
            \Log::warning("Unmapped QuickBooks type: '{$distributionAccountType}' for account '{$fullName}'");
            dd($qbAccountData);
            return null; // Skip unmapped
        }

        // 🏷️ Create/find ChartOfAccountType
        $systemTypeName = $typeMapping[$typeName];
        $type = ChartOfAccountType::firstOrCreate(
            ['name' => $systemTypeName, 'created_by' => $creatorId]
        );
        $matchTypes = [
            'bank',
            'banks',
            'cost of goods sold',
            'cost of goods solds'
        ];
        $accType = strtolower(trim($qbAccountData['AccountType'] ?? ''));
        // ✅ Compare safely (case-insensitive + plural-friendly)
        if (in_array($accType, $matchTypes)) {
            $detailType = ucwords(strtolower($qbAccountData['AccountType']));
        }
        // 🧩 Create/find SubType
        $subType = ChartOfAccountSubType::firstOrCreate(
            [
                'type' => $type->id,
                'name' => $detailType ?: 'Other',
                'created_by' => $creatorId,
            ]
        );

        // 🧾 Create/find ChartOfAccount
        $account = ChartOfAccount::firstOrCreate(
            [
                'name' => $fullName,
                'code' => $qbAccountData['Id'] ?? '',
                'qb_balance' => $qbAccountData['CurrentBalance'] ?? 0,
                'description' => $qbAccountData['AccountType'] ?? null,
                'type' => $type->id,
                'sub_type' => $subType->id,
                'created_by' => $creatorId,
            ]
        );
        if (
    strtolower($subType->name) == 'banks' ||
    strtolower($subType->name) == 'bank' ||
    strtolower($subType->name) == 'credit card' ||
    strtolower($subType->name) == 'credit cards' ||
    strtolower($subType->name) == 'creditcard' ||
    strtolower($subType->name) == 'creditcards'
) {
    // detect if it's a credit card type
    $isCreditCard = in_array(strtolower($subType->name), [
        'credit card', 'credit cards', 'creditcard', 'creditcards'
    ]);

        $newBankAccount = BankAccount::create([
            'bank_name'       => $account->name,
            'holder_name'     => \Auth::user()->name ?? $account->name,
            'account_number'  => $account->code,
            'opening_balance' => 0,
            'contact_number'  => '0000000000',
            'bank_address'    => 'System Imported',
            'chart_account_id'=> $account->id,
            'created_by'      => $creatorId,
            'owned_by'        => \Auth::user()->ownedId(),

            // set sub_type dynamically
            'account_subtype'        => $isCreditCard ? 'credit_card' : 'current_account'
        ]);
    }

        return $account;
    }

}

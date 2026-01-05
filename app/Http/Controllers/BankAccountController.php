<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BillPayment;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\CustomField;
use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Utility;
use App\Models\Transaction;
use App\Models\TransactionLines;
use App\Models\WorkFlow;
use App\Models\Notification;
use App\Models\WorkFlowAction;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('create bank account'))
        {
            $accounts = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->with(['chartAccount'])->get();

            return view('bankAccount.index', compact('accounts'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create bank account'))
        {
            $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            ->where('parent', '=', 0)
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $chartAccounts->prepend('Select Account', 0);

            $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.name , chart_of_account_parents.account'));
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();


            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'account')->get();

            return view('bankAccount.create', compact('customFields','chartAccounts' , 'subAccounts'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
        if(\Auth::user()->can('create bank account'))
        {

            $rules = [
                'holder_name' => 'required',
                'bank_name' => 'required',
                'account_number' => 'required',
            ];

            if ($request->contact_number != null) {
                $rules['contact_number'] = ['regex:/^([0-9\s\-\+\(\)]*)$/'];
            }

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('bank-account.index')->with('error', $messages->first());
            }

            $account                  = new BankAccount();
            $account->chart_account_id = $request->chart_account_id;
            $account->holder_name     = $request->holder_name;
            $account->bank_name       = $request->bank_name;
            $account->account_number  = $request->account_number;
            $account->opening_balance = $request->opening_balance ? $request->opening_balance : 0;
            $account->contact_number  = $request->contact_number ? $request->contact_number : '-';
            $account->bank_address    = $request->bank_address ? $request->bank_address : '-';
            $account->created_by      = \Auth::user()->creatorId();
            $account->save();
            CustomField::saveData($account, $request->customField);

             // // WorkFlow get which is active
            //  $us_mail = 'false';
            //  $us_notify = 'false';
            //  $us_approve = 'false';
            //  $usr_Notification = [];
            //  $workflow = WorkFlow::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'accounts')->where('status', 1)->first();
            //  if ($workflow) {
            //      $workflowaction = WorkFlowAction::where('workflow_id', $workflow->id)->where('status', 1)->get();
            //      foreach ($workflowaction as $action) {
            //          $useraction = json_decode($action->assigned_users);
            //          if (strtolower('bank-create') == $action->node_id) {
            //             // Pick that stage user assign or change on lead
            //             if (@$useraction != '') {
            //                 $useraction = json_decode($useraction);
            //                 foreach ($useraction as $anyaction) {
            //                     // make new user array
            //                     if ($anyaction->type == 'user') {
            //                         $usr_Notification[] = $anyaction->id;
            //                     }
            //                 }
            //             }
            //             $raw_json = trim($action->applied_conditions, '"');
            //             $cleaned_json = stripslashes($raw_json);
            //             $applied_conditions = json_decode($cleaned_json, true);

            //             if (isset($applied_conditions['conditions']) && is_array($applied_conditions['conditions'])) {
            //                 $arr = [
            //                     'holder_name' => 'holder_name',
            //                     'bank_name' => 'bank_name',
            //                     'account_number' => 'account_number',
            //                 ];
            //                 $relate = [
            //                 ];
            //                 foreach ($applied_conditions['conditions'] as $conditionGroup) {
            //                     if (in_array($conditionGroup['action'], ['send_email', 'send_notification', 'send_approval'])) {
            //                         $query = BankAccount::where('id', $account->id);
            //                         foreach ($conditionGroup['conditions'] as $condition) {
            //                             $field = $condition['field'];
            //                             $operator = $condition['operator'];
            //                             $value = $condition['value'];
            //                                 // Apply condition directly to the contract model
            //                             $query->where($arr[$field], $operator, $value);
            //                         }
            //                         $result = $query->first();
            //                         if (!empty($result)) {
            //                             if ($conditionGroup['action'] === 'send_email') {
            //                                 $us_mail = 'true';
            //                             } elseif ($conditionGroup['action'] === 'send_notification') {
            //                                 $us_notify = 'true';
            //                             } elseif ($conditionGroup['action'] === 'send_approval') {
            //                                 $us_approve = 'true';
            //                             }
            //                         }
            //                     }
            //                 }
            //              }

            //              if ($us_mail == 'true') {
            //                  // email send
            //              }

            //             if ($us_notify == 'true' || $us_approve == 'true') {
            //                 // notification generate
            //                 if (count($usr_Notification) > 0) {
            //                     $usr_Notification[] = \Auth::user()->creatorId();
            //                     foreach ($usr_Notification as $usrLead) {
            //                         $data = [
            //                             "updated_by" => \Auth::user()->id,
            //                             "data_id" => $account->id,
            //                             "name" => @$account->bank_name,
            //                         ];
            //                         if($us_notify == 'true'){
            //                             Utility::makeNotification($usrLead,'create_bank',$data,$account->id,'create Bank');
            //                         }elseif($us_approve == 'true'){
            //                             Utility::makeNotification($usrLead,'approve_bank',$data,$account->id,'For Approval Bank');
            //                         }
            //                     }
            //                 }
            //             }
            //         }
            //      }
            //  }
            $data = [
                'account_id' => $account->chart_account_id,
                'transaction_type' => 'Credit',
                'transaction_amount' => $account->opening_balance,
                'reference' => 'Bank Account',
                'reference_id' => $account->id,
                'reference_sub_id' => 0,
                'date' => date('Y-m-d'),
            ];
            // Utility::addTransactionLines($data , 'create');
            \DB::commit();
            // return redirect()->route('bank-account.index')->with('success', __('Account successfully created.'));
            $html = view('bankAccount.appendrow', ['account' => $account])->render();
            $data = [
                'datarow' => $html,
                'table_id' => "bank-account-table",
                'action' => 'add',
                'row_id' => $account->id,
            ];
            return response()->json(['success' => true, 'message' => __('Account successfully created.'), 'data' => $data]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    public function show()
    {
        return redirect()->route('bank-account.index');
    }


    public function edit(BankAccount $bankAccount)
    {
        if(\Auth::user()->can('edit bank account'))
        {
            if($bankAccount->created_by == \Auth::user()->creatorId())
            {
                $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                ->where('parent', '=', 0)
                    ->where('created_by', \Auth::user()->creatorId())->get()
                    ->pluck('code_name', 'id');
                $chartAccounts->prepend('Select Account', 0);

                $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.name , chart_of_account_parents.account'));
                $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
                $subAccounts->where('chart_of_accounts.parent', '!=', 0);
                $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                $subAccounts = $subAccounts->get()->toArray();

                $bankAccount->customField = CustomField::getData($bankAccount, 'account');
                $customFields             = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'account')->get();

                return view('bankAccount.edit', compact('bankAccount', 'customFields','chartAccounts','subAccounts'));
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


    public function update(Request $request, BankAccount $bankAccount)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('create bank account'))
        {

            $rules = [
                'holder_name' => 'required',
                'bank_name' => 'required',
                'account_number' => 'required',
            ];

            if ($request->contact_number != null) {
                $rules['contact_number'] = ['regex:/^([0-9\s\-\+\(\)]*)$/'];
            }

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('bank-account.index')->with('error', $messages->first());
            }

            $bankAccount->chart_account_id = $request->chart_account_id;
            $bankAccount->holder_name     = $request->holder_name;
            $bankAccount->bank_name       = $request->bank_name;
            $bankAccount->account_number  = $request->account_number;
            $bankAccount->opening_balance = $request->opening_balance ? $request->opening_balance : 0;
            $bankAccount->contact_number  = $request->contact_number ? $request->contact_number : '-';
            $bankAccount->bank_address    = $request->bank_address ? $request->bank_address : '-';
            $bankAccount->created_by      = \Auth::user()->creatorId();
            $bankAccount->save();
            CustomField::saveData($bankAccount, $request->customField);

            $data = [
                'account_id' => $bankAccount->chart_account_id,
                'transaction_type' => 'Credit',
                'transaction_amount' => $bankAccount->opening_balance,
                'reference' => 'Bank Account',
                'reference_id' => $bankAccount->id,
                'reference_sub_id' => 0,
                'date' => date('Y-m-d'),
            ];
            // Utility::addTransactionLines($data , 'edit');

            \DB::commit();
            $html = view('bankAccount.appendrow', ['account' => $bankAccount])->render();

            $data = [
                'datarow' => $html,
                'table_id' => "bank-account-table",
                'action' => 'edit',
                'row_id' => $bankAccount->id,
            ];
            return response()->json(['success' => true, 'message' => __('Account successfully updated.'), 'data' => $data]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }


    public function destroy(BankAccount $bankAccount)
    {
        if(\Auth::user()->can('delete bank account'))
        {
            if($bankAccount->created_by == \Auth::user()->creatorId())
            {
                $revenue        = Revenue::where('account_id', $bankAccount->id)->first();
                $invoicePayment = InvoicePayment::where('account_id', $bankAccount->id)->first();
                $transaction    = Transaction::where('account', $bankAccount->id)->first();
                $payment        = Payment::where('account_id', $bankAccount->id)->first();
                $billPayment    = BillPayment::first();

            TransactionLines::where('reference_id', $bankAccount->id)->where('reference', 'Bank Account')->delete();

                if(!empty($revenue) && !empty($invoicePayment) && !empty($transaction) && !empty($payment) && !empty($billPayment))
                {
                    return redirect()->route('bank-account.index')->with('error', __('Please delete related record of this account.'));
                }
                else
                {
                    $bankAccount->delete();

                    return redirect()->route('bank-account.index')->with('success', __('Account successfully deleted.'));
                }

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

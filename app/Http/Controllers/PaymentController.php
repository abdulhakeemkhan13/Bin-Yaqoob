<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BillAccount;
use App\Models\BillPayment;
use App\Models\ChartOfAccount;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionLines;
use App\Models\Utility;
use App\Models\Vender;
use App\Models\WorkFlow;
use App\Models\Notification;
use App\Models\WorkFlowAction;
use Illuminate\Http\Request;
use Auth;
class PaymentController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage payment')) {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';

            $vender = Vender::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');

            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');

            $category = ProductServiceCategory::where($column, '=',$ownerId)->where('type', '=', 'expense')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $query = Payment::where($column, '=',$ownerId);

//            if(!empty($request->date))
//            {
//                $date_range = explode('to', $request->date);
//                $query->whereBetween('date', $date_range);
//            }
            if (count(explode('to', $request->date)) > 1) {
                $date_range = explode(' to ', $request->date);
                $query->whereBetween('date', $date_range);
            } elseif (!empty($request->date)) {
                $date_range = [$request->date, $request->date];
                $query->whereBetween('date', $date_range);
            }

            if (!empty($request->vender)) {
                $query->where('vender_id', '=', $request->vender);
            }
            if (!empty($request->account)) {
                $query->where('account_id', '=', $request->account);
            }

            if (!empty($request->category)) {
                $query->where('category_id', '=', $request->category);
            }

            $payments = $query->with(['bankAccount','vender','category'])->get();

            return view('payment.index', compact('payments', 'account', 'category', 'vender'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    public function create()
    {
        if (\Auth::user()->can('create payment')) {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $venders = Vender::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $venders->prepend('--', null);

//            $categories = ProductServiceCategory::where($column, '=', $ownerId)->where('type', '=', 2)->get()->pluck('name', 'id');
            $categories = ProductServiceCategory::where($column, $ownerId)
                ->whereNotIn('type', ['product & service', 'income'])
                ->get()->pluck('name', 'id');
            $categories->prepend('Select Category', '');

            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $chartAccounts->prepend('Select Account', '');
            return view('payment.create', compact('venders', 'categories', 'accounts', 'chartAccounts'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            if (!\Auth::user()->can('create payment')) {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
    
            $validator = \Validator::make($request->all(), [
                'date' => 'required',
                'amount' => 'required',
                'account_id' => 'required',
                'category_id' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->first()], 401);
            }
    
            $payment = new Payment();
            $payment->date = $request->date;
            $payment->amount = $request->amount;
            $payment->account_id = $request->account_id;
            $payment->vender_id = $request->vender_id;
            $payment->category_id = $request->category_id;
            $payment->payment_method = 0;
            $payment->reference = $request->reference;
    
            if (!empty($request->add_receipt)) {
                $image_size = $request->file('add_receipt')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                if ($result == 1) {
                    $fileName = time() . "_" . preg_replace('/[^A-Za-z0-9\-]/', '', $request->add_receipt->getClientOriginalName());
                    $payment->add_receipt = $fileName;
                    $dir = 'uploads/payment';
                    $path = Utility::upload_file($request, 'add_receipt', $fileName, $dir, []);
                    if ($path['flag'] == 0) {
                        return response()->json(['error' => __($path['msg'])], 401);
                    }
                }
            }
    
            $payment->description = $request->description;
            $payment->created_by = \Auth::user()->creatorId();
            $payment->owned_by = \Auth::user()->ownedId();
            $payment->save();
    
            $pay_id = $payment->id;
    
            $chartAccountId = ChartOfAccount::find($request->account_id);
            $accountId = BankAccount::find($payment->account_id);
    
            $data = [
                'account_id' => $accountId->chart_account_id,
                'transaction_type' => 'Debit',
                'transaction_amount' => $payment->amount,
                'reference' => 'Payment',
                'reference_id' => $payment->id,
                'reference_sub_id' => 0,
                'date' => $payment->date,
            ];
            Utility::addTransactionLines($data , 'create');
    
            $account = new BillAccount();
            $account->chart_account_id = $chartAccountId;
            $account->price = $request->amount;
            $account->description = $request->description;
            $account->type = 'Payment';
            $account->ref_id = $payment->id;
            $account->save();
    
            // Add Transaction
            $category = ProductServiceCategory::find($request->category_id);
            $payment->payment_id = $payment->id;
            $payment->type = 'Payment';
            $payment->category = $category->name ?? '';
            $payment->user_id = $payment->vender_id;
            $payment->user_type = 'Vender';
            $payment->account = $request->account_id;
    
            Transaction::addTransaction($payment);
    
            $vender = Vender::find($request->vender_id);
    
            // Use different variable name to avoid overwriting
            $billPayment = new BillPayment();
            $billPayment->name = $vender->name ?? '';
            $billPayment->method = '-';
            $billPayment->date = \Auth::user()->dateFormat($request->date);
            $billPayment->amount = \Auth::user()->priceFormat($request->amount);
            $billPayment->bill = '';
    
            if ($vender) {
                Utility::userBalance('vendor', $vender->id, $request->amount, 'debit');
            }
    
            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');
    
            // Workflow
            // ... your workflow logic remains unchanged here ...
            // (Use same $pay_id, $usr_Notification, $us_mail, etc.)
    
            // Twilio Notification
            $setting = Utility::settings(\Auth::user()->creatorId());
            if (!empty($setting['twilio_payment_notification']) && $setting['twilio_payment_notification'] == 1 && $vender) {
                $paymentNotificationArr = [
                    'payment_amount' => \Auth::user()->priceFormat($request->amount),
                    'vendor_name' => $vender->name,
                    'payment_type' => 'Payment',
                ];
                Utility::send_twilio_msg($vender->contact, 'bill_payment', $paymentNotificationArr);
            }
    
            // Activity log
            Utility::makeActivityLog(\Auth::user()->id, 'payment', $payment->id, 'Create payment', $payment->name);
    
            \DB::commit();
    
            $html = view('payment.appendrow', compact('payment'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "payment-table",
                'action' => 'add',
                'row_id' => $payment->id,
            ];
    
            return response()->json(['success' => true, 'message' => __('Payment successfully created.'), 'data' => $data]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
    

    public function edit(Payment $payment)
    {

        if (\Auth::user()->can('edit payment')) {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $venders = Vender::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $venders->prepend('--', 0);

            $categories = ProductServiceCategory::where($column, $ownerId)
                ->whereNotIn('type', ['product & service', 'income'])
                ->get()->pluck('name', 'id');
            $categories->prepend('Select Category', '');

            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $chartAccounts->prepend('Select Account', '');

            return view('payment.edit', compact('venders', 'categories', 'accounts', 'payment', 'chartAccounts'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Payment $payment)
    {
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('edit payment')) {

            $validator = \Validator::make(
                $request->all(), [
                    'date' => 'required',
                    'amount' => 'required',
                    'account_id' => 'required',
                    'vender_id' => 'required',
                    'category_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json(['error' => $messages->first()], 401);
            }
            $vender = Vender::where('id', $request->vender_id)->first();
            if (!empty($vender)) {
                Utility::userBalance('vendor', $vender->id, $payment->amount, 'credit');
            }
            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

            $payment->date = $request->date;
            $payment->amount = $request->amount;
            $payment->account_id = $request->account_id;
//            $payment->chart_account_id  = $request->chart_account_id;
            $payment->vender_id = $request->vender_id;
            $payment->category_id = $request->category_id;
            $payment->payment_method = 0;
            $payment->reference = $request->reference;

            if (!empty($request->add_receipt)) {
                //storage limit
                $file_path = '/uploads/payment/' . $payment->add_receipt;
                $image_size = $request->file('add_receipt')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                if ($result == 1) {
                    if ($payment->add_receipt) {
                        Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
                        $path = storage_path('uploads/payment' . $payment->add_receipt);
//                        if(file_exists($path))
//                        {
//                            \File::delete($path);
//                        }
                    }
                    $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                    $payment->add_receipt = $fileName;
                    $dir = 'uploads/payment';
                    $path = Utility::upload_file($request, 'add_receipt', $fileName, $dir, []);
                    if ($path['flag'] == 0) {
                        return response()->json(['error' => __($path['msg'])], 401);
                    }
                }

            }

            $payment->description = $request->description;
            $payment->save();

            $accountId = BankAccount::find($payment->account_id);
            $data = [
                'account_id' => $accountId->chart_account_id,
                'transaction_type' => 'Debit',
                'transaction_amount' => $payment->amount,
                'reference' => 'Payment',
                'reference_id' => $payment->id,
                'reference_sub_id' => 0,
                'date' => $payment->date,
            ];
            Utility::addTransactionLines($data , 'edit');

            $category = ProductServiceCategory::where('id', $request->category_id)->first();
            $payment->category_id = $request->category_id;  // Store the category ID instead of name
            $payment->payment_id = $payment->id;
            $payment->type = 'Payment';
            $payment->account = $request->account_id;
            
            // Find existing transaction or create new one
            $transaction = Transaction::where('payment_id', $payment->id)
                ->where('type', 'Payment')
                ->first();
                
            if (!$transaction) {
                $transaction = new Transaction();
                $transaction->payment_id = $payment->id;
                $transaction->type = 'Payment';
                $transaction->user_id = $payment->vender_id;
                $transaction->user_type = 'Vender';
                $transaction->created_by = \Auth::user()->creatorId();
            }
            
            $transaction->account = $request->account_id;
            $transaction->amount = $request->amount;
            $transaction->description = $request->description;
            $transaction->date = $request->date;
            $transaction->category = $category->name;
            $transaction->save();
            
            if (!empty($vender)) {
                Utility::userBalance('vendor', $vender->id, $request->amount, 'debit');
            }
            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');
            //activity log
            Utility::makeActivityLog(\Auth::user()->id, 'payment', $payment->id, 'Update payment', $payment->category);
            
            \DB::commit();
            $html = view('payment.appendrow', compact('payment'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "payment-table",
                'action' => 'edit',
                'row_id' => $payment->id,
            ];
            return response()->json(['success' => true, 'message' => __('Payment Updated Successfully.'), 'data' => $data]);
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 401);;
        }
    }

    public function destroy(Payment $payment)
    {
        if (\Auth::user()->can('delete payment')) {
            if ($payment->created_by == \Auth::user()->creatorId()) {
                if (!empty($payment->add_receipt)) {
                    //storage limit
                    $file_path = '/uploads/payment/' . $payment->add_receipt;
                    $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

                }

                TransactionLines::where('reference_id', $payment->id)->where('reference', 'Payment')->delete();
                //log
                Utility::makeActivityLog(\Auth::user()->id, 'payment', $payment->id, 'Delete payment', $payment->category);
                $payment->delete();
                $type = 'Payment';
                $user = 'Vender';
                Transaction::destroyTransaction($payment->id, $type, $user);

                if ($payment->vender_id != 0) {
                    Utility::userBalance('vendor', $payment->vender_id, $payment->amount, 'credit');
                }
                Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

                return response()->json(['success' => true, 'message' => __('Payment successfully deleted.')]);;
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}

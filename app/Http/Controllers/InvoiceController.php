<?php

namespace App\Http\Controllers;

use App\Exports\InvoiceExport;
use App\Models\BankAccount;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\InvoiceBankTransfer;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Notification;
use App\Models\WorkFlow;
use App\Models\WorkFlowAction;
use App\Models\CheckList;
use App\Models\Plan;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\StockReport;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utility;
use App\Models\TransactionLines;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use Auth;

class InvoiceController extends Controller
{
    public function __construct() {}

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage invoice')) {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $customer = Customer::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $customer->prepend('Select Customer', '');
            $status = Invoice::$statues;
            $query = Invoice::where($column, '=', $ownerId);

            if (!empty($request->customer)) {
                $query->where('customer_id', '=', $request->customer);
            }
            if (count(explode('to', $request->issue_date)) > 1) {
                $date_range = explode(' to ', $request->issue_date);
                $query->whereBetween('issue_date', $date_range);
            } elseif (!empty($request->issue_date)) {
                $date_range = [$request->issue_date, $request->issue_date];
                $query->whereBetween('issue_date', $date_range);
            }
            if ($request->status != null) {
                $query->where('status', '=', $request->status);
            }
            $invoices = $query->get();

            return view('invoice.index', compact('invoices', 'customer', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create($customerId)
    {
        if (\Auth::user()->can('create invoice')) {
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
            $invoice_number = \Auth::user()->invoiceNumberFormat($this->invoiceNumber());
            $customers = Customer::where($column, $ownerId)->get()->pluck('name', 'id');
            $customers->prepend('Select Customer', '');
            $category = ProductServiceCategory::where($column, $ownerId)->where('type', 'income')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $product_services = ProductService::where($column, $ownerId)->get()->mapWithKeys(function($product) {
                return [$product->id => ($product->sku ? $product->sku . ' - ' : '') . $product->name];
            });
            $product_services->prepend('--', '');

            // Check for installment pre-fill
            $installment = null;
            $contract = null;
            $prefillCustomer = null;
            $installmentId = request()->query('installment_id');
            $contractId = request()->query('contract_id');

            if ($installmentId) {
                $installment = \App\Models\ContractInstallment::with(['contract.customer', 'unit'])->find($installmentId);
                if ($installment) {
                    $contract = $installment->contract;
                    $customerId = $contract->customer_id ?? $customerId;
                    $prefillCustomer = $contract->customer;
                }
            } elseif ($contractId) {
                $contract = \App\Models\Contract::with('customer')->find($contractId);
                if ($contract) {
                    $customerId = $contract->customer_id ?? $customerId;
                    $prefillCustomer = $contract->customer;
                }
            }

            return view('invoice.create', compact('customers', 'invoice_number', 'product_services', 'category', 'customFields', 'customerId', 'installment', 'contract', 'prefillCustomer'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function customer(Request $request)
    {
        $customer = Customer::where('id', '=', $request->id)->first();
        return view('invoice.customer_detail', compact('customer'));
    }

    public function getCustomerContracts(Request $request)
    {
        $customerId = $request->customer_id;
        
        // Find contracts linked to this customer
        $contracts = \App\Models\Contract::where('customer_id', $customerId)
            ->where('created_by', \Auth::user()->creatorId())
            ->with(['installment_plan', 'unit', 'installments'])
            ->get()
            ->map(function($contract) {
                $installments = [];
                $firstPendingInstallment = null;
                
                if ($contract->payment_plan_type == 'Installment') {
                    if ($contract->installment_plan) {
                        $installments = [
                            'down_payment' => $contract->installment_plan->down_payment_amount,
                            'installment_amount' => $contract->installment_plan->installment_amount,
                            'installment_count' => $contract->installment_plan->installment_count,
                        ];
                    }
                    
                    // Get first pending installment
                    $pending = $contract->installments
                        ->where('status', 'pending')
                        ->sortBy('installment_number')
                        ->first();
                    
                    if ($pending) {
                        $firstPendingInstallment = [
                            'id' => $pending->id,
                            'number' => $pending->installment_number,
                            'type' => $pending->installment_type,
                            'amount' => $pending->amount,
                            'issue_date' => $pending->issue_date->format('Y-m-d'),
                            'due_date' => $pending->due_date->format('Y-m-d'),
                            'description' => $pending->description ?? ($pending->installment_type == 'down_payment' ? 'Down Payment' : 'Installment Payment'),
                        ];
                    }
                }
                
                // Get the product linked to the unit
                $productId = null;
                if ($contract->unit && $contract->unit->product_id) {
                    $productId = $contract->unit->product_id;
                }
                
                return [
                    'id' => $contract->id,
                    'subject' => $contract->subject,
                    'value' => $contract->sale_price ?? $contract->value,
                    'payment_plan_type' => $contract->payment_plan_type ?? 'Installment',
                    'installments' => $installments,
                    'product_id' => $productId,
                    'first_pending_installment' => $firstPendingInstallment,
                ];
            });
        
        return response()->json($contracts);
    }

    public function product(Request $request)
    {
        $data['product'] = $product = ProductService::find($request->product_id);
        $data['unit'] = (!empty($product->unit)) ? $product->unit->name : '';
        $data['taxRate'] = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes'] = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice = $product->sale_price;
        $quantity = 1;
        $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);

        return json_encode($data);
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'customer_id' => 'required',
                    'issue_date' => 'required',
                    'due_date' => 'required',
                    // 'category_id' => 'required',
                    'items' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
                        // DATE VALIDATION: Due Date cannot be earlier than Issue Date
            if ($request->issue_date > $request->due_date) {
                return redirect()->back()
                    ->with('error', 'Due Date cannot be earlier than Issue Date.')
                    ->withInput();
            }
            $status = Invoice::$statues;
            $invoice = new Invoice();
            $invoice->invoice_id = $this->invoiceNumber();
            $invoice->customer_id = $request->customer_id;
            $invoice->status = 0;
            $invoice->issue_date = $request->issue_date;
            $invoice->due_date = $request->due_date;
            $invoice->category_id = $request->category_id ?? 1;
            $invoice->ref_number = $request->ref_number;
            //            $invoice->discount_apply = isset($request->discount_apply) ? 1 : 0;
            $invoice->created_by = \Auth::user()->creatorId();
            $invoice->owned_by = \Auth::user()->ownedId();
            
            // Set contract-related fields if installment_id is provided
            if ($request->has('installment_id') && $request->installment_id) {
                $installment = \App\Models\ContractInstallment::with('contract')->find($request->installment_id);
                if ($installment) {
                    
                    $invoice->installment_id = $installment->id;
                    $invoice->contract_id = $installment->contract_id;
                    
                    // Get project/tower/floor/unit from contract
                    if ($installment->contract) {
                        $invoice->re_project_id = $installment->contract->re_project_id;
                        $invoice->tower_id = $installment->contract->tower_id;
                        $invoice->floor_id = $installment->contract->floor_id;
                        $invoice->unit_id = $installment->contract->unit_id;
                    }
                }
            } elseif ($request->has('contract_id') && $request->contract_id) {
                $contract = \App\Models\Contract::find($request->contract_id);
                if ($contract) {
                
                    $invoice->contract_id = $contract->id;
                    $invoice->customer_id = $contract->customer_id ?? $request->customer_id;
                    $invoice->re_project_id = $contract->re_project_id;
                    $invoice->tower_id = $contract->tower_id;
                    $invoice->floor_id = $contract->floor_id;
                    $invoice->unit_id = $contract->unit_id;
                }
            }
            
            $invoice->save();
            
            // Link invoice to installment if provided (update installment's invoice_id and status)
            if ($request->has('installment_id') && $request->installment_id) {
                $installment = \App\Models\ContractInstallment::find($request->installment_id);
                if ($installment) {
                    $installment->invoice_id = $invoice->id;
                    $installment->status = 'generated';
                    $installment->save();
                }
            }
            
            CustomField::saveData($invoice, $request->customField);
            $products = $request->items;

            for ($i = 0; $i < count($products); $i++) {

                $invoiceProduct = new InvoiceProduct();
                $invoiceProduct->invoice_id = $invoice->id;
                $invoiceProduct->product_id = $products[$i]['item'];
                $invoiceProduct->quantity = $products[$i]['quantity'];
                $invoiceProduct->tax = $products[$i]['tax'];
                //                $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                $invoiceProduct->discount = $products[$i]['discount'];
                $invoiceProduct->price = $products[$i]['price'];
                $invoiceProduct->description = $products[$i]['description'];
                $invoiceProduct->save();

                //inventory management (Quantity)
                Utility::total_quantity('minus', $invoiceProduct->quantity, $invoiceProduct->product_id);

                //For Notification
                $setting = Utility::settings(\Auth::user()->creatorId());
                $customer = Customer::find($request->customer_id);
                $invoiceNotificationArr = [
                    'invoice_number' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                    'user_name' => \Auth::user()->name,
                    'invoice_issue_date' => $invoice->issue_date,
                    'invoice_due_date' => $invoice->due_date,
                    'customer_name' => $customer->name,
                ];
                //Slack Notification
                if (isset($setting['invoice_notification']) && $setting['invoice_notification'] == 1) {
                    Utility::send_slack_msg('new_invoice', $invoiceNotificationArr);
                }
                //Telegram Notification
                if (isset($setting['telegram_invoice_notification']) && $setting['telegram_invoice_notification'] == 1) {
                    Utility::send_telegram_msg('new_invoice', $invoiceNotificationArr);
                }
                //Twilio Notification
                if (isset($setting['twilio_invoice_notification']) && $setting['twilio_invoice_notification'] == 1) {
                    Utility::send_twilio_msg($customer->contact, 'new_invoice', $invoiceNotificationArr);
                }
            }
            $us_mail = 'false';
            $us_notify = 'false';
            $us_approve = 'false';
            $usr_Notification = [];
            // $workflow = WorkFlow::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'crm')->where('status', 1)->first();
            // if ($workflow) {
            //     $workflowaction = WorkFlowAction::where('workflow_id', $workflow->id)->where('status', 1)->where('level_id', 4)->get();
            //     foreach ($workflowaction as $action) {
            //         $useraction = json_decode($action->assigned_users);
            //         if ('create-invoice' == $action->node_id) {
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
            //                     'category' => 'category_name',
            //                     'customer' => 'customer_name',
            //                     'referance number' => 'ref_number',
            //                 ];
            //                 $relate = [
            //                     'category_name' => 'category',
            //                     'customer_name' => 'customer',
            //                 ];

            //                 foreach ($applied_conditions['conditions'] as $conditionGroup) {

            //                     if (in_array($conditionGroup['action'], ['send_email', 'send_notification', 'send_approval'])) {
            //                         $query = Invoice::where('id', $invoice->id);
            //                         foreach ($conditionGroup['conditions'] as $condition) {
            //                             $field = $condition['field'];
            //                             $operator = $condition['operator'];
            //                             $value = $condition['value'];
            //                             if (isset($arr[$field], $relate[$arr[$field]])) {
            //                                 $relatedField = strpos($arr[$field], '_') !== false ? explode('_', $arr[$field], 2)[1] : $arr[$field];
            //                                 $relation = $relate[$arr[$field]];

            //                                 // Apply condition to the related model
            //                                 $query->whereHas($relation, function ($relatedQuery) use ($relatedField, $operator, $value) {
            //                                     $relatedQuery->where($relatedField, $operator, $value);
            //                                 });
            //                             } else {
            //                                 // Apply condition directly to the contract model
            //                                 $query->where($arr[$field], $operator, $value);
            //                             }
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
            //             }
            //             if ($us_mail == 'true') {
            //                 // email send
            //             }
            //             if ($us_notify == 'true' || $us_approve == 'true') {
            //                 // notification generate
            //                 if (count($usr_Notification) > 0) {
            //                     $usr_Notification[] = Auth::user()->creatorId();
            //                     foreach ($usr_Notification as $usrLead) {
            //                         $data = [
            //                             "updated_by" => Auth::user()->id,
            //                             "data_id" => $invoice->id,
            //                             "name" => '',
            //                         ];
            //                         if ($us_notify == 'true') {
            //                             Utility::makeNotification($usrLead, 'create_invoice', $data, $invoice->id, 'create Invoice');
            //                         } elseif ($us_approve == 'true') {
            //                             Utility::makeNotification($usrLead, 'approve_invoice', $data, $invoice->id, 'For Approval Invoice');
            //                         }
            //                     }
            //                 }
            //             }
            //         }
            //     }
            // }
            //Product Stock Report
            $type = 'invoice';
            $type_id = $invoice->id;
            StockReport::where('type', '=', 'invoice')->where('type_id', '=', $invoice->id)->delete();
            $description = $invoiceProduct->quantity . '  ' . __(' quantity sold in invoice') . ' ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            Utility::addProductStock($invoiceProduct->product_id, $invoiceProduct->quantity, $type, $description, $type_id);

            //webhook
            $module = 'New Invoice';
            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($invoice);
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    // Log
                    Utility::makeActivityLog(\Auth::user()->id, 'Invoice', $invoiceProduct->id, 'Create Invoice', $invoiceProduct->description);
                    return redirect()->route('invoice.index', $invoice->id)->with('success', __('Invoice successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }
            Utility::makeActivityLog(\Auth::user()->id, 'Invoice', $invoiceProduct->id, 'Create Invoice', $invoiceProduct->description);
            return redirect()->route('invoice.index', $invoice->id)->with('success', __('Invoice successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($ids)
    {
        if (\Auth::user()->can('edit invoice')) {
            $id = Crypt::decrypt($ids);
            $invoice = Invoice::find($id);
            $invoice_number = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $customers = Customer::where($column, $ownerId)->get()->pluck('name', 'id');
            $category = ProductServiceCategory::where($column, $ownerId)->where('type', 'income')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $product_services = ProductService::where($column, $ownerId)->get()->mapWithKeys(function($product) {
                return [$product->id => ($product->sku ? $product->sku . ' - ' : '') . $product->name];
            });
            $invoice->customField = CustomField::getData($invoice, 'invoice');
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();

            return view('invoice.edit', compact('customers', 'product_services', 'invoice', 'invoice_number', 'category', 'customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {

        if (\Auth::user()->can('edit invoice')) {
            if ($invoice->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'customer_id' => 'required',
                        'issue_date' => 'required',
                        'due_date' => 'required',
                        'category_id' => 'required',
                        'items' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('invoice.index')->with('error', $messages->first());
                }
                $invoice->customer_id = $request->customer_id;
                $invoice->issue_date = $request->issue_date;
                $invoice->due_date = $request->due_date;
                $invoice->ref_number = $request->ref_number;
                //                $invoice->discount_apply = isset($request->discount_apply) ? 1 : 0;
                $invoice->category_id = $request->category_id;
                
                // Update contract-related fields if installment_id is provided
                if ($request->has('installment_id') && $request->installment_id) {
                    $installment = \App\Models\ContractInstallment::with('contract')->find($request->installment_id);
                    if ($installment) {
                        $invoice->installment_id = $installment->id;
                        $invoice->contract_id = $installment->contract_id;
                        
                        // Get project/tower/floor/unit from contract
                        if ($installment->contract) {
                            $invoice->re_project_id = $installment->contract->re_project_id;
                            $invoice->tower_id = $installment->contract->tower_id;
                            $invoice->floor_id = $installment->contract->floor_id;
                            $invoice->unit_id = $installment->contract->unit_id;
                        }
                        
                        // Update installment's invoice_id and status
                        $installment->invoice_id = $invoice->id;
                        $installment->status = 'generated';
                        $installment->save();
                    }
                } elseif ($request->has('contract_id') && $request->contract_id) {
                    $contract = \App\Models\Contract::find($request->contract_id);
                    if ($contract) {
                        $invoice->contract_id = $contract->id;
                        $invoice->re_project_id = $contract->re_project_id;
                        $invoice->tower_id = $contract->tower_id;
                        $invoice->floor_id = $contract->floor_id;
                        $invoice->unit_id = $contract->unit_id;
                    }
                }
                
                $invoice->save();

                Utility::starting_number($invoice->invoice_id + 1, 'invoice');
                CustomField::saveData($invoice, $request->customField);
                $products = $request->items;

                for ($i = 0; $i < count($products); $i++) {
                    $invoiceProduct = InvoiceProduct::find($products[$i]['id']);

                    if ($invoiceProduct == null) {
                        $invoiceProduct = new InvoiceProduct();
                        $invoiceProduct->invoice_id = $invoice->id;

                        Utility::total_quantity('minus', $products[$i]['quantity'], $products[$i]['item']);

                        $updatePrice = ($products[$i]['price'] * $products[$i]['quantity']) + ($products[$i]['itemTaxPrice']) - ($products[$i]['discount']);
                        Utility::updateUserBalance('customer', $request->customer_id, $updatePrice, 'credit');
                    } else {
                        Utility::total_quantity('plus', $invoiceProduct->quantity, $invoiceProduct->product_id);
                    }

                    if (isset($products[$i]['item'])) {
                        $invoiceProduct->product_id = $products[$i]['item'];
                    }

                    $invoiceProduct->quantity = $products[$i]['quantity'];
                    $invoiceProduct->tax = $products[$i]['tax'];
                    $invoiceProduct->discount = $products[$i]['discount'];
                    $invoiceProduct->price = $products[$i]['price'];
                    $invoiceProduct->description = $products[$i]['description'];
                    $invoiceProduct->save();

                    if ($products[$i]['id'] > 0) {
                        Utility::total_quantity('minus', $products[$i]['quantity'], $invoiceProduct->product_id);
                    }

                    //Product Stock Report
                    $type = 'invoice';
                    $type_id = $invoice->id;
                    StockReport::where('type', '=', 'invoice')->where('type_id', '=', $invoice->id)->delete();
                    $description = $products[$i]['quantity'] . '  ' . __(' quantity sold in invoice') . ' ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
                    if (empty($products[$i]['id'])) {
                        Utility::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);
                    }
                }

                // TransactionLines::where('reference_id', $invoice->id)->where('reference', 'Invoice')->delete();

                $invoice_products = InvoiceProduct::where('invoice_id', $invoice->id)->get();
                foreach ($invoice_products as $invoice_product) {
                    $product = ProductService::find($invoice_product->product_id);
                    $totalTaxPrice = 0;
                    if ($invoice_product->tax != null) {
                        $taxes = \App\Models\Utility::tax($invoice_product->tax);
                        foreach ($taxes as $tax) {
                            $taxPrice = \App\Models\Utility::taxRate($tax->rate, $invoice_product->price, $invoice_product->quantity, $invoice_product->discount);
                            $totalTaxPrice += $taxPrice;
                        }
                    }

                    $itemAmount = ($invoice_product->price * $invoice_product->quantity) - ($invoice_product->discount) + $totalTaxPrice;

                    $data = [
                        'account_id' => $product->sale_chartaccount_id,
                        'transaction_type' => 'Credit',
                        'transaction_amount' => $itemAmount,
                        'reference' => 'Invoice',
                        'reference_id' => $invoice->id,
                        'reference_sub_id' => $product->id,
                        'date' => $invoice->issue_date,
                    ];
                    // Utility::addTransactionLines($data, 'edit');
                }

                // Update voucher if invoice has already been sent (has a voucher_id)
                if (!empty($invoice->voucher_id)) {
                    // Delete old journal entries and items
                    $oldJournal = \App\Models\JournalEntry::find($invoice->voucher_id);
                    if ($oldJournal) {
                        // Delete journal items
                        \App\Models\JournalItem::where('journal', $oldJournal->id)->delete();
                        // Delete related transaction lines
                        TransactionLines::where('reference', 'Invoice Journal')
                            ->where('reference_id', $oldJournal->id)
                            ->delete();
                        // Delete the journal entry
                        $oldJournal->delete();
                    }
                    // Recreate the voucher
                    $this->createInvoiceJournalVoucher($invoice);
                }

                //log
                Utility::makeActivityLog(\Auth::user()->id, 'Invoice', $invoice->id, 'Update Invoice', $invoice->description);
                return redirect()->route('invoice.index')->with('success', __('Invoice successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function invoiceNumber()
    {
        $user = \Auth::user();
        $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
        $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
        $latest = Invoice::where($column, '=', $ownerId)->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function show($ids)
    {

        if (\Auth::user()->can('show invoice')) {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Invoice Not Found.'));
            }
            $id = Crypt::decrypt($ids);
            $invoice = Invoice::with(['creditNote', 'payments.bankAccount', 'items.product.unit'])->find($id);

            if (!empty($invoice->created_by) == \Auth::user()->creatorId()) {
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();

                $customer = $invoice->customer;
                $iteams = $invoice->items;
                $user = \Auth::user();

                // start for storage limit note
                $invoice_user = User::find($invoice->created_by);
                $user_plan = Plan::getPlan($invoice_user->plan);
                // end for storage limit note

                $invoice->customField = CustomField::getData($invoice, 'invoice');
                $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();

                $creditnote = CreditNote::where('invoice', $invoice->id)->first();

                return view('invoice.view', compact('invoice', 'customer', 'iteams', 'invoicePayment', 'customFields', 'user', 'invoice_user', 'user_plan', 'creditnote'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Invoice $invoice, Request $request)
    {
        if (\Auth::user()->can('delete invoice')) {
            if ($invoice->created_by == \Auth::user()->creatorId()) {
                foreach ($invoice->payments as $payment) {
                    Utility::bankAccountBalance($payment->account_id, $payment->amount, 'debit');

                    // Delete payment voucher if exists
                    if (!empty($payment->voucher_id)) {
                        $journal = \App\Models\JournalEntry::find($payment->voucher_id);
                        if ($journal) {
                            \App\Models\JournalItem::where('journal', $journal->id)->delete();
                            TransactionLines::where('reference', 'Invoice Payment')
                                ->where('reference_id', $journal->id)
                                ->delete();
                            $journal->delete();
                        }
                    }

                    $invoicepayment = InvoicePayment::find($payment->id);
                    if ($invoicepayment) {
                        $invoicepayment->delete();
                    }
                }

                if ($invoice->customer_id != 0 && $invoice->status != 0) {
                    Utility::updateUserBalance('customer', $invoice->customer_id, $invoice->getDue(), 'debit');
                }

                // TransactionLines::where('reference_id', $invoice->id)->where('reference', 'Invoice')->delete();
                // TransactionLines::where('reference_id', $invoice->id)->Where('reference', 'Invoice Payment')->delete();

                CreditNote::where('invoice', '=', $invoice->id)->delete();

                InvoiceProduct::where('invoice_id', '=', $invoice->id)->delete();

                // Delete invoice voucher if exists
                if (!empty($invoice->voucher_id)) {
                    $journal = \App\Models\JournalEntry::find($invoice->voucher_id);
                    if ($journal) {
                        \App\Models\JournalItem::where('journal', $journal->id)->delete();
                        TransactionLines::where('reference', 'Invoice Journal')
                            ->where('reference_id', $journal->id)
                            ->delete();
                        $journal->delete();
                    }
                }

                // /log
                Utility::makeActivityLog(\Auth::user()->id, 'Invoice', $invoice->id, 'Delete Invoice', $invoice->description);
                $invoice->delete();
                return redirect()->route('invoice.index')->with('success', __('Invoice successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function productDestroy(Request $request)
    {

        if (\Auth::user()->can('delete invoice product')) {
            $invoiceProduct = InvoiceProduct::find($request->id);

            if ($invoiceProduct) {
                $invoice = Invoice::find($invoiceProduct->invoice_id);
                $productService = ProductService::find($invoiceProduct->product_id);

                Utility::updateUserBalance('customer', $invoice->customer_id, $request->amount, 'debit');

                // TransactionLines::where('reference_sub_id', $productService->id)->where('reference', 'Invoice')->delete();

                InvoiceProduct::where('id', '=', $request->id)->delete();

                // Update voucher if invoice has been sent (has a voucher_id)
                if (!empty($invoice->voucher_id)) {
                    $oldJournal = \App\Models\JournalEntry::find($invoice->voucher_id);
                    if ($oldJournal) {
                        \App\Models\JournalItem::where('journal', $oldJournal->id)->delete();
                        TransactionLines::where('reference', 'Invoice Journal')
                            ->where('reference_id', $oldJournal->id)
                            ->delete();
                        $oldJournal->delete();
                    }
                    $this->createInvoiceJournalVoucher($invoice);
                }
            }

            // /log
            Utility::makeActivityLog(\Auth::user()->id, 'Invoice Product', $invoiceProduct->id, 'Delete Invoice Product', $invoiceProduct->product->name);
            
            // Return JSON for AJAX requests
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => __('Invoice product successfully deleted.')]);
            }
            return redirect()->back()->with('success', __('Invoice product successfully deleted.'));
        } else {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customerInvoice(Request $request)
    {
        if (\Auth::user()->can('manage customer invoice')) {

            $status = Invoice::$statues;
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $query = Invoice::where('customer_id', '=', \Auth::user()->id)->where('status', '!=', '0')->where($column, $ownerId);

            if (!empty($request->issue_date)) {
                $date_range = explode(' - ', $request->issue_date);
                $query->whereBetween('issue_date', $date_range);
            }

            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }
            $invoices = $query->get();

            return view('invoice.index', compact('invoices', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function customerInvoiceShow($id)
    {

        $invoice = Invoice::with('payments.bankAccount')->find($id);

        $user = User::where('id', $invoice->created_by)->first();
        if ($invoice->created_by == $user->creatorId()) {
            $customer = $invoice->customer;
            $iteams = $invoice->items;

            if ($user->type == 'super admin') {
                return view('invoice.view', compact('invoice', 'customer', 'iteams', 'user'));
            } elseif ($user->type == 'company') {
                return view('invoice.customer_invoice', compact('invoice', 'customer', 'iteams', 'user'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sent($id)
    {

        if (\Auth::user()->can('send invoice')) {
            // Send Email

            $incomeAccountId = null;
            $receivableAccountId = null;
            $invoice = Invoice::find($id);
            
            if ($invoice->re_project_id) {
                $project = \App\Models\REProject::find($invoice->re_project_id);
                if ($project) {
                    if (!$project->income_account_id || !$project->receivable_account_id) {
                        \Log::warning('Invoice journal voucher skipped - Project missing COA accounts', [
                            'invoice_id' => $invoice->id,
                            'project_id' => $project->id,
                        ]);
                        return null; // Skip journal entry if project doesn't have COA configured
                    }
                    $incomeAccountId = $project->income_account_id;
                    $receivableAccountId = $project->receivable_account_id;
                }
            }
            $setings = Utility::settings();

            if ($setings['customer_invoice_sent'] == 1) {
                $invoice = Invoice::where('id', $id)->first();
                $invoice->send_date = date('Y-m-d');
                $invoice->status = 1;
                $invoice->save();

                $customer = Customer::where('id', $invoice->customer_id)->first();
                $invoice->name = !empty($customer) ? $customer->name : '';
                $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

                $invoiceId = Crypt::encrypt($invoice->id);
                $invoice->url = route('invoice.pdf', $invoiceId);

                Utility::updateUserBalance('customer', $customer->id, $invoice->getTotal(), 'credit');

                $invoice_products = InvoiceProduct::where('invoice_id', $invoice->id)->get();
                foreach ($invoice_products as $invoice_product) {
                    $product = ProductService::find($invoice_product->product_id);
                    $totalTaxPrice = 0;
                    if ($invoice_product->tax != null) {
                        $taxes = \App\Models\Utility::tax($invoice_product->tax);
                        foreach ($taxes as $tax) {
                            $taxPrice = \App\Models\Utility::taxRate($tax->rate, $invoice_product->price, $invoice_product->quantity, $invoice_product->discount);
                            $totalTaxPrice += $taxPrice;
                        }
                    }

                    $itemAmount = ($invoice_product->price * $invoice_product->quantity) - ($invoice_product->discount) + $totalTaxPrice;

                    $data = [
                        'account_id' => $product->sale_chartaccount_id,
                        'transaction_type' => 'Credit',
                        'transaction_amount' => $itemAmount,
                        'reference' => 'Invoice',
                        'reference_id' => $invoice->id,
                        'reference_sub_id' => $product->id,
                        'date' => $invoice->issue_date,
                    ];
                    // Utility::addTransactionLines($data, 'create');
                }

                $customerArr = [

                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'invoice_name' => $customer->name,
                    'invoice_number' => $invoice->invoice,
                    'invoice_url' => $invoice->url,

                ];
                $resp = Utility::sendEmailTemplate('customer_invoice_sent', [$customer->id => $customer->email], $customerArr);
                
                // Create journal voucher for the invoice
                $this->createInvoiceJournalVoucher($invoice);

                
                //log
                Utility::makeActivityLog(\Auth::user()->id, 'Invoice', $invoice->id, 'Send Invoice to Customer', $invoice->description);
                return redirect()->back()->with('success', __('Invoice successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function resent($id)
    {
        if (\Auth::user()->can('send invoice')) {
            $invoice = Invoice::where('id', $id)->first();

            $customer = Customer::where('id', $invoice->customer_id)->first();
            $invoice->name = !empty($customer) ? $customer->name : '';
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $invoiceId = Crypt::encrypt($invoice->id);
            $invoice->url = route('invoice.pdf', $invoiceId);
            $customerArr = [

                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'invoice_name' => $customer->name,
                'invoice_number' => $invoice->invoice,
                'invoice_url' => $invoice->url,

            ];
            $resp = Utility::sendEmailTemplate('customer_invoice_sent', [$customer->id => $customer->email], $customerArr);
            //log
            Utility::makeActivityLog(\Auth::user()->id, 'Invoice', $invoice->id, 'Resend Invoice to Customer', $invoice->description);

            return redirect()->back()->with('success', __('Invoice successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payment($invoice_id)
    {
        if (\Auth::user()->can('create payment invoice')) {
            $invoice = Invoice::where('id', $invoice_id)->first();
            $user = \Auth::user();
            $ownerId = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
            $column = ($user->type == 'company') ? 'created_by' : 'owned_by';
            $customers = Customer::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $categories = ProductServiceCategory::where($column, '=', $ownerId)->get()->pluck('name', 'id');
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $types = [
                'cash'           => 'Cash',
                // 'cheque'         => 'Cheque',
                'direct_deposit' => 'Direct Deposit',
            ];
            return view('invoice.payment', compact('customers', 'categories', 'accounts', 'invoice', 'types'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

public function createPayment(Request $request, $invoice_id)
{
    $invoice = Invoice::find($invoice_id);
    if ($invoice->getDue() < $request->amount) {
        return redirect()->back()->with('error', __('Invoice payment amount should not greater than subtotal.'));
    }

    if (!\Auth::user()->can('create payment invoice')) {
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    $validator = \Validator::make(
        $request->all(),
        [
            'date'             => 'required|date',
            'amount'           => 'required|numeric|min:0.01',
            'account_id'       => 'required|integer',
            'type'             => 'required|in:cash,cheque,direct_deposit',
            'cheque_number'    => 'required_if:type,cheque|max:100',
            'title_of_account' => 'required_if:type,cheque|max:191',
            'add_receipt'      => 'nullable|file|max:10240',
        ],
        // [
        //     'cheque_number.required_if'    => __('Cheque Number is required when Type is Cheque.'),
        //     'title_of_account.required_if' => __('Title of Account is required when Type is Cheque.'),
        // ]
    );

    if ($validator->fails()) {
        return redirect()->back()->with('error', $validator->getMessageBag()->first())->withInput();
    }

    \DB::beginTransaction();
    try {
        $invoicePayment = new InvoicePayment();
        $invoicePayment->invoice_id      = $invoice_id;
        $invoicePayment->date            = $request->date;
        $invoicePayment->amount          = $request->amount;
        $invoicePayment->account_id      = $request->account_id;
        $invoicePayment->payment_method  = 0;
        $invoicePayment->reference       = $request->reference;
        $invoicePayment->description     = $request->description;

        $invoicePayment->type = strtolower(trim($request->type));
        // Save cheque number only for cheque
        $invoicePayment->cheque_number = $invoicePayment->type === 'cheque' ? $request->cheque_number : null;

        if (!empty($request->add_receipt)) {
            $image_size = $request->file('add_receipt')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
            if ($result == 1) {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                $request->add_receipt->storeAs('uploads/payment', $fileName);
                $invoicePayment->add_receipt = $fileName;
            }
        }

        $invoicePayment->save();

        // Create checklist row for CHEQUE only (hide in UI, show and require when type=cheque)
        // if ($invoicePayment->type == 'cheque') {
        //     CheckList::create([
        //         'invoice_id'         => $invoice->id,
        //         'invoice_payment_id' => $invoicePayment->id,
        //         'account_id'         => $request->account_id,
        //         'account_title'      => $request->title_of_account, // Title of Account (Cheque Account Title Name)
        //         'type'               => $invoicePayment->type,       // 'cheque'
        //         'cheque_number'      => $request->cheque_number,     // Cheque number
        //         'amount'             => $request->amount,
        //         'date'               => $request->date,
        //         'status'             => 'Pending',
        //         'created_by'         => \Auth::user()->id,
        //         'owned_by'           => \Auth::user()->ownedId(),
        //     ]);
        // }

        // Refresh invoice to include the new payment in getDue calculation
        $invoice->refresh();
        $invoice->load('payments');
        
        $due = $invoice->getDue();
        $total = $invoice->getTotal();
        if ($invoice->status == 0) {
            $invoice->send_date = date('Y-m-d');
            $invoice->save();
        }
        if ($due <= 0) {
            $invoice->status = 4;
            $invoice->save();
            
            // Link back to installment if it exists and mark as paid
            if ($invoice->installment_id) {
                $installment = \App\Models\ContractInstallment::find($invoice->installment_id);
                if ($installment) {
                    $installment->status = 'paid';
                    $installment->paid_date = $request->date;
                    $installment->save();
                    
                    // Trigger commission release check for this contract
                    if ($installment->contract_id) {
                        \App\Models\Commission::autoUpdateReleases($installment->contract_id);
                    }
                }
            } elseif ($invoice->contract_id) {
                // Also trigger for contracts linked directly to invoice without specific installment
                \App\Models\Commission::autoUpdateReleases($invoice->contract_id);
            }
        } else {
            $invoice->status = 3;
            $invoice->save();
        }

        $invoicePayment->user_id    = $invoice->customer_id;
        $invoicePayment->user_type  = 'Customer';
        $invoicePayment->type_flow  = 'Partial';
        $invoicePayment->created_by = \Auth::user()->id;
        $invoicePayment->owned_by   = \Auth::user()->ownedId();
        $invoicePayment->payment_id = $invoicePayment->id;
        $invoicePayment->category   = 'Invoice';
        $invoicePayment->account    = $request->account_id;

        Transaction::addTransaction($invoicePayment);

        $customer = Customer::where('id', $invoice->customer_id)->first();

        $payment = new InvoicePayment();
        $payment->name      = $customer['name'];
        $payment->date      = \Auth::user()->dateFormat($request->date);
        $payment->amount    = \Auth::user()->priceFormat($request->amount);
        $payment->invoice   = 'invoice ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
        $payment->dueAmount = \Auth::user()->priceFormat($invoice->getDue());

        Utility::updateUserBalance('customer', $invoice->customer_id, $request->amount, 'debit');
        Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

        // Create receipt voucher (CRV for cash, BRV for bank)
        $bankAccount = BankAccount::find($request->account_id);

        // Get project's receivable account if project exists
        $receivableAccountId = null;
        if ($invoice->re_project_id) {
            $project = \App\Models\ReProject::find($invoice->re_project_id);
            if ($project && $project->receivable_account_id) {
                $receivableAccountId = $project->receivable_account_id;
            }
        }

        $voucherData = [
            'id' => $invoicePayment->id,
            'no' => $invoice->invoice_id,
            'vendor_id' => null,
            'customer_id' => $invoice->customer_id,
            'employee_id' => null,
            'date' => $request->date,
            'reference' => $request->reference,
            'description' => $request->description ?? 'Invoice Payment',
            'category' => 'Invoice',
            'prod_id' => $invoicePayment->id,
            'account_id' => $bankAccount->chart_account_id,
            'amount' => $request->amount,
            'created_at' => date('Y-m-d H:i:s', strtotime($payment->date.' '.date('H:i:s'))),
            'owned_by' => \Auth::user()->ownedId(),
            'created_by' => \Auth::user()->creatorId(),
            're_project_id' => $invoice->re_project_id,
            'tower_id' => $invoice->tower_id,
            'floor_id' => $invoice->floor_id,
            'unit_id' => $invoice->unit_id,
            'receivable_account_id' => $receivableAccountId,
        ];

        // Use CRV for cash accounts, BRV for bank accounts
        if (preg_match('/\bcash\b/i', $bankAccount->bank_name) || preg_match('/\bcash\b/i', $bankAccount->holder_name)) {
            $voucherId = Utility::crv_entry($voucherData); // Cash Receipt Voucher
        } else {
            $voucherId = Utility::brv_entry($voucherData); // Bank Receipt Voucher
        }

        // Save voucher_id to invoice payment
        $invoicePaymentRecord = InvoicePayment::find($invoicePayment->id);
        $invoicePaymentRecord->voucher_id = $voucherId;
        $invoicePaymentRecord->save();

        $setings = Utility::settings();
        if (!empty($setings['new_invoice_payment']) && $setings['new_invoice_payment'] == 1) {
            $customer = Customer::where('id', $invoice->customer_id)->first();
            $invoicePaymentArr = [
                'invoice_payment_name'   => $customer->name,
                'invoice_payment_amount' => $payment->amount,
                'invoice_payment_date'   => $payment->date,
                'payment_dueAmount'      => $payment->dueAmount,
            ];
            $resp = Utility::sendEmailTemplate('new_invoice_payment', [$customer->id => $customer->email], $invoicePaymentArr);
        }

        $module = 'New Invoice Payment';
        $webhook = Utility::webhookSetting($module);
        if ($webhook) {
            $parameter = json_encode($invoice);
            $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
            if ($status != true) {
                \DB::rollBack();
                return redirect()->back()->with('error', __('Webhook call failed.'));
            }
        }

        Utility::makeActivityLog(\Auth::user()->id, 'Invoice Payment', $invoicePayment->id, 'Create Invoice Payment', $customer->name);
        \DB::commit();

        return redirect()->back()->with('success', __('Payment successfully added.'));
    } catch (\Throwable $e) {
        \DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
}


    public function paymentDestroy(Request $request, $invoice_id, $payment_id)
    {
        if (\Auth::user()->can('delete payment invoice')) {
            $payment = InvoicePayment::find($payment_id);

            // Delete payment voucher if exists
            if (!empty($payment->voucher_id)) {
                $journal = \App\Models\JournalEntry::find($payment->voucher_id);
                if ($journal) {
                    \App\Models\JournalItem::where('journal', $journal->id)->delete();
                    TransactionLines::where('reference', 'Invoice Payment')
                        ->where('reference_id', $journal->id)
                        ->delete();
                    $journal->delete();
                }
            }

            InvoicePayment::where('id', '=', $payment_id)->delete();

            InvoiceBankTransfer::where('id', '=', $payment_id)->delete();

            // TransactionLines::where('reference_sub_id', $payment_id)->where('reference', 'Invoice Payment')->delete();

            $invoice = Invoice::where('id', $invoice_id)->first();
            $due = $invoice->getDue();
            $total = $invoice->getTotal();

            if ($due > 0 && $total != $due) {
                $invoice->status = 3;
            } else {
                $invoice->status = 2;
            }

            if (!empty($payment->add_receipt)) {
                //storage limit
                $file_path = '/uploads/payment/' . $payment->add_receipt;
                $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
            }

            $invoice->save();
            $type = 'Partial';
            $user = 'Customer';
            Transaction::destroyTransaction($payment_id, $type, $user);

            Utility::updateUserBalance('customer', $invoice->customer_id, $payment->amount, 'credit');

            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'debit');
            //log
            Utility::makeActivityLog(\Auth::user()->id, 'Invoice Payment', $payment_id, 'Delete Invoice Payment', $invoice->customer->name ?? '');
            return redirect()->back()->with('success', __('Payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function paymentReminder($invoice_id)
    {

        //        dd($invoice_id);
        $invoice = Invoice::find($invoice_id);
        $customer = Customer::where('id', $invoice->customer_id)->first();
        $invoice->dueAmount = \Auth::user()->priceFormat($invoice->getDue());
        $invoice->name = $customer['name'];
        $invoice->date = \Auth::user()->dateFormat($invoice->send_date);
        $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        //For Notification
        $setting = Utility::settings(\Auth::user()->creatorId());
        $customer = Customer::find($invoice->customer_id);
        $reminderNotificationArr = [
            'invoice_number' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
            'customer_name' => $customer->name,
            'user_name' => \Auth::user()->name,
        ];

        //Twilio Notification
        if (isset($setting['twilio_reminder_notification']) && $setting['twilio_reminder_notification'] == 1) {
            Utility::send_twilio_msg($customer->contact, 'invoice_payment_reminder', $reminderNotificationArr);
        }

        // Send Email
        $setings = Utility::settings();
        if ($setings['new_payment_reminder'] == 1) {
            $invoice = Invoice::find($invoice_id);
            $customer = Customer::where('id', $invoice->customer_id)->first();
            $invoice->dueAmount = \Auth::user()->priceFormat($invoice->getDue());
            $invoice->name = $customer['name'];
            $invoice->date = \Auth::user()->dateFormat($invoice->send_date);
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $reminderArr = [

                'payment_reminder_name' => $invoice->name,
                'invoice_payment_number' => $invoice->invoice,
                'invoice_payment_dueAmount' => $invoice->dueAmount,
                'payment_reminder_date' => $invoice->date,

            ];

            $resp = Utility::sendEmailTemplate('new_payment_reminder', [$customer->id => $customer->email], $reminderArr);
        }
        //log
        Utility::makeActivityLog(\Auth::user()->id, 'Invoice Payment', $invoice_id, 'Send Payment Reminder', $customer->name);
        return redirect()->back()->with('success', __('Payment reminder successfully send.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
    }

    public function customerInvoiceSend($invoice_id)
    {
        return view('customer.invoice_send', compact('invoice_id'));
    }

    public function customerInvoiceSendMail(Request $request, $invoice_id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $email = $request->email;
        $invoice = Invoice::where('id', $invoice_id)->first();

        $customer = Customer::where('id', $invoice->customer_id)->first();
        $invoice->name = !empty($customer) ? $customer->name : '';
        $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        $invoiceId = Crypt::encrypt($invoice->id);
        $invoice->url = route('invoice.pdf', $invoiceId);

        try {
            Mail::to($email)->send(new CustomerInvoiceSend($invoice));
        } catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }
        // /log
        Utility::makeActivityLog(\Auth::user()->id, 'Invoice Payment', $invoice_id, 'Send Invoice Email', $customer->name);
        return redirect()->back()->with('success', __('Invoice successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function shippingDisplay(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if ($request->is_display == 'true') {
            $invoice->shipping_display = 1;
        } else {
            $invoice->shipping_display = 0;
        }
        $invoice->save();
        //log
        Utility::makeActivityLog(\Auth::user()->id, 'Invoice Payment', $invoice_id, 'Change Shipping Display Status', $invoice->customer->name);
        return redirect()->back()->with('success', __('Shipping address status successfully changed.'));
    }

    public function duplicate($invoice_id)
    {
        if (\Auth::user()->can('duplicate invoice')) {
            $invoice = Invoice::where('id', $invoice_id)->first();
            $duplicateInvoice = new Invoice();
            $duplicateInvoice->invoice_id = $this->invoiceNumber();
            $duplicateInvoice->customer_id = $invoice['customer_id'];
            $duplicateInvoice->issue_date = date('Y-m-d');
            $duplicateInvoice->due_date = $invoice['due_date'];
            $duplicateInvoice->send_date = null;
            $duplicateInvoice->category_id = $invoice['category_id'];
            $duplicateInvoice->ref_number = $invoice['ref_number'];
            $duplicateInvoice->status = 0;
            $duplicateInvoice->shipping_display = $invoice['shipping_display'];
            $duplicateInvoice->created_by = $invoice['created_by'];
            $duplicateInvoice->save();

            if ($duplicateInvoice) {
                $invoiceProduct = InvoiceProduct::where('invoice_id', $invoice_id)->get();
                foreach ($invoiceProduct as $product) {
                    $duplicateProduct = new InvoiceProduct();
                    $duplicateProduct->invoice_id = $duplicateInvoice->id;
                    $duplicateProduct->product_id = $product->product_id;
                    $duplicateProduct->quantity = $product->quantity;
                    $duplicateProduct->tax = $product->tax;
                    $duplicateProduct->discount = $product->discount;
                    $duplicateProduct->price = $product->price;
                    $duplicateProduct->save();
                }
            }
            //log
            Utility::makeActivityLog(\Auth::user()->id, 'Invoice Payment', $invoice_id, 'Duplicate Invoice', $invoice->customer->name);
            return redirect()->back()->with('success', __('Invoice duplicate successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function previewInvoice($template, $color)
    {

        $objUser = \Auth::user();
        $settings = Utility::settings();
        $invoice = new Invoice();

        $customer = new \stdClass();
        $customer->email = '<Email>';
        $customer->shipping_name = '<Customer Name>';
        $customer->shipping_country = '<Country>';
        $customer->shipping_state = '<State>';
        $customer->shipping_city = '<City>';
        $customer->shipping_phone = '<Customer Phone Number>';
        $customer->shipping_zip = '<Zip>';
        $customer->shipping_address = '<Address>';
        $customer->billing_name = '<Customer Name>';
        $customer->billing_country = '<Country>';
        $customer->billing_state = '<State>';
        $customer->billing_city = '<City>';
        $customer->billing_phone = '<Customer Phone Number>';
        $customer->billing_zip = '<Zip>';
        $customer->billing_address = '<Address>';

        $totalTaxPrice = 0;
        $taxesData = [];

        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $item = new \stdClass();
            $item->name = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax = 5;
            $item->discount = 50;
            $item->price = 100;
            $item->unit = 1;
            $item->description = 'XYZ';

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach ($taxes as $k => $tax) {
                $taxPrice = 10;
                $totalTaxPrice += $taxPrice;
                $itemTax['name'] = 'Tax ' . $k;
                $itemTax['rate'] = '10 %';
                $itemTax['price'] = '$10';
                $itemTax['tax_price'] = 10;
                $itemTaxes[] = $itemTax;
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[] = $item;
        }

        $invoice->invoice_id = 1;
        $invoice->issue_date = date('Y-m-d H:i:s');
        $invoice->due_date = date('Y-m-d H:i:s');
        $invoice->itemData = $items;
        $invoice->status = 0;
        $invoice->totalTaxPrice = 60;
        $invoice->totalQuantity = 3;
        $invoice->totalRate = 300;
        $invoice->totalDiscount = 10;
        $invoice->taxesData = $taxesData;
        $invoice->created_by = $objUser->creatorId();

        $invoice->customField = [];
        $customFields = [];

        $preview = 1;
        $color = '#' . $color;
        $font_color = Utility::getFontColor($color);

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $invoice_logo = Utility::getValByName('invoice_logo');
        if (isset($invoice_logo) && !empty($invoice_logo)) {
            $img = Utility::get_file('invoice_logo/') . $invoice_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        return view('invoice.templates.' . $template, compact('invoice', 'preview', 'color', 'img', 'settings', 'customer', 'font_color', 'customFields'));
    }

    public function invoice($invoice_id)
    {
        $settings = Utility::settings();

        $invoiceId = Crypt::decrypt($invoice_id);
        $invoice = Invoice::where('id', $invoiceId)->first();

        $data = DB::table('settings');
        $data = $data->where('created_by', '=', $invoice->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer = $invoice->customer;
        $items = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        foreach ($invoice->items as $product) {
            $item = new \stdClass();
            $item->name = !empty($product->product) ? $product->product->name : '';
            $item->quantity = $product->quantity;
            $item->tax = $product->tax;
            $item->unit = !empty($product->product) ? $product->product->unit_id : '';
            $item->discount = $product->discount;
            $item->price = $product->price;
            $item->description = $product->description;

            $totalQuantity += $item->quantity;
            $totalRate += $item->price;
            $totalDiscount += $item->discount;

            $taxes = Utility::tax($product->tax);

            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice = Utility::taxRate($tax->rate, $item->price, $item->quantity, $item->discount);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name'] = $tax->name;
                    $itemTax['rate'] = $tax->rate . '%';
                    $itemTax['price'] = Utility::priceFormat($settings, $taxPrice);
                    $itemTax['tax_price'] = $taxPrice;
                    $itemTaxes[] = $itemTax;

                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }
                }
                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }
            $items[] = $item;
        }

        $invoice->itemData = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData = $taxesData;
        $invoice->customField = CustomField::getData($invoice, 'invoice');
        $customFields = [];
        if (!empty(\Auth::user())) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
        }

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($invoice->created_by);
        $invoice_logo = $settings_data['invoice_logo'];
        if (isset($invoice_logo) && !empty($invoice_logo)) {
            $img = Utility::get_file('invoice_logo/') . $invoice_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        if ($invoice) {
            $color = '#' . $settings['invoice_color'];
            $font_color = Utility::getFontColor($color);

            return view('invoice.templates.' . $settings['invoice_template'], compact('invoice', 'color', 'settings', 'customer', 'img', 'font_color', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function saveTemplateSettings(Request $request)
    {

        $post = $request->all();
        unset($post['_token']);

        if (isset($post['invoice_template']) && (!isset($post['invoice_color']) || empty($post['invoice_color']))) {
            $post['invoice_color'] = "ffffff";
        }

        if ($request->invoice_logo) {
            $dir = 'invoice_logo/';
            $invoice_logo = \Auth::user()->id . '_invoice_logo.png';
            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];
            $path = Utility::upload_file($request, 'invoice_logo', $invoice_logo, $dir, $validation);

            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $post['invoice_logo'] = $invoice_logo;
        }

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->creatorId(),
                ]
            );
        }
        //log
        return redirect()->back()->with('success', __('Invoice Setting updated successfully'));
    }

    public function items(Request $request)
    {
        $items = InvoiceProduct::where('invoice_id', $request->invoice_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);
    }

    public function invoiceLink($invoiceId)
    {
        try {
            $id = Crypt::decrypt($invoiceId);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }

        $id = Crypt::decrypt($invoiceId);
        $invoice = Invoice::with(['creditNote', 'payments.bankAccount', 'items.product.unit'])->find($id);
        $settings = Utility::settingsById($invoice->created_by);

        if (!empty($invoice)) {

            $user_id = $invoice->created_by;
            $user = User::find($user_id);
            $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->get();
            $customer = $invoice->customer;
            $iteams = $invoice->items;
            $invoice->customField = CustomField::getData($invoice, 'invoice');
            $customFields = CustomField::where('module', '=', 'invoice')->where('created_by', $invoice->created_by)->get();
            $company_payment_setting = Utility::getCompanyPaymentSetting($user_id);

            // start for storage limit note
            $user_plan = Plan::find($user->plan);
            // end for storage limit note

            return view('invoice.customer_invoice', compact('settings', 'invoice', 'customer', 'iteams', 'invoicePayment', 'customFields', 'user', 'company_payment_setting', 'user_plan'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function export()
    {
        $name = 'invoice_' . date('Y-m-d i:h:s');
        $data = Excel::download(new InvoiceExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    private function createInvoiceJournalVoucher(Invoice $invoice)
    {
        // Check if invoice is linked to a project and validate COA accounts
        $incomeAccountId = null;
        $receivableAccountId = null;
        
        if ($invoice->re_project_id) {
            $project = \App\Models\REProject::find($invoice->re_project_id);
            if ($project) {
                if (!$project->income_account_id || !$project->receivable_account_id) {
                    \Log::warning('Invoice journal voucher skipped - Project missing COA accounts', [
                        'invoice_id' => $invoice->id,
                        'project_id' => $project->id,
                    ]);
                    return null; // Skip journal entry if project doesn't have COA configured
                }
                $incomeAccountId = $project->income_account_id;
                $receivableAccountId = $project->receivable_account_id;
            }
        }
        
        $invoiceProducts = $invoice->items; // must have relation in Invoice model
        // dd($invoiceProducts,'invpro');
        $newitems = [];

        // Include all items (products, subtotals, text lines)
        // Utility::jrentry will handle skipping non-product items safely
        foreach ($invoiceProducts as $product) {
            $item = ProductService::find($product->product_id);
            $taxRate     = !empty($item->tax_id) ? $item->taxRate($item->tax_id) : 0;
            $taxPrice            = ($taxRate / 100) * ($product->price * $product->quantity);
            $newitems[] = [
                'prod_id' => $product->id,
                'item' => $product->product_id,
                'quantity' => $product->quantity,
                'price' => $product->price,
                'discount' => $product->discount,
                'itemTaxPrice' => $taxPrice,
                'description' => $product->description,
            ];
        }
        
        $data = [
            'id' => $invoice->id,
            'no' => $invoice->invoice_id,
            'date' => $invoice->issue_date,
            'created_at' => date('Y-m-d H:i:s', strtotime($invoice->issue_date.' '.date('H:i:s'))),
            'reference' => $invoice->ref_number,
            'category' => 'Invoice',
            'owned_by' => $invoice->owned_by,
            'created_by' => $invoice->created_by,
            'prod_id' => $invoiceProducts->where('product_id', '!=', null)->first()->product_id ?? null,
            'items' => $newitems,
            'customer_id' => $invoice->customer_id,
            'total' => $invoice->getTotal(),
            // Project-specific COA accounts
            're_project_id' => $invoice->re_project_id,
            'tower_id' => $invoice->tower_id,
            'floor_id' => $invoice->floor_id,
            'unit_id' => $invoice->unit_id,
            'income_account_id' => $incomeAccountId,
            'receivable_account_id' => $receivableAccountId,
        ];

        $voucherId = Utility::jrentry($data);
        $invoice = Invoice::find($invoice->id);
        $invoice->voucher_id = $voucherId;
        $invoice->save();

        return $voucherId;
    }
}

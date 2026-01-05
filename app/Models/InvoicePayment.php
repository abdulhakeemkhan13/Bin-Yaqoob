<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'date',
        'amount',
        'account_id',
        'payment_method',
        'order_id',
        'currency',
        'txn_id',
        'payment_type',
        'receipt',
        'reference',
        'description',
        'type',
        'cheque_number',
        'voucher_id',
        'add_receipt',
        'created_by',
        'owned_by',
        'user_id',
        'user_type',
        'payment_id',
        'category',
        'account',
        'type_flow',
    ];


    public function bankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'account_id');
    }
    public function checkList()
    {
        return $this->hasOne(CheckList::class, 'invoice_payment_id');
    }
}

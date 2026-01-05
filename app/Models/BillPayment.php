<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $fillable = [
        'bill_id',
        'date',
        'amount',
        'account_id',
        'payment_method',
        'reference',
        'description',
        'type',
        'cheque_number',
        'voucher_id',
        'created_by',
        'owned_by',
        'user_id',
        'user_type',
        'payment_id',
        'category',
        'account',
        'type_flow',
    ];

    public function checkList()
    {
        return $this->hasOne(\App\Models\CheckList::class, 'bill_payment_id');
    }

    public function bill()
    {
        return $this->belongsTo(\App\Models\Bill::class);
    }

    public function bankAccount()
{
    return $this->hasOne(\App\Models\BankAccount::class, 'id', 'account_id');
}

}

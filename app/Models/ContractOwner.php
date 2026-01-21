<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractOwner extends Model
{
    protected $fillable = [
        'contract_id',
        'customer_id',
        'ownership_percent',
        'role',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

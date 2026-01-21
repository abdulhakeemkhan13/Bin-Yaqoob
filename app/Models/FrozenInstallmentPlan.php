<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrozenInstallmentPlan extends Model
{
    protected $table = 'frozen_installment_plans';

    protected $fillable = [
        'contract_id',
        'down_payment_amount',
        'down_payment_due_date',
        'installment_count',
        'installment_frequency',
        'installment_amount',
        'possession_amount',
        'balloon_amount',
        'total_payable',
        'plan_start_date',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}

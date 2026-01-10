<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReUnitPaymentPlan extends Model
{
    use HasFactory;

    protected $table = 're_unit_payment_plans';

    protected $fillable = [
        're_unit_id',
        're_payment_plan_id',
        'price_override',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
    ];

    /**
     * The unit
     */
    public function unit()
    {
        return $this->belongsTo(ReUnit::class, 're_unit_id');
    }

    /**
     * The payment plan
     */
    public function paymentPlan()
    {
        return $this->belongsTo(RePaymentPlan::class, 're_payment_plan_id');
    }
}

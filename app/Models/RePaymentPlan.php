<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RePaymentPlan extends Model
{
    use HasFactory;

    protected $table = 're_payment_plans';

    protected $fillable = [
        'plan_name',
        'duration_months',
        'down_payment_percentage',
        'down_payment_amount',
        'num_installments',
        'frequency',
        'possession_charges',
        'discount',
        'extra_charges',
        'booking_charges',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'down_payment_percentage' => 'decimal:2',
        'down_payment_amount' => 'decimal:2',
        'possession_charges' => 'decimal:2',
        'discount' => 'decimal:2',
        'extra_charges' => 'decimal:2',
        'booking_charges' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static $frequencies = ['Monthly', 'Quarterly', 'Half-Yearly', 'Yearly'];

    /**
     * Projects that use this payment plan
     */
    public function projects()
    {
        return $this->belongsToMany(ReProject::class, 're_project_payment_plans', 're_payment_plan_id', 're_project_id')
            ->withTimestamps();
    }

    /**
     * Units that have this plan as override
     */
    public function unitOverrides()
    {
        return $this->hasMany(ReUnitPaymentPlan::class, 're_payment_plan_id');
    }

    /**
     * Bookings using this plan
     */
    public function bookings()
    {
        return $this->hasMany(ReBooking::class, 're_payment_plan_id');
    }

    /**
     * Creator of this plan
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate installment amount for a given price
     */
    public function calculateInstallmentAmount($totalPrice)
    {
        $downPayment = 0;
        
        if ($this->down_payment_percentage) {
            $downPayment = ($totalPrice * $this->down_payment_percentage) / 100;
        } elseif ($this->down_payment_amount) {
            $downPayment = $this->down_payment_amount;
        }
        
        $remainingAmount = $totalPrice - $downPayment + ($this->possession_charges ?? 0) + ($this->extra_charges ?? 0) - ($this->discount ?? 0);
        
        return $this->num_installments > 0 ? $remainingAmount / $this->num_installments : 0;
    }
}

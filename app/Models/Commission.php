<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'employee_id',
        'commission_for',
        'dealer_id',
        'contract_id',
        'deal_id',
        'title',
        'type',
        'amount',
        'commission_percentage',
        'total_commission_amount',
        'release_condition_type',
        'release_percentage',
        'status',
        'amount_released',
        'notes',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function dealer()
    {
        return $this->hasOne('App\Models\User', 'id', 'dealer_id');
    }

    public function contract()
    {
        return $this->belongsTo('App\Models\Contract', 'contract_id');
    }

    public function deal()
    {
        return $this->belongsTo('App\Models\Deal', 'deal_id');
    }

    public static $commission_for = [
        'dealer' => 'Dealer',
        'company_management' => 'Company Management',
        'employee' => 'Company Employee',
    ];

    public static $release_condition_types = [
        'full_payment_received' => 'After Full Payment',
        'down_payment_received' => 'After Down Payment',
        'percentage_received' => 'Proportional to Payment %',
        'fixed_milestone' => 'On Fixed Milestone (Manual)',
    ];

    public static $commissiontype = [
        'fixed' => 'Fixed',
        'percentage' => 'Percentage',
    ];

    /**
     * Automatically check and update all commissions related to a contract
     */
    public static function autoUpdateReleases($contractId)
    {
        $commissions = self::where('contract_id', $contractId)
            ->where('status', '!=', 'released')
            ->get();

        if ($commissions->isEmpty()) {
            return;
        }

        $contract = Contract::with('installments')->find($contractId);
        if (!$contract) {
            return;
        }

        $total_paid = $contract->installments->where('status', 'paid')->sum('amount');
        $total_value = $contract->net_sale_price > 0 ? $contract->net_sale_price : $contract->sale_price;

        if ($total_value <= 0) {
            return;
        }

        foreach ($commissions as $commission) {
            $total_expected = $commission->total_commission_amount;
            $already_released = $commission->amount_released;
            $releasable = 0;

            switch ($commission->release_condition_type) {
                case 'full_payment_received':
                    if ($total_paid >= $total_value) {
                        $releasable = $total_expected - $already_released;
                    }
                    break;

                case 'down_payment_received':
                    $down_payment_paid = $contract->installments->where('installment_type', 'down_payment')->where('status', 'paid')->count() > 0;
                    if ($down_payment_paid) {
                        $releasable = $total_expected - $already_released;
                    }
                    break;

                case 'percentage_received':
                    $payment_ratio = $total_paid / $total_value;
                    $should_be_released = $total_expected * $payment_ratio;
                    $diff = $should_be_released - $already_released;
                    $releasable = $diff > 0 ? $diff : 0;
                    break;
            }

            if ($releasable > 0) {
                $commission->amount_released += $releasable;
                if ($commission->amount_released >= $commission->total_commission_amount) {
                    $commission->status = 'released';
                } else {
                    $commission->status = 'partially_released';
                }
                $commission->save();
            }
        }
    }
}

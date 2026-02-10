<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'client_name',
        'subject',
        'value',
        'type',
        'start_date',
        'end_date',
        'description',
        'status',
        'contract_description',
        'company_signature',
        'client_signature',
        'created_by',
        'project_id',
        'owned_by',
        'payment_plan_id',
        'num_installments',
        'down_payment_percent',
        'down_payment_amount',
        'deal_id',
        'customer_id',
        'contract_no',
        're_project_id',
        'tower_id',
        'floor_id',
        'unit_id',
        'sale_price',
        'price_per_sqft',
        'discount_amount',
        'other_charges',
        'net_sale_price',
        'booking_date',
        'agreement_date',
        'possession_due_date',
        'payment_plan_type',
        'penalty_percent_per_month',
        'grace_days',
        // Fine configuration fields
        'fine_percentage',
        'fine_apply_after_due_date',
        'fine_frequency',
        // Discount configuration fields
        'discount_type',
        'discount_value',
        // Possession charge fields
        'possession_charge_percentage',
        'possession_charge_type',
    ];

    // Fine frequency constants
    const FINE_FREQUENCY_ONE_TIME = 'one_time';
    const FINE_FREQUENCY_EVERY_MONTH = 'every_month_after_due';

    // Discount type constants
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FIXED = 'fixed_amount';

    public static $status = [
        'accept' => 'Accept',
        'decline' => 'Decline',
    ];

    public static function getStatusList()
    {
        return self::$status;
    }

    public function clients()
    {
        return $this->hasOne('App\Models\User', 'id', 'client_name');
    }

    public function types()
    {
        return $this->hasOne('App\Models\ContractType', 'id', 'type');
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'customer_id');
    }

    public static function getContractSummary($contracts)
    {
        $total = 0;

        foreach($contracts as $contract)
        {
            $total += $contract->value;
        }

        return \Auth::user()->priceFormat($total);
    }

    public function projects()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }

    public function re_project()
    {
        return $this->belongsTo(ReProject::class, 're_project_id');
    }

    public function tower()
    {
        return $this->belongsTo(ReTower::class, 'tower_id');
    }

    public function floor()
    {
        return $this->belongsTo(ReFloor::class, 'floor_id');
    }

    public function unit()
    {
        return $this->belongsTo(ReUnit::class, 'unit_id');
    }

    public function owners()
    {
        return $this->hasMany(ContractOwner::class, 'contract_id');
    }

    public function installment_plan()
    {
        return $this->hasOne(FrozenInstallmentPlan::class, 'contract_id');
    }

    public function installments()
    {
        return $this->hasMany(ContractInstallment::class, 'contract_id')->orderBy('installment_number');
    }
    public function files()
    {
        return $this->hasMany('App\Models\Contract_attachment', 'contract_id' , 'id');
    }
    public function notes()
    {
        return $this->hasMany('App\Models\ContractNotes', 'contract_id' , 'id');
    }
    public function comment()
    {
        return $this->hasMany('App\Models\ContractComment', 'contract_id', 'id');
    }
    public function note()
    {
        return $this->hasMany('App\Models\ContractNotes', 'contract_id', 'id');
    }

    public function ContractAttechment()
    {
        return $this->belongsTo('App\Models\Contract_attachment', 'id', 'contract_id');
    }

    public function ContractComment()
    {
        return $this->belongsTo('App\Models\ContractComment', 'id', 'contract_id');
    }

    public function ContractNote()
    {
        return $this->belongsTo('App\Models\ContractNotes', 'id', 'contract_id');
    }

}

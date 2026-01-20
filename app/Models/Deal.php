<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'price',
        'pipeline_id',
        'stage_id',
        'group_id',
        'sources',
        'products',
        'created_by',
        'notes',
        'labels',
        'permissions',
        'status',
        'is_active',
        // Unit selection fields
        're_project_id',
        're_tower_id',
        're_floor_id',
        're_unit_id',
        'offered_price',
        'discount',
        'expected_closing_date',
        'contract_id',
        // Client details
        'customer_type',
        'full_name',
        'father_or_company_name',
        'cnic_or_ntn',
        'mobile_primary',
        'mobile_secondary',
        'current_address',
        'permanent_address',
        'nationality',
        'nominee_name',
        'nominee_relation',
        'nominee_cnic',
        'nominee_contact',
    ];

    // Add this property with default empty values
    protected $attributes = [
        'labels' => '',
        'products' => '',
        'sources' => '',
    ];

    public function __get($key)
    {
        // Handle the case when properties are accessed but not set
        if (($key === 'labels' || $key === 'products' || $key === 'sources') && !isset($this->attributes[$key])) {
            return '';
        }
        
        return parent::__get($key);
    }

    public static $permissions = [
        'Client View Tasks',
        'Client View Products',
        'Client View Sources',
        'Client View Contacts',
        'Client View Files',
        'Client View Invoices',
        'Client View Custom fields',
        'Client View Members',
        'Client Add File',
        'Client Deal Activity',
    ];

    public static $statues = [
        'Active' => 'Active',
        'Won' => 'Won',
        'Loss' => 'Loss',
    ];

    public $customField;

    public function labels()
    {
        if($this->labels)
        {
            return Label::whereIn('id', explode(',', $this->labels))->get();
        }

        return collect();
    }

    public function pipeline()
    {
        return $this->hasOne('App\Models\Pipeline', 'id', 'pipeline_id');
    }

    public function stage()
    {
        return $this->hasOne('App\Models\Stage', 'id', 'stage_id');
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    public function clients()
    {
        return $this->belongsToMany('App\Models\User', 'client_deals', 'deal_id', 'client_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_deals', 'deal_id', 'user_id');
    }

    public function products()
    {
        if($this->products)
        {
            return ProductService::whereIn('id', explode(',', $this->products))->get();
        }

        return collect();
    }

    public function sources()
    {
        if($this->sources)
        {
            return Source::whereIn('id', explode(',', $this->sources))->get();
        }

        return collect();
    }

    public function files()
    {
        return $this->hasMany('App\Models\DealFile', 'deal_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany('App\Models\DealTask', 'deal_id', 'id');
    }

    public function complete_tasks()
    {
        return $this->hasMany('App\Models\DealTask', 'deal_id', 'id')->where('status', '=', 1);
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice', 'deal_id', 'id');
    }

    public function calls()
    {
        return $this->hasMany('App\Models\DealCall', 'deal_id', 'id');
    }

    public function emails()
    {
        return $this->hasMany('App\Models\DealEmail', 'deal_id', 'id')->orderByDesc('id');
    }

    public function activities()
    {
        return $this->hasMany('App\Models\ActivityLog', 'deal_id', 'id')->orderBy('id', 'desc');
    }

    public function discussions()
    {
        return $this->hasMany('App\Models\DealDiscussion', 'deal_id', 'id')->orderBy('id', 'desc');
    }

    public static function getDealSummary($deals)
    {
        $total = 0;

        foreach($deals as $deal)
        {
            $total += $deal->price;
        }

        return \Auth::user()->priceFormat($total);
    }

    /**
     * Get the project for the deal.
     */
    public function project()
    {
        return $this->belongsTo(ReProject::class, 're_project_id');
    }

    /**
     * Get the tower for the deal.
     */
    public function tower()
    {
        return $this->belongsTo(ReTower::class, 're_tower_id');
    }

    /**
     * Get the floor for the deal.
     */
    public function floor()
    {
        return $this->belongsTo(ReFloor::class, 're_floor_id');
    }

    /**
     * Get the unit for the deal.
     */
    public function unit()
    {
        return $this->belongsTo(ReUnit::class, 're_unit_id');
    }

    /**
     * Get the contract for the deal.
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}

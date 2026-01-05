<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'user_id',
        'pipeline_id',
        'stage_id',
        'sources',
        'products',
        'notes',
        'labels',
        'order',
        'created_by',
        'is_active',
        'date',
    ];

    // Add this property with default empty value
    protected $attributes = [
        'labels' => '',
        'products' => '',
    ];

    public function __get($key)
    {
        // Handle the case when properties are accessed but not set
        if (($key === 'labels' || $key === 'products' || $key === 'sources') && !isset($this->attributes[$key])) {
            return '';
        }
        
        return parent::__get($key);
    }

    public function labels()
    {
        if($this->labels && !empty($this->labels)) {
            return Label::whereIn('id', explode(',', $this->labels))->get();
        }
        return collect();
    }
    public function stage()
    {
        return $this->hasOne('App\Models\LeadStage', 'id', 'stage_id');
    }


    public function files()
    {
        return $this->hasMany('App\Models\LeadFile', 'lead_id', 'id');
    }

    public function pipeline()
    {
        return $this->hasOne('App\Models\Pipeline', 'id', 'pipeline_id');
    }

    public function products()
    {
        if($this->products && !empty($this->products)) {
            return ProductService::whereIn('id', explode(',', $this->products))->get();
        }
        return collect();
    }

    public function sources()
    {
        if($this->sources && !empty($this->sources)) {
            return Source::whereIn('id', explode(',', $this->sources))->get();
        }
        return collect();
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_leads', 'lead_id', 'user_id');
    }

    public function activities()
    {
        return $this->hasMany('App\Models\LeadActivityLog', 'lead_id', 'id')->orderBy('id', 'desc');
    }

    public function discussions()
    {
        return $this->hasMany('App\Models\LeadDiscussion', 'lead_id', 'id')->orderBy('id', 'desc');
    }

    public function calls()
    {
        return $this->hasMany('App\Models\LeadCall', 'lead_id', 'id');
    }

    public function emails()
    {
        return $this->hasMany('App\Models\LeadEmail', 'lead_id', 'id')->orderByDesc('id');
    }
}

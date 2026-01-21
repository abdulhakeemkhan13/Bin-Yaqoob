<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    protected $fillable = [
        'journal',
        'account',
        'description',
        'product_ids',
        'prod_tax_id',
        'debit',
        'credit',
        're_project_id',
        'tower_id',
        'floor_id',
        'unit_id',
    ];

    public function accounts()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'account');
    }


}

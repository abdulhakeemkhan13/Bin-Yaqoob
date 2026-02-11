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
        'product_id',
        'prod_tax_id',
        'debit',
        'credit',
        'type',
        'name',
        'customer_id',
        'vendor_id',
        'employee_id',
        're_project_id',
        'tower_id',
        'floor_id',
        'unit_id',
    ];

    public function accounts()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'account');
    }

    public function journalEntry()
    {
        return $this->hasOne('App\Models\JournalEntry', 'id', 'journal');
    }

}

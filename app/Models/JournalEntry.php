<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'date',
        'reference',
        'description',
        'journal_id',
        'voucher_type',
        'reference_id',
        'prod_id',
        'category',
        'vendor_id',
        'customer_id',
        'employee_id',
        'owned_by',
        'created_by',
        're_project_id',
        'tower_id',
        'floor_id',
        'unit_id',
    ];


    public function accounts()
    {
        return $this->hasmany('App\Models\JournalItem', 'journal', 'id');
    }

    public function totalCredit()
    {
        $total = 0;
        foreach($this->accounts as $account)
        {
            $total += $account->credit;
        }

        return $total;
    }

    public function totalDebit()
    {
        $total = 0;
        foreach($this->accounts as $account)
        {
            $total += $account->debit;
        }

        return $total;
    }
 /**
     * Scope to filter by module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter by reference
     */
    public function scopeByReference($query, $referenceId, $category = null)
    {
        $query->where('reference_id', $referenceId);
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

}

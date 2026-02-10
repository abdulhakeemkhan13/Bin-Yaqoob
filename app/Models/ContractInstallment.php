<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractInstallment extends Model
{
    protected $table = 'contract_installments';

    protected $fillable = [
        'contract_id',
        'unit_id',
        'installment_number',
        'installment_type',
        'amount',
        'issue_date',
        'due_date',
        'status',
        'invoice_id',
        'paid_date',
        'description',
        'created_by',
        'owned_by',
        // Fine tracking fields
        'fine_amount',
        'fine_applied_date',
        'last_fine_calculation_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public static $statuses = ['pending', 'generated', 'paid', 'overdue'];
    public static $types = ['down_payment', 'installment'];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function unit()
    {
        return $this->belongsTo(ReUnit::class, 'unit_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}

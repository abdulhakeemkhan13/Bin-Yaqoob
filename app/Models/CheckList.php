<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckList extends Model
{
    protected $table = 'check_lists';

    public $timestamps = true;

    protected $fillable = [
        'invoice_id',
        'invoice_payment_id',
        'bill_id',
        'bill_payment_id',
        'minutes_sheet_id',
        'payee_name',
        'account_id',
        'account_title',
        'account_number',
        'bank_coa',
        'other_coa',
        'journal_entry_id',
        'type',
        'cheque_number',
        'amount',
        'date',
        'status',
        'notes',
        'forward_to',
        'approved_at',
        'approved_by',
        'created_by',
        'owned_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function payment()
    {
        return $this->belongsTo(InvoicePayment::class, 'invoice_payment_id');
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
    public function billPayment()
    {
        return $this->belongsTo(BillPayment::class, 'bill_payment_id');
    }
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'account_id');
    }
    public function minutesSheet()
    {
        return $this->belongsTo(MinutesSheet::class, 'minutes_sheet_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match(strtolower($this->status)) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Generate check number for minutes sheet
     */
    public static function generateCheckNumber($minutesSheetId)
    {
        $prefix = 'CHK-MS-';
        $count = self::where('minutes_sheet_id', $minutesSheetId)->count() + 1;
        return $prefix . str_pad($minutesSheetId, 5, '0', STR_PAD_LEFT) . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}


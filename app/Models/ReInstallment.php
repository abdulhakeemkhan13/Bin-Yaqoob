<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReInstallment extends Model
{
    use HasFactory;

    protected $table = 're_installments';

    protected $fillable = [
        're_booking_id',
        'installment_no',
        'due_date',
        'amount',
        'amount_paid',
        'paid_date',
        'payment_method',
        'receipt_number',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public static $statuses = ['Pending', 'Paid', 'Partial', 'Overdue'];

    /**
     * The booking this installment belongs to
     */
    public function booking()
    {
        return $this->belongsTo(ReBooking::class, 're_booking_id');
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAttribute()
    {
        return $this->amount - $this->amount_paid;
    }

    /**
     * Check if overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->status === 'Pending' && $this->due_date->isPast();
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($amount = null, $paymentMethod = null, $receiptNumber = null)
    {
        $payAmount = $amount ?? $this->remaining;
        $this->amount_paid += $payAmount;
        $this->paid_date = now();
        $this->payment_method = $paymentMethod;
        $this->receipt_number = $receiptNumber;
        
        if ($this->amount_paid >= $this->amount) {
            $this->status = 'Paid';
        } else {
            $this->status = 'Partial';
        }
        
        $this->save();
    }
}

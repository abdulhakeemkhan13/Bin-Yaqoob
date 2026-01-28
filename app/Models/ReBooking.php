<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ReBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 're_bookings';

    protected $fillable = [
        'booking_number',
        'deal_id',
        're_unit_id',
        're_payment_plan_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_cnic',
        'customer_email',
        'booking_date',
        'total_price',
        'down_payment',
        'discount',
        'extra_charges',
        'net_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'discount' => 'decimal:2',
        'extra_charges' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public static $statuses = ['Draft', 'Active', 'Cancelled', 'Completed', 'Transferred'];

    /**
     * The unit being booked
     */
    public function unit()
    {
        return $this->belongsTo(ReUnit::class, 're_unit_id');
    }

    /**
     * The payment plan selected
     */
    public function paymentPlan()
    {
        return $this->belongsTo(RePaymentPlan::class, 're_payment_plan_id');
    }

    /**
     * Customer (if registered user)
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Installments for this booking
     */
    public function installments()
    {
        return $this->hasMany(ReInstallment::class, 're_booking_id');
    }

    /**
     * The deal associated with this booking
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate unique booking number
     */
    public static function generateBookingNumber()
    {
        $prefix = 'BK';
        $year = date('Y');
        $lastBooking = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        
        if ($lastBooking) {
            $lastNumber = (int) substr($lastBooking->booking_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate installments for this booking
     */
    public function generateInstallments()
    {
        $plan = $this->paymentPlan;
        if (!$plan) return;

        // Delete existing installments
        $this->installments()->delete();

        $installmentAmount = $plan->calculateInstallmentAmount($this->total_price);
        $startDate = Carbon::parse($this->booking_date);
        
        // Add months based on frequency
        $monthsToAdd = match ($plan->frequency) {
            'Monthly' => 1,
            'Quarterly' => 3,
            'Half-Yearly' => 6,
            'Yearly' => 12,
            default => 1,
        };
        
        for ($i = 1; $i <= $plan->num_installments; $i++) {
            $dueDate = $startDate->copy()->addMonths($monthsToAdd * $i);
            
            ReInstallment::create([
                're_booking_id' => $this->id,
                'installment_no' => $i,
                'due_date' => $dueDate,
                'amount' => round($installmentAmount, 2),
                'amount_paid' => 0,
                'status' => 'Pending',
            ]);
        }
    }

    /**
     * Calculate amounts
     */
    public function calculateNetAmount()
    {
        return $this->total_price - $this->discount + $this->extra_charges;
    }

    public function tower()
    {
        return $this->belongsTo(ReTower::class, 're_tower_id');
    }

    // floor
    public function floor()
    {
        return $this->belongsTo(ReFloor::class, 're_floor_id');
    }

    // project
    public function project()
    {
        return $this->belongsTo(ReProject::class, 're_project_id');
    }
}

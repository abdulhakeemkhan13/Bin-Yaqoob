<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinutesSheet extends Model
{
    /**
     * Approval chain order - strict hierarchy
     */
    public const APPROVAL_CHAIN = [
        'Company',
        'Senior Executive Finance',
        'General Manager Finance',
        'COO',
        'CEO',
        'Chairman',
    ];

    protected $fillable = [
        'amount',
        'date',
        'reference_no',
        'subject',
        'description',
        'bank_account_id',
        'created_by',
        'designation',
        'current_stage',
        'status',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relationship: Bank Account
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    /**
     * Relationship: Creator (User)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Logs
     */
    public function logs()
    {
        return $this->hasMany(MinutesSheetLog::class, 'minutes_sheet_id')->orderBy('created_at', 'desc');
    }

    /**
     * Relationship: Checks (using existing CheckList table)
     */
    public function checks()
    {
        return $this->hasMany(CheckList::class, 'minutes_sheet_id');
    }

    /**
     * Get total amount of all checks
     */
    public function getTotalChecksAmount()
    {
        return $this->checks()->whereNotIn('status', ['Rejected', 'rejected'])->sum('amount');
    }

    /**
     * Get remaining amount available for checks
     */
    public function getRemainingAmount()
    {
        return $this->amount - $this->getTotalChecksAmount();
    }

    /**
     * Check if more checks can be created
     */
    public function canCreateCheck($amount = 0)
    {
        return $this->status === 'approved' && ($this->getRemainingAmount() >= $amount);
    }

    /**
     * Get the next stage in the approval chain
     */
    public static function getNextStage($currentDesignation)
    {
        $index = array_search($currentDesignation, self::APPROVAL_CHAIN);
        
        if ($index === false || $index >= count(self::APPROVAL_CHAIN) - 1) {
            return null; // No next stage or already at Chairman
        }
        
        return self::APPROVAL_CHAIN[$index + 1];
    }

    /**
     * Get the previous stage in the approval chain
     */
    public static function getPreviousStage($currentDesignation)
    {
        $index = array_search($currentDesignation, self::APPROVAL_CHAIN);
        
        if ($index === false || $index <= 0) {
            return null; // No previous stage or already at first level
        }
        
        return self::APPROVAL_CHAIN[$index - 1];
    }

    /**
     * Get the stage index (position in chain)
     */
    public static function getStageIndex($designation)
    {
        $index = array_search($designation, self::APPROVAL_CHAIN);
        return $index !== false ? $index : -1;
    }

    /**
     * Check if a user can be the current approver
     */
    public function isCurrentApprover($userDesignation)
    {
        return $this->status === 'pending' && $this->current_stage === $userDesignation;
    }

    /**
     * Check if this minutes sheet can be viewed by a user based on their designation
     * All users in the approval chain can view all minutes sheets (including approved/rejected)
     */
    public function canBeViewedBy($userDesignation)
    {
        // Any user in the approval chain can view any minutes sheet
        return in_array($userDesignation, self::APPROVAL_CHAIN);
    }

    /**
     * Check if user designation can create minutes sheets
     */
    public static function canCreate($designation)
    {
        // Chairman cannot create
        return $designation !== 'Chairman' && in_array($designation, self::APPROVAL_CHAIN);
    }

    /**
     * Check if this is the final approval stage
     */
    public function isFinalStage()
    {
        return $this->current_stage === 'Chairman';
    }

    /**
     * Generate a unique reference number
     */
    public static function generateReferenceNo()
    {
        $prefix = 'MS-';
        $year = date('Y');
        $lastRecord = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->reference_no, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}

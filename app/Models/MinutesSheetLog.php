<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinutesSheetLog extends Model
{
    protected $fillable = [
        'minutes_sheet_id',
        'action_by',
        'action',
        'remarks',
        'details',
    ];

    /**
     * Relationship: Minutes Sheet
     */
    public function minutesSheet()
    {
        return $this->belongsTo(MinutesSheet::class, 'minutes_sheet_id');
    }

    /**
     * Relationship: Action By User
     */
    public function actionByUser()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    /**
     * Get action badge class
     */
    public function getActionBadgeClass()
    {
        return match($this->action) {
            'created' => 'bg-info',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'edited' => 'bg-warning',
            default => 'bg-secondary',
        };
    }
}

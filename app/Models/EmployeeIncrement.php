<?php

// app/Models/EmployeeIncrement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeIncrement extends Model
{
    protected $fillable = [
        'employee_id',
        'previous_salary',
        'increment_amount',
        'increment_date',
        'notes',
        'created_by',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

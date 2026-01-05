<?php

// app/Models/Reminder.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Reminder extends Model
{
    protected $fillable = [
        'reminder_type_id','user_id','title','description',
        'due_date','before_days','remind_from_date','is_completed','completed_at'
    ];
    protected $casts = [
        'due_date' => 'date',
        'remind_from_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function type() { return $this->belongsTo(ReminderType::class, 'reminder_type_id'); }
    public function user() { return $this->belongsTo(User::class); }

    // Ensure remind_from_date stays in sync when setting attributes programmatically
    protected static function booted() {
        static::saving(function (Reminder $r) {
            $r->remind_from_date = Carbon::parse($r->due_date)->subDays((int) $r->before_days)->toDateString();
        });
    }
}

<?php

// app/Models/ReminderType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderType extends Model
{
    protected $fillable = ['name','slug'];

    public function reminders() {
        return $this->hasMany(Reminder::class);
    }
}

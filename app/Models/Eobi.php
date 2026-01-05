<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eobi extends Model
{
    protected $fillable = ['employee_id','title','amount','created_by'];

public function employee()
{
    // return the model instance so Blade usage employee()->name works
    return $this->hasOne(\App\Models\Employee::class, 'id', 'employee_id')->first();
}
}


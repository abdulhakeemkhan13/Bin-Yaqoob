<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlip extends Model
{
    protected $fillable = [
        'employee_id',
        'net_payble',
        'basic_salary',
        'salary_month',
        'status',
        'allowance',
        'commission',
        'loan',
        'saturation_deduction',
        'other_payment',
        'overtime',
        'tax',
        'eobi',
        'leaves',
        'created_by',
        // 'owned_by', // add if you ever use mass assignment for create()
    ];

    protected $casts = [
        'allowance'            => 'array',
        'commission'           => 'array',
        'loan'                 => 'array',
        'saturation_deduction' => 'array',
        'other_payment'        => 'array',
        'overtime'             => 'array',
        'tax'                  => 'array',
        'eobi'                 => 'array',
        'leaves'               => 'array',
    ];

    public static function employee($id)
    {
        return Employee::find($id);
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }
}

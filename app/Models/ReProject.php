<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReProject extends Model
{
    use SoftDeletes;

    protected $table = 're_projects';

    protected $fillable = [
        'name',
        'code',
        'city',
        'area',
        'address',
        'total_floors',
        'total_towers',
        'total_units',
        'type',
        'status',
        'start_date',
        'expected_completion',
        'description',
        'approval_authority',
        'noc_number',
        'approval_date',
        'income_account_id',
        'receivable_account_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_completion' => 'date',
        'approval_date' => 'date',
    ];

    public static $approvalAuthorities = ['LDA', 'CDA', 'SBCA', 'RDA', 'KDA', 'TMA', 'Other'];

    public static $types = ['Residential', 'Commercial', 'Mixed'];
    public static $statuses = ['Planning', 'Active', 'Completed'];

    /**
     * Get the income chart of account.
     */
    public function incomeAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'income_account_id');
    }

    /**
     * Get the receivable chart of account.
     */
    public function receivableAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'receivable_account_id');
    }

    /**
     * Get the floors for the project.
     */
    public function floors()
    {
        return $this->hasMany(ReFloor::class, 're_project_id');
    }

    /**
     * Get the towers for the project.
     */
    public function towers()
    {
        return $this->hasMany(ReTower::class, 're_project_id');
    }

    /**
     * Get the payment plans assigned to this project.
     */
    public function paymentPlans()
    {
        return $this->belongsToMany(RePaymentPlan::class, 're_project_payment_plans', 're_project_id', 're_payment_plan_id')
            ->withTimestamps();
    }

    /**
     * Get the user who created the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all units through floors.
     */
    public function units()
    {
        return $this->hasManyThrough(ReUnit::class, ReFloor::class, 're_project_id', 're_floor_id');
    }

    /**
     * Generate unique project code.
     */
    public static function generateCode()
    {
        $lastProject = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastProject ? $lastProject->id + 1 : 1;
        return 'PRJ-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReTower extends Model
{
    protected $table = 're_towers';

    protected $fillable = [
        're_project_id',
        'tower_code',
        'tower_name',
        'floors_count',
        'construction_type',
        'elevator_count',
        'parking_type',
        'status',
    ];

    public static $constructionTypes = ['RCC', 'Steel', 'Composite', 'Other'];
    public static $parkingTypes = ['Basement', 'Podium', 'Mechanical', 'Open', 'None'];
    public static $statuses = ['Planning', 'Construction', 'Completed'];

    /**
     * Get the project that owns the tower.
     */
    public function project()
    {
        return $this->belongsTo(ReProject::class, 're_project_id');
    }

    /**
     * Get the floors for the tower.
     */
    public function floors()
    {
        return $this->hasMany(ReFloor::class, 're_tower_id');
    }
}

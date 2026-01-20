<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReFloor extends Model
{
    protected $table = 're_floors';

    protected $fillable = [
        're_project_id',
        're_tower_id',
        'code',
        'floor_number',
        'floor_name',
        'total_units',
    ];

    /**
     * Get the project that owns the floor.
     */
    public function project()
    {
        return $this->belongsTo(ReProject::class, 're_project_id');
    }

    /**
     * Get the tower that owns the floor.
     */
    public function tower()
    {
        return $this->belongsTo(ReTower::class, 're_tower_id');
    }

    /**
     * Get the units for the floor.
     */
    public function units()
    {
        return $this->hasMany(ReUnit::class, 're_floor_id');
    }
}

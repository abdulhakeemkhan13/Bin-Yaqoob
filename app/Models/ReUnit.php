<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReUnit extends Model
{
    use SoftDeletes;

    protected $table = 're_units';

    protected $fillable = [
        're_floor_id',
        'product_id',
        'unit_number',
        'unit_type',
        'covered_area',
        'price',
        'price_per_sqft',
        'facing',
        'status',
        'description',
        // Flat specific
        'bedrooms',
        'bathrooms',
        'balconies',
        'has_parking',
        // Shop/Office specific
        'floor_position',
        'has_mezzanine',
        'has_washroom',
        // Penthouse specific
        'terrace_area',
        'is_duplex',
    ];

    protected $casts = [
        'covered_area' => 'decimal:2',
        'price' => 'decimal:2',
        'price_per_sqft' => 'decimal:2',
        'terrace_area' => 'decimal:2',
        'has_parking' => 'boolean',
        'has_mezzanine' => 'boolean',
        'has_washroom' => 'boolean',
        'is_duplex' => 'boolean',
    ];

    public static $unitTypes = ['Flat', 'Shop', 'Office', 'Penthouse'];
    public static $statuses = ['Available', 'Booked', 'Sold', 'Reserved'];
    public static $facings = ['North', 'South', 'East', 'West', 'N-E', 'N-W', 'S-E', 'S-W'];
    public static $floorPositions = ['Front', 'Back', 'Corner', 'Center'];

    /**
     * Get the floor that owns the unit.
     */
    public function floor()
    {
        return $this->belongsTo(ReFloor::class, 're_floor_id');
    }

    /**
     * Get the project through floor.
     */
    public function project()
    {
        return $this->hasOneThrough(ReProject::class, ReFloor::class, 'id', 'id', 're_floor_id', 're_project_id');
    }

    /**
     * Get the booking for this unit.
     */
    public function booking()
    {
        return $this->hasOne(ReBooking::class, 're_unit_id');
    }

    /**
     * Get the contract for this unit.
     */
    public function contract()
    {
        // return $this->hasOne(Contract::class, 'unit_id')->where('status', 'accept');
        return $this->hasOne(Contract::class, 'unit_id');
    }
}

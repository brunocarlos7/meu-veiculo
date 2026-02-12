<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'date',
        'station',
        'fuel_type',
        'price_per_liter',
        'liters',
        'total_value',
        'odometer',
        'is_full_tank',
    ];

    protected $casts = [
        'date' => 'date',
        'is_full_tank' => 'boolean',
        'price_per_liter' => 'decimal:2',
        'liters' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MileageEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'date',
        'odometer',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'odometer' => 'integer',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

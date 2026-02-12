<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'year',
        'plate',
        'fuel_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fuelEntries()
    {
        return $this->hasMany(FuelEntry::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'location_type',
        'address',
        'city',
        'state_id',
        'lga_id',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function localGovernment()
    {
        return $this->belongsTo(LocalGovernment::class, 'lga_id');
    }

    public function getFullAddressAttribute()
    {
        return $this->address . ', ' . $this->city . ', ' . 
               $this->localGovernment->name . ', ' . $this->state->name;
    }

    public function scopeOrigin($query)
    {
        return $query->where('location_type', 'origin');
    }

    public function scopeResidence($query)
    {
        return $query->where('location_type', 'residence');
    }

    public function scopeBirth($query)
    {
        return $query->where('location_type', 'birth');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
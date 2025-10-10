<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverReferee extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'name',
        'nin',
        'address',
        'state_id',
        'lga_id',
        'city',
        'phone',
        'verification_status',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
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

    public function getIsVerifiedAttribute()
    {
        return $this->verification_status === 'verified';
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }
}
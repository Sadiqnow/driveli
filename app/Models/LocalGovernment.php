<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalGovernment extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function driverLocations()
    {
        return $this->hasMany(DriverLocation::class, 'lga_id');
    }

    public function driverReferees()
    {
        return $this->hasMany(DriverReferee::class, 'lga_id');
    }

    public function getFullNameAttribute()
    {
        return $this->name . ', ' . $this->state->name;
    }
}
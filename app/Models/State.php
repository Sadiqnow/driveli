<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function localGovernments()
    {
        return $this->hasMany(LocalGovernment::class);
    }

    public function driverLocations()
    {
        return $this->hasMany(DriverLocation::class);
    }

    public function driverReferees()
    {
        return $this->hasMany(DriverReferee::class);
    }
}
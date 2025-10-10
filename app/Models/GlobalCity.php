<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'type',
        'latitude',
        'longitude',
        'is_major_city',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_major_city' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(GlobalState::class, 'state_id');
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class, 'city_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMajorCities($query)
    {
        return $query->where('is_major_city', true);
    }

    public function scopeByState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    // Methods
    public function getFullNameAttribute()
    {
        return $this->name . ', ' . $this->state->name . ', ' . $this->country->name;
    }

    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }
}
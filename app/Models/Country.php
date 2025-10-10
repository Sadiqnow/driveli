<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code_2',
        'iso_code_3',
        'phone_code',
        'currency_code',
        'currency_symbol',
        'timezone',
        'common_languages',
        'continent',
        'is_active',
        'is_supported_market',
        'priority_order',
    ];

    protected $casts = [
        'common_languages' => 'array',
        'is_active' => 'boolean',
        'is_supported_market' => 'boolean',
        'priority_order' => 'integer',
    ];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function states()
    {
        return $this->hasMany(GlobalState::class);
    }

    public function cities()
    {
        return $this->hasMany(GlobalCity::class);
    }

    public function categoryRequirements()
    {
        return $this->hasMany(DriverCategoryRequirement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSupportedMarkets($query)
    {
        return $query->where('is_supported_market', true);
    }

    public function scopeByContinent($query, $continent)
    {
        return $query->where('continent', $continent);
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority_order')->orderBy('name');
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    public function getFullPhoneCodeAttribute()
    {
        return $this->phone_code;
    }

    // Methods
    public function isSupported()
    {
        return $this->is_supported_market;
    }

    public function getPrimaryLanguage()
    {
        return $this->common_languages[0] ?? 'en';
    }
}
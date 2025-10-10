<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function driverBankingDetails()
    {
        return $this->hasMany(DriverBankingDetail::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverBankingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'account_number',
        'bank_id',
        'account_name',
        'is_primary',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function getFormattedAccountNumberAttribute()
    {
        return chunk_split($this->account_number, 3, ' ');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
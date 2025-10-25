<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_id',
        'registration_number',
        'make',
        'model',
        'year',
        'color',
        'vin',
        'engine_number',
        'chassis_number',
        'vehicle_type',
        'seating_capacity',
        'purchase_price',
        'purchase_date',
        'current_value',
        'insurance_expiry',
        'insurance_provider',
        'road_worthiness_expiry',
        'mileage',
        'status',
        'notes',
        'features',
    ];

    protected $casts = [
        'year' => 'integer',
        'seating_capacity' => 'integer',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'mileage' => 'integer',
        'purchase_date' => 'date',
        'insurance_expiry' => 'date',
        'road_worthiness_expiry' => 'date',
        'features' => 'array',
    ];

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function getFullNameAttribute(): string
    {
        return $this->year . ' ' . $this->make . ' ' . $this->model;
    }

    public function insuranceExpired(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->isPast();
    }

    public function roadWorthinessExpired(): bool
    {
        return $this->road_worthiness_expiry && $this->road_worthiness_expiry->isPast();
    }
}

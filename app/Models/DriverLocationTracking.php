<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLocationTracking extends Model
{
    use HasFactory;

    protected $table = 'driver_locations';

    protected $fillable = [
        'driver_id',
        'latitude',
        'longitude',
        'accuracy',
        'device_info',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'metadata' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeWithinBounds($query, $lat1, $lon1, $lat2, $lon2)
    {
        return $query->whereBetween('latitude', [$lat1, $lat2])
                    ->whereBetween('longitude', [$lon1, $lon2]);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function getCoordinatesAttribute()
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }

    public function isSuspicious()
    {
        // Check for suspicious patterns (implement based on business rules)
        // This could include sudden location jumps, unusual timing, etc.
        return false; // Placeholder
    }
}

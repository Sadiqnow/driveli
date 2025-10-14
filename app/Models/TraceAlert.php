<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'alert_type',
        'severity',
        'last_known_location',
        'alert_data',
        'status',
        'triggered_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'last_known_location' => 'array',
        'alert_data' => 'array',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'resolved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('triggered_at', '>=', now()->subDays($days));
    }

    // Methods
    public function resolve(int $adminId, string $notes = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $adminId,
            'resolution_notes' => $notes,
        ]);
    }

    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            default => 'gray',
        };
    }

    public function getAlertTypeDescriptionAttribute()
    {
        return match($this->alert_type) {
            'app_uninstalled' => 'App Uninstalled',
            'ping_missed' => 'Ping Timeout',
            'suspicious_activity' => 'Suspicious Activity',
            'device_changed' => 'Device Changed',
            default => ucwords(str_replace('_', ' ', $this->alert_type)),
        };
    }
}

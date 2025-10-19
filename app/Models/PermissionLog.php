<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionLog extends Model
{
    use HasFactory;

    protected $table = 'permission_logs';

    protected $fillable = [
        'user_id',
        'permission_name',
        'result',
        'ip_address',
        'user_agent',
        'route_name',
        'method',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the permission log
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    /**
     * Scope for granted permissions
     */
    public function scopeGranted($query)
    {
        return $query->where('result', 'granted');
    }

    /**
     * Scope for denied permissions
     */
    public function scopeDenied($query)
    {
        return $query->where('result', 'denied');
    }

    /**
     * Scope for specific permission
     */
    public function scopeForPermission($query, string $permission)
    {
        return $query->where('permission_name', $permission);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}

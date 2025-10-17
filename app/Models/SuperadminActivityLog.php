<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperadminActivityLog extends Model
{
    use HasFactory;

    protected $table = 'superadmin_activity_logs';

    protected $fillable = [
        'superadmin_id',
        'action',
        'resource_type',
        'resource_id',
        'resource_name',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with the Superadmin (AdminUser)
     */
    public function superadmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'superadmin_id');
    }

    /**
     * Get the resource URL for the logged action
     */
    public function getResourceUrlAttribute(): ?string
    {
        if (!$this->resource_type || !$this->resource_id) {
            return null;
        }

        return match($this->resource_type) {
            'driver' => route('admin.superadmin.drivers.show', $this->resource_id),
            'user' => route('admin.superadmin.users.show', $this->resource_id),
            'company' => route('admin.superadmin.companies.show', $this->resource_id),
            default => null,
        };
    }

    /**
     * Get formatted action description
     */
    public function getFormattedActionAttribute(): string
    {
        return match($this->action) {
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'approve' => 'Approved',
            'reject' => 'Rejected',
            'flag' => 'Flagged',
            'restore' => 'Restored',
            'bulk_operation' => 'Bulk Operation',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get action badge class
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match($this->action) {
            'create' => 'badge-success',
            'update' => 'badge-info',
            'delete' => 'badge-danger',
            'approve' => 'badge-success',
            'reject' => 'badge-danger',
            'flag' => 'badge-warning',
            'restore' => 'badge-info',
            'bulk_operation' => 'badge-primary',
            default => 'badge-secondary',
        };
    }

    /**
     * Scope for filtering by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by resource type
     */
    public function scopeByResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope for filtering by Superadmin
     */
    public function scopeBySuperadmin($query, int $superadminId)
    {
        return $query->where('superadmin_id', $superadminId);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for today's activities
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get activity summary for dashboard
     */
    public static function getActivitySummary(int $superadminId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_activities' => self::where('superadmin_id', $superadminId)
                ->where('created_at', '>=', $startDate)
                ->count(),

            'actions_breakdown' => self::where('superadmin_id', $superadminId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),

            'resources_breakdown' => self::where('superadmin_id', $superadminId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('resource_type, COUNT(*) as count')
                ->groupBy('resource_type')
                ->pluck('count', 'resource_type')
                ->toArray(),

            'daily_activities' => self::where('superadmin_id', $superadminId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }
}

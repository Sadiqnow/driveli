<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'user_activities';

    protected $fillable = [
        'user_type',
        'user_id',
        'action',
        'description',
        'resource_type',
        'resource_id',
        'resource_name',
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

    public function user(): BelongsTo
    {
        return $this->morphTo();
    }

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

    public static function log(
        string $action,
        string $description,
        Model $resource = null,
        array $oldValues = null,
        array $newValues = null,
        array $metadata = null,
        $user = null
    ): UserActivity {
        $user = $user ?? auth('admin')->user();

        $logData = [
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($user) {
            $logData['user_type'] = get_class($user);
            $logData['user_id'] = $user->getKey();
        }

        if ($resource) {
            $logData['resource_type'] = self::getResourceType($resource);
            $logData['resource_id'] = $resource->getKey();
            $logData['resource_name'] = self::getResourceName($resource);
        }

        if ($oldValues) {
            $logData['old_values'] = $oldValues;
        }

        if ($newValues) {
            $logData['new_values'] = $newValues;
        }

        if ($metadata) {
            $logData['metadata'] = $metadata;
        }

        return self::create($logData);
    }

    private static function getResourceType(Model $resource): string
    {
        return match(get_class($resource)) {
            'App\Models\Drivers' => 'driver',
            'App\Models\AdminUser' => 'admin_user',
            'App\Models\Company' => 'company',
            'App\Models\CompanyRequest' => 'company_request',
            default => strtolower(class_basename($resource)),
        };
    }

    private static function getResourceName(Model $resource): ?string
    {
        return match(get_class($resource)) {
            'App\Models\Drivers' => $resource->full_name ?? $resource->driver_id,
            'App\Models\AdminUser' => $resource->name ?? $resource->email,
            'App\Models\Company' => $resource->name ?? $resource->company_id,
            'App\Models\CompanyRequest' => $resource->request_id ?? "Request #{$resource->id}",
            default => $resource->name ?? $resource->title ?? "ID: {$resource->getKey()}",
        };
    }
}

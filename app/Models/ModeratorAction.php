<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeratorAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'moderator_id',
        'action_type',
        'resource_type',
        'resource_id',
        'action_data',
        'reason',
        'notes',
        'metadata',
        'effective_from',
        'effective_until',
        'is_reversible',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'action_data' => 'array',
        'metadata' => 'array',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'is_reversible' => 'boolean',
        'reversed_at' => 'datetime',
    ];

    // Relationships
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'moderator_id');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reversed_by');
    }

    // Scopes
    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByResourceType($query, $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('effective_until')
              ->orWhere('effective_until', '>', now());
        })->whereNull('reversed_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('effective_until', '<=', now());
    }

    public function scopeReversible($query)
    {
        return $query->where('is_reversible', true);
    }

    public function scopeReversed($query)
    {
        return $query->whereNotNull('reversed_at');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isActive(): bool
    {
        if ($this->reversed_at) {
            return false;
        }

        if (!$this->effective_until) {
            return true;
        }

        return $this->effective_until->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->effective_until && $this->effective_until->isPast();
    }

    public function isReversed(): bool
    {
        return !is_null($this->reversed_at);
    }

    public function canBeReversed(): bool
    {
        return $this->is_reversible && !$this->isReversed() && $this->isActive();
    }

    public function getActionTypeDisplayName(): string
    {
        return match($this->action_type) {
            'approve' => 'Approved',
            'reject' => 'Rejected',
            'suspend' => 'Suspended',
            'reinstate' => 'Reinstated',
            'flag' => 'Flagged',
            'review' => 'Under Review',
            default => ucfirst(str_replace('_', ' ', $this->action_type))
        };
    }

    public function getResourceTypeDisplayName(): string
    {
        return match($this->resource_type) {
            'driver' => 'Driver Profile',
            'document' => 'Document',
            'verification' => 'Verification',
            'profile' => 'Profile',
            default => ucfirst(str_replace('_', ' ', $this->resource_type))
        };
    }

    public function getStatusBadgeClass(): string
    {
        if ($this->isReversed()) {
            return 'badge-secondary';
        }

        return match($this->action_type) {
            'approve' => 'badge-success',
            'reject' => 'badge-danger',
            'suspend' => 'badge-warning',
            'reinstate' => 'badge-info',
            'flag' => 'badge-warning',
            'review' => 'badge-info',
            default => 'badge-secondary'
        };
    }

    public function reverse(AdminUser $admin, string $reason = null): void
    {
        if (!$this->canBeReversed()) {
            throw new \Exception('This action cannot be reversed.');
        }

        $this->update([
            'reversed_by' => $admin->id,
            'reversed_at' => now(),
            'reversal_reason' => $reason,
        ]);
    }

    public function getEffectiveDuration(): ?string
    {
        if (!$this->effective_from || !$this->effective_until) {
            return null;
        }

        $days = $this->effective_from->diffInDays($this->effective_until);
        return $days . ' days';
    }
}

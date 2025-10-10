<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'driver_id',
        'request_id',
        'position_title',
        'request_type',
        'description',
        'location',
        'requirements',
        'salary_range',
        'status',
        'priority',
        'created_by',
        'approved_by',
        'approved_at',
        'expires_at',
        'queue_position',
        'assigned_to',
        'acceptance_notes',
        'estimated_completion',
        'accepted_at',
        'rejected_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'processing_notes',
        'started_at',
        'completion_notes',
        'completed_at',
        'rating',
        'pause_reason',
        'paused_at',
    ];

    protected $casts = [
        'requirements' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships with optimized queries
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)
                    ->select(['id', 'name', 'email', 'status', 'verified_at'])
                    ->withDefault();
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id')
                    ->select(['id', 'driver_id', 'first_name', 'surname', 'email', 'phone', 'status', 'verification_status'])
                    ->withDefault();
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DriverMatch::class, 'company_request_id')
                    ->select(['id', 'company_request_id', 'driver_id', 'status', 'matched_at'])
                    ->orderBy('matched_at', 'desc');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by')
                    ->select(['id', 'name', 'email']);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'approved_by')
                    ->select(['id', 'name', 'email']);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'assigned_to')
                    ->select(['id', 'name', 'email']);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'cancelled_by')
                    ->select(['id', 'name', 'email']);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'warning',
            'active' => 'success',
            'completed' => 'info',
            'cancelled' => 'danger',
            'expired' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Methods
    public function approve(AdminUser $admin): bool
    {
        return $this->update([
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);
    }

    public function cancel(AdminUser $admin, string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_by' => $admin->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }
}

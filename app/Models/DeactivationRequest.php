<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeactivationRequest extends Model
{
    use HasFactory;

    protected $table = 'deactivation_requests';

    protected $fillable = [
        'user_type',
        'user_id',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function requester(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'approved_by');
    }

    public function user()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    // Methods
    public function approve(AdminUser $admin, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        // Log activity
        ActivityLog::create([
            'user_type' => AdminUser::class,
            'user_id' => $admin->id,
            'action' => 'deactivation_approved',
            'description' => "Deactivation request approved for {$this->user_type} ID: {$this->user_id}",
            'metadata' => [
                'request_id' => $this->id,
                'user_type' => $this->user_type,
                'user_id' => $this->user_id,
                'reason' => $this->reason,
            ],
        ]);

        return true;
    }

    public function reject(AdminUser $admin, $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        // Log activity
        ActivityLog::create([
            'user_type' => AdminUser::class,
            'user_id' => $admin->id,
            'action' => 'deactivation_rejected',
            'description' => "Deactivation request rejected for {$this->user_type} ID: {$this->user_id}",
            'metadata' => [
                'request_id' => $this->id,
                'user_type' => $this->user_type,
                'user_id' => $this->user_id,
                'reason' => $this->reason,
            ],
        ]);

        return true;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverVerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'action',
        'status',
        'verification_data',
        'result_data',
        'confidence_score',
        'notes',
        'performed_by',
        'performed_at',
    ];

    protected $casts = [
        'verification_data' => 'array',
        'result_data' => 'array',
        'confidence_score' => 'decimal:2',
        'performed_at' => 'datetime',
    ];

    // Relationships
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'performed_by');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getActionDisplayName(): string
    {
        return match($this->action) {
            'ocr_verification' => 'OCR Verification',
            'facial_verification' => 'Facial Verification',
            'document_verification' => 'Document Verification',
            'manual_review' => 'Manual Review',
            default => ucfirst(str_replace('_', ' ', $this->action))
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'completed' => 'badge-success',
            'failed' => 'badge-danger',
            'pending' => 'badge-warning',
            'started' => 'badge-info',
            default => 'badge-secondary'
        };
    }
}

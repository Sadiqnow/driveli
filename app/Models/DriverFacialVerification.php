<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverFacialVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'session_id',
        'status',
        'facial_data',
        'reference_image_path',
        'captured_image_path',
        'similarity_score',
        'confidence_score',
        'is_match',
        'verification_metadata',
        'failure_reason',
        'started_at',
        'completed_at',
        'expires_at',
        'verified_by',
    ];

    protected $casts = [
        'facial_data' => 'array',
        'similarity_score' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'is_match' => 'boolean',
        'verification_metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeMatched($query)
    {
        return $query->where('is_match', true);
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

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isSuccessful(): bool
    {
        return $this->isCompleted() && $this->is_match;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'completed' => $this->is_match ? 'badge-success' : 'badge-danger',
            'failed' => 'badge-danger',
            'pending' => 'badge-warning',
            'in_progress' => 'badge-info',
            'expired' => 'badge-dark',
            default => 'badge-secondary'
        };
    }

    public function getSimilarityPercentage(): float
    {
        return $this->similarity_score ? round($this->similarity_score * 100, 2) : 0;
    }

    public function getConfidencePercentage(): float
    {
        return $this->confidence_score ? round($this->confidence_score * 100, 2) : 0;
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'failure_reason' => 'Session expired',
        ]);
    }

    public function completeVerification(bool $isMatch, float $similarityScore, float $confidenceScore, array $metadata = []): void
    {
        $this->update([
            'status' => 'completed',
            'is_match' => $isMatch,
            'similarity_score' => $similarityScore,
            'confidence_score' => $confidenceScore,
            'verification_metadata' => $metadata,
            'completed_at' => now(),
        ]);
    }
}

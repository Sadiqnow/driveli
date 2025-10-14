<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'verifiable_id',
        'verifiable_type',
        'type',
        'verification_source',
        'status',
        'score',
        'api_response',
        'response_timestamp',
        'response_time_ms',
        'external_reference_id',
        'expires_at',
        'notes',
        'requires_reverification',
        'last_reverification_check',
    ];

    protected $casts = [
        'api_response' => 'array',
        'response_timestamp' => 'datetime',
        'expires_at' => 'datetime',
        'last_reverification_check' => 'datetime',
        'requires_reverification' => 'boolean',
        'score' => 'integer',
        'response_time_ms' => 'integer',
    ];

    /**
     * Get the verifiable model (driver, etc.) that owns the verification.
     */
    public function verifiable()
    {
        return $this->morphTo();
    }

    /**
     * Get the driver that owns the verification.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class, 'verifiable_id');
    }

    /**
     * Scope a query to only include completed verifications.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed verifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include expired verifications.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope a query to only include valid (non-expired) verifications.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to only include verifications requiring reverification.
     */
    public function scopeRequiresReverification($query)
    {
        return $query->where('requires_reverification', true);
    }

    /**
     * Check if the verification is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the verification is valid.
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Get the days until expiration.
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Mark this verification as requiring reverification.
     */
    public function markForReverification(): bool
    {
        return $this->update([
            'requires_reverification' => true,
            'last_reverification_check' => now(),
        ]);
    }
}

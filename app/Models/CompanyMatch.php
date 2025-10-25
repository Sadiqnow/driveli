<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_request_id',
        'driver_id',
        'match_score',
        'matching_criteria',
        'status',
        'rejection_reason',
        'accepted_at',
        'completed_at',
        'agreed_rate',
        'notes',
        'matched_by',
    ];

    protected $casts = [
        'match_score' => 'decimal:2',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'agreed_rate' => 'decimal:2',
        'matching_criteria' => 'array',
    ];

    public function companyRequest(): BelongsTo
    {
        return $this->belongsTo(CompanyRequest::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function matchedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'matched_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }
}

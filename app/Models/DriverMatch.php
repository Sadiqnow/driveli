<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverMatch extends Model
{
    use HasFactory;

    protected $table = 'driver_matches';

    protected $fillable = [
        'match_id',
        'company_request_id',
        'driver_id',
        'status',
        'commission_rate',
        'commission_amount',
        'matched_at',
        'accepted_at',
        'declined_at',
        'completed_at',
        'cancelled_at',
        'matched_by_admin',
        'auto_matched',
        'driver_rating',
        'company_rating',
        'driver_feedback',
        'company_feedback',
        'notes',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'matched_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'matched_by_admin' => 'boolean',
        'auto_matched' => 'boolean',
        'driver_rating' => 'decimal:1',
        'company_rating' => 'decimal:1',
    ];

    // Relationships
    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id')->withDefault();
    }

    public function companyRequest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompanyRequest::class, 'company_request_id')->withDefault();
    }

    public function commission(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Commission::class, 'driver_match_id');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Company::class,
            CompanyRequest::class,
            'id', // Foreign key on CompanyRequest table
            'id', // Foreign key on Company table
            'company_request_id', // Local key on DriverMatch table
            'company_id' // Local key on CompanyRequest table
        );
    }

    public function matchedByAdmin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'matched_by_admin');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'accepted' => 'info',
            'completed' => 'success',
            'declined' => 'danger',
            'cancelled' => 'secondary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getFormattedCommissionAttribute(): string
    {
        return number_format($this->commission_amount, 2);
    }

    // Methods
    public function accept(): bool
    {
        return $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function decline(): bool
    {
        return $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }

    public function complete(): bool
    {
        $updated = $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($updated && $this->commission_amount > 0) {
            // Create commission record
            Commission::create([
                'driver_match_id' => $this->id,
                'driver_id' => $this->driver_id,
                'amount' => $this->commission_amount,
                'rate' => $this->commission_rate,
                'status' => 'pending',
            ]);
        }

        return $updated;
    }

    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function calculateCommission(float $totalAmount = null): float
    {
        if (!$totalAmount && $this->companyRequest) {
            // Extract amount from salary range if available
            $salaryRange = $this->companyRequest->salary_range;
            if (is_string($salaryRange) && preg_match('/\d+/', $salaryRange, $matches)) {
                $totalAmount = (float) $matches[0];
            }
        }

        $rate = $this->commission_rate ?? config('drivelink.default_commission_rate', 15);
        return $totalAmount ? ($totalAmount * $rate / 100) : 0;
    }
}
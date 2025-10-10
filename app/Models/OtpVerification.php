<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'verification_type',
        'otp_code',
        'expires_at',
        'verified_at',
        'attempts',
        'last_attempt_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Relationship with DriverNormalized
     */
    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is verified
     */
    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    /**
     * Check if can resend OTP (cooldown period)
     */
    public function canResend()
    {
        if (!$this->last_attempt_at) {
            return true;
        }

        return $this->last_attempt_at->addMinutes(1)->isPast();
    }

    /**
     * Get remaining cooldown time in seconds
     */
    public function getCooldownRemaining()
    {
        if (!$this->last_attempt_at) {
            return 0;
        }

        $cooldownEnd = $this->last_attempt_at->addMinutes(1);
        $remaining = now()->diffInSeconds($cooldownEnd, false);

        return max(0, $remaining);
    }

    /**
     * Increment attempts counter
     */
    public function incrementAttempts()
    {
        $this->increment('attempts');
        $this->update(['last_attempt_at' => now()]);
    }

    /**
     * Scope for active (non-expired, non-verified) OTPs
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
                    ->whereNull('verified_at');
    }

    /**
     * Scope for verified OTPs
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }
}

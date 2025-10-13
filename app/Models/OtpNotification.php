<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpNotification extends Model
{
    use HasFactory;

    protected $table = 'otp_notifications';

    protected $fillable = [
        'user_type',
        'user_id',
        'otp_code',
        'type',
        'status',
        'expires_at',
        'sent_at',
        'verified_at',
        'attempts',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForUser($query, $userId, $userType)
    {
        return $query->where('user_id', $userId)->where('user_type', $userType);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isVerified()
    {
        return $this->status === 'verified';
    }

    public function canAttempt()
    {
        return $this->attempts < 3 && !$this->isExpired();
    }

    public function verify($otpCode, $ipAddress = null, $userAgent = null)
    {
        if ($this->isExpired()) {
            $this->update(['status' => 'expired']);
            return false;
        }

        if ($this->attempts >= 3) {
            $this->update(['status' => 'failed']);
            return false;
        }

        $this->increment('attempts');

        if ($this->otp_code === $otpCode) {
            $this->update([
                'status' => 'verified',
                'verified_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            // Log activity
            ActivityLog::create([
                'user_type' => $this->user_type,
                'user_id' => $this->user_id,
                'action' => 'otp_verified',
                'description' => "OTP verified for {$this->type}",
                'metadata' => [
                    'otp_id' => $this->id,
                    'type' => $this->type,
                    'ip_address' => $ipAddress,
                ],
            ]);

            return true;
        }

        return false;
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }
}

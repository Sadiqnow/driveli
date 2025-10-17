<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        // Core Blackbox fields only
        'driver_id', 'first_name', 'middle_name', 'surname', 'email', 'phone', 'phone_2',
        'password', 'remember_token', 'email_verified_at', 'phone_verified_at',
        'phone_verification_status', 'email_verification_status',
        'status', 'verification_status', 'is_active', 'is_available',
        'verified_at', 'verified_by', 'verification_notes',
        'kyc_status', 'kyc_step', 'kyc_retry_count', 'kyc_rejection_reason',
        'kyc_submission_ip', 'kyc_user_agent', 'kyc_last_activity_at',
        'profile_completion_percentage', 'registration_source', 'registration_ip'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'kyc_last_activity_at' => 'datetime',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'profile_completion_percentage' => 'decimal:2',
        'kyc_retry_count' => 'integer',
        'kyc_step' => 'integer',
    ];

    // ===========================
    // RELATIONSHIPS
    // ===========================

    // Transactional relationships - hasOne for single records
    public function personalInfo()
    {
        return $this->hasOne(DriverNextOfKin::class, 'driver_id');
    }

    public function performance()
    {
        return $this->hasOne(DriverPerformance::class, 'driver_id');
    }

    public function primaryBankingDetail()
    {
        return $this->hasOne(DriverBankingDetail::class, 'driver_id')->where('is_primary', true);
    }

    public function bankingDetails()
    {
        return $this->hasMany(DriverBankingDetail::class, 'driver_id');
    }

    public function documents()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id');
    }

    public function profileDocument()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id')->where('document_type', 'profile');
    }

    public function matches()
    {
        return $this->hasMany(DriverMatch::class, 'driver_id');
    }

    public function categoryRequirements()
    {
        return $this->hasMany(DriverCategoryRequirement::class, 'driver_id');
    }

    // Reverse relationships
    public function verifiedBy()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    public function verifiedByAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    // ===========================
    // ACCESSORS & MUTATORS
    // ===========================

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->surname);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->first_name;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->personalInfo?->date_of_birth?->age;
    }

    public function getDateOfBirthAttribute(): ?string
    {
        return $this->personalInfo?->date_of_birth;
    }

    public function getGenderAttribute(): ?string
    {
        return $this->personalInfo?->gender;
    }

    public function getProfilePictureAttribute(): ?string
    {
        return $this->profileDocument?->profile_picture;
    }

    public function getLicenseNumberAttribute(): ?string
    {
        return $this->performance?->license_number;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isKycCompleted(): bool
    {
        return $this->kyc_status === 'completed';
    }

    public function getProfileCompletionPercentage(): float
    {
        // Calculate based on completed fields in transactional tables
        $completed = 0;
        $total = 10; // Total required fields

        if ($this->first_name && $this->surname) $completed++;
        if ($this->email) $completed++;
        if ($this->phone) $completed++;
        if ($this->personalInfo?->date_of_birth) $completed++;
        if ($this->personalInfo?->gender) $completed++;
        if ($this->performance?->license_number) $completed++;
        if ($this->primaryBankingDetail) $completed++;
        if ($this->profileDocument?->profile_picture) $completed++;
        if ($this->documents()->where('verification_status', 'approved')->exists()) $completed++;
        if ($this->isKycCompleted()) $completed++;

        return round(($completed / $total) * 100, 2);
    }

    // ===========================
    // BUSINESS LOGIC METHODS
    // ===========================

    public function updateProfileCompletion()
    {
        $this->update(['profile_completion_percentage' => $this->getProfileCompletionPercentage()]);
    }

    public function completeKycStep(int $step)
    {
        $field = "kyc_step_{$step}_completed_at";
        $this->update([
            'kyc_step' => $step,
            $field => now(),
        ]);

        if ($step === 3) {
            $this->update([
                'kyc_status' => 'completed',
                'kyc_completed_at' => now(),
            ]);
        }
    }

    public function submitKyc()
    {
        $this->update([
            'kyc_status' => 'submitted',
            'kyc_submitted_at' => now(),
        ]);
    }

    public function approveKyc($adminId)
    {
        $this->update([
            'kyc_status' => 'approved',
            'kyc_reviewed_at' => now(),
            'kyc_reviewed_by' => $adminId,
        ]);
    }

    public function rejectKyc($reason, $adminId)
    {
        $this->update([
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => $reason,
            'kyc_reviewed_at' => now(),
            'kyc_reviewed_by' => $adminId,
            'kyc_retry_count' => $this->kyc_retry_count + 1,
        ]);
    }
}

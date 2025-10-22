<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DriverNormalized extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'drivers_normalized';

    protected $fillable = [
        'driver_id',
        'first_name',
        'middle_name',
        'surname',
        'email',
        'phone',
        'phone_2',
        'password',
        'remember_token',
        'email_verified_at',
        'phone_verified_at',
        'phone_verification_status',
        'email_verification_status',
        'date_of_birth',
        'gender',
        'religion',
        'blood_group',
        'height_meters',
        'disability_status',
        'nationality_id',
        'current_employer',
        'years_of_experience',
        'employment_start_date',
        'is_working',
        'previous_company',
        'reason_stopped_working',
        'license_number',
        'license_class',
        'license_issue_date',
        'license_expiry_date',
        'has_vehicle',
        'vehicle_type',
        'vehicle_year',
        'bank_id',
        'account_number',
        'account_name',
        'bvn_number',
        'preferred_work_location',
        'available_for_night_shifts',
        'available_for_weekend_work',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'status',
        'verification_status',
        'is_active',
        'is_available',
        'registered_at',
        'last_active_at',
        'verified_at',
        'verified_by',
        'verification_notes',
        'kyc_status',
        'kyc_step',
        'kyc_step_data',
        'kyc_step_1_completed_at',
        'kyc_step_2_completed_at',
        'kyc_step_3_completed_at',
        'kyc_completed_at',
        'kyc_submitted_at',
        'kyc_reviewed_at',
        'kyc_reviewed_by',
        'kyc_retry_count',
        'kyc_rejection_reason',
        'kyc_submission_ip',
        'kyc_user_agent',
        'kyc_last_activity_at',
        'ocr_verification_status',
        'ocr_verification_notes',
        'nin_verification_data',
        'nin_verified_at',
        'nin_ocr_match_score',
        'frsc_verification_data',
        'frsc_verified_at',
        'frsc_ocr_match_score',
        'profile_picture',
        'passport_photograph',
        'nin_number',
        'profile_completion_percentage',
        'registration_source',
        'registration_ip',
        'created_by_admin_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'employment_start_date' => 'date',
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'registered_at' => 'datetime',
        'last_active_at' => 'datetime',
        'verified_at' => 'datetime',
        'kyc_step_data' => 'array',
        'kyc_step_1_completed_at' => 'datetime',
        'kyc_step_2_completed_at' => 'datetime',
        'kyc_step_3_completed_at' => 'datetime',
        'kyc_completed_at' => 'datetime',
        'kyc_submitted_at' => 'datetime',
        'kyc_reviewed_at' => 'datetime',
        'kyc_last_activity_at' => 'datetime',
        'nin_verification_data' => 'array',
        'nin_verified_at' => 'datetime',
        'frsc_verification_data' => 'array',
        'frsc_verified_at' => 'datetime',
        'nin_ocr_match_score' => 'decimal:2',
        'frsc_ocr_match_score' => 'decimal:2',
        'profile_completion_percentage' => 'decimal:2',
        'height_meters' => 'decimal:2',
        'years_of_experience' => 'integer',
        'vehicle_year' => 'integer',
        'kyc_retry_count' => 'integer',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'is_working' => 'boolean',
        'has_vehicle' => 'boolean',
        'available_for_night_shifts' => 'boolean',
        'available_for_weekend_work' => 'boolean',
    ];

    // Relationships
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    public function kycReviewedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'kyc_reviewed_by');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by_admin_id');
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DriverDocument::class);
    }

    public function bankingDetails(): HasMany
    {
        return $this->hasMany(DriverBankingDetail::class);
    }

    public function nextOfKin(): HasOne
    {
        return $this->hasOne(DriverNextOfKin::class);
    }

    public function performance(): HasOne
    {
        return $this->hasOne(DriverPerformance::class);
    }

    public function companyRequests(): HasMany
    {
        return $this->hasMany(CompanyRequest::class, 'driver_id');
    }

    public function driverMatches(): HasMany
    {
        return $this->hasMany(DriverMatch::class);
    }

    // Helper methods
    public function getFullName(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->surname}");
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_active;
    }

    public function isAvailable(): bool
    {
        return $this->is_available && $this->isActive();
    }

    public function getKycProgress(): array
    {
        $steps = [
            'personal_info' => !empty($this->kyc_step_1_completed_at),
            'documents' => !empty($this->kyc_step_2_completed_at),
            'verification' => !empty($this->kyc_step_3_completed_at),
        ];

        $completedSteps = array_filter($steps);
        $progress = (count($completedSteps) / count($steps)) * 100;

        return [
            'steps' => $steps,
            'completed_count' => count($completedSteps),
            'total_steps' => count($steps),
            'percentage' => round($progress, 2),
        ];
    }

    public function updateProfileCompletion(): void
    {
        $requiredFields = [
            'first_name', 'surname', 'email', 'phone', 'date_of_birth',
            'gender', 'license_number', 'profile_picture', 'nin_number'
        ];

        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        $this->profile_completion_percentage = round(($completedFields / count($requiredFields)) * 100, 2);
        $this->save();
    }

    public function canAcceptRequest(): bool
    {
        return $this->isAvailable() && $this->isVerified();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->active();
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByKycStatus($query, $kycStatus)
    {
        return $query->where('kyc_status', $kycStatus);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('preferred_work_location', $location);
    }
}

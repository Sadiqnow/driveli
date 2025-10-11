<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\DrivelinkHelper;
use App\Traits\HasEncryptedFields;
use App\Services\EncryptionService;
use App\Constants\DrivelinkConstants;

class DriverNormalized extends Drivers
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasEncryptedFields;

    protected $table = 'drivers';

    /**
     * The attributes that should be encrypted.
     *
     * @var array
     */
    protected $encrypted = [
        'nin_number',
        'phone',
        'emergency_contact_phone',
        'account_number',
        'bvn',
    ];

    protected $fillable = [
        'driver_id',
        'nickname',
        'first_name',
        'middle_name',
        'surname',
        'last_name',
        'phone',
        'phone_2',
        'phone_verified_at',
        'phone_verification_status',
        'email',
        'email_verified_at',
        'email_verification_status',
        'password',
        'remember_token',
        'date_of_birth',
        'gender',
        'religion',
        'blood_group',
        'height_meters',
        'disability_status',
        'nationality_id',
        'state_of_origin',
        'lga_of_origin',
        'address_of_origin',
        'profile_picture',
        'profile_photo',
        'nin_number',
        'nin_document',
        'license_number',
        'license_class',
        'license_expiry_date',
        'frsc_document',
        'license_front_image',
        'license_back_image',
        'passport_photograph',
        'additional_documents',
        'current_employer',
        'experience_years',
        'employment_start_date',
        'is_working',
        'previous_workplace',
        'previous_work_id_record',
        'reason_stopped_working',
        'residence_address',
        'residential_address',
        'residence_state_id',
        'residence_lga_id',
        'vehicle_types',
        'work_regions',
        'special_skills',
        'status',
        'verification_status',
        'is_active',
        'registered_at',
        'verification_notes',
        'verified_by',
        'ocr_verification_status',
        'ocr_verification_notes',
        'nin_verification_data',
        'nin_verified_at',
        'nin_ocr_match_score',
        'frsc_verification_data',
        'frsc_verified_at',
        'frsc_ocr_match_score',
        'kyc_step',
        'kyc_status',
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
        'profile_completion_percentage',
        'marital_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'license_issue_date',
        'license_expiry_date',
        'full_address',
        'city',
        'postal_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'driver_license_scan',
        'national_id',
        'passport_photo',
        'document_verification_data',
        'kyc_status',
        'kyc_rejection_reason',
        'bvn_number',
        'created_by_admin_id',
        'kyc_submitted_at',
        'kyc_reviewed_at',
        'kyc_reviewed_by',
        'kyc_retry_count',
        'kyc_submission_ip',
        'kyc_user_agent',
        'kyc_last_activity_at',
        'marital_status',
        'state_id',
        'lga_id',
        'residential_address',
        'emergency_contact_relationship',
        'years_of_experience',
        'previous_company',
        'has_vehicle',
        'vehicle_type',
        'vehicle_year',
        'bank_id',
        'account_number',
        'account_name',
        'bvn',
        'preferred_work_location',
        'available_for_night_shifts',
        'available_for_weekend_work',
        'registration_date',
        'registration_source',
        'registration_ip',
        'drivers_license_photo_path',
        'profile_photo_path',
        'national_id_path',
        'created_by',
        'state',
        'national_id_image',
        'proof_of_address_path',
        'guarantor_letter_path',
        'vehicle_registration_path',
        'insurance_certificate_path',
        'available',
    ];

    protected $guarded = [
        'id',
        'last_active_at',
        'verified_at',
        'rejected_at',
        'rejection_reason',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $attributes = [
        'kyc_status' => 'pending',
        'kyc_step' => 'not_started',
        'kyc_rejection_reason' => null,
        'kyc_retry_count' => 0,
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'license_expiry_date' => 'date',
        'employment_start_date' => 'date',
        'height_meters' => 'decimal:2',
        'experience_years' => 'integer',
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
        'registered_at' => 'datetime',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'nin_verified_at' => 'datetime',
        'frsc_verified_at' => 'datetime',
        'nin_ocr_match_score' => 'decimal:2',
        'frsc_ocr_match_score' => 'decimal:2',
        'nin_verification_data' => 'array',
        'frsc_verification_data' => 'array',
        'additional_documents' => 'array',
        'vehicle_types' => 'array',
        'work_regions' => 'array',
        'kyc_step_data' => 'array',
        'kyc_step_1_completed_at' => 'datetime',
        'kyc_step_2_completed_at' => 'datetime',
        'kyc_step_3_completed_at' => 'datetime',
        'kyc_completed_at' => 'datetime',
        'license_issue_date' => 'date',
        'kyc_submitted_at' => 'datetime',
        'kyc_reviewed_at' => 'datetime',
        'kyc_last_activity_at' => 'datetime',
        'kyc_retry_count' => 'integer',
        'document_verification_data' => 'array',
        'years_of_experience' => 'integer',
        'has_vehicle' => 'boolean',
        'vehicle_year' => 'integer',
        'available_for_night_shifts' => 'boolean',
        'available_for_weekend_work' => 'boolean',
        'available' => 'boolean',
        'is_working' => 'boolean',
        'registration_date' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Password hashing mutator
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            if (preg_match('/^\$2[abxy]\$\d{2}\$.{53}$/', $value) ||
                preg_match('/^\$argon2i?\$.+/', $value)) {
                $this->attributes['password'] = $value;
            } else {
                $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
            }
        }
    }

    // Relationships
    public function nationality()
    {
        return $this->belongsTo(Nationality::class)->select(['id', 'name', 'code']);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by')->select(['id', 'name', 'email']);
    }

    public function locations()
    {
        return $this->hasMany(DriverLocation::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'location_type', 'state_id', 'lga_id', 'address', 'is_primary'])
            ->with(['state:id,name', 'lga:id,name']);
    }

    public function originLocation()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'state_id', 'lga_id', 'address'])
            ->where('location_type', 'origin')
            ->where('is_primary', true)
            ->with(['state:id,name', 'lga:id,name']);
    }

    public function residenceLocation()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'state_id', 'lga_id', 'address'])
            ->where('location_type', 'residence')
            ->where('is_primary', true)
            ->with(['state:id,name', 'lga:id,name']);
    }

    public function residenceState()
    {
        return $this->belongsTo(State::class, 'residence_state_id');
    }

    public function residenceLga()
    {
        return $this->belongsTo(LocalGovernment::class, 'residence_lga_id');
    }

    public function originState()
    {
        return $this->belongsTo(State::class, 'state_of_origin');
    }

    public function originLga()
    {
        return $this->belongsTo(LocalGovernment::class, 'lga_of_origin');
    }

    public function birthLocation()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'state_id', 'lga_id', 'address'])
            ->where('location_type', 'birth')
            ->where('is_primary', true)
            ->with(['state:id,name', 'lga:id,name']);
    }

    public function documents()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id', 'id');
    }

    public function ninDocument()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id', 'id')
            ->where('document_type', 'nin');
    }

    public function licenseFrontDocument()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id', 'id')
            ->where('document_type', 'license_front');
    }

    public function licenseBackDocument()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id', 'id')
            ->where('document_type', 'license_back');
    }

    public function employmentHistory()
    {
        return $this->hasMany(DriverEmploymentHistory::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'company_name', 'job_title', 'start_date', 'end_date', 'is_current'])
            ->orderBy('start_date', 'desc');
    }

    public function currentEmployment()
    {
        return $this->hasOne(DriverEmploymentHistory::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'company_name', 'job_title', 'start_date'])
            ->whereNull('end_date')
            ->where('is_current', true);
    }

    public function nextOfKin()
    {
        return $this->hasMany(DriverNextOfKin::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'name', 'relationship', 'phone', 'is_primary']);
    }

    public function primaryNextOfKin()
    {
        return $this->hasOne(DriverNextOfKin::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'name', 'relationship', 'phone', 'address'])
            ->where('is_primary', true);
    }

    public function bankingDetails()
    {
        return $this->hasMany(DriverBankingDetail::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'bank_id', 'account_number', 'account_name', 'is_primary', 'is_verified'])
            ->with(['bank:id,name,code']);
    }

    public function primaryBankingDetail()
    {
        return $this->hasOne(DriverBankingDetail::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'bank_id', 'account_number', 'account_name', 'is_verified'])
            ->where('is_primary', true)
            ->with(['bank:id,name,code']);
    }

    public function referees()
    {
        return $this->hasMany(DriverReferee::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'name', 'relationship', 'phone', 'organization']);
    }

    public function performance()
    {
        return $this->hasOne(DriverPerformance::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'total_jobs_completed', 'average_rating', 'total_earnings']);
    }

    public function preferences()
    {
        return $this->hasOne(DriverPreference::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'preferred_work_areas', 'vehicle_type_preference', 'work_schedule_preference']);
    }

    public function guarantors()
    {
        return $this->hasMany(Guarantor::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'first_name', 'last_name', 'relationship', 'phone', 'address']);
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function lga()
    {
        return $this->belongsTo(LocalGovernment::class, 'lga_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function driverMatches()
    {
        return $this->hasMany(DriverMatch::class, 'driver_id', 'id');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $name = trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->surname);
        return $name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->nickname ?: $this->first_name;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getIsVerifiedAttribute()
    {
        return $this->verification_status === 'verified';
    }

    public function getStatusBadgeAttribute()
    {
        return DrivelinkHelper::getStatusBadge($this->status);
    }

    public function getVerificationBadgeAttribute()
    {
        return DrivelinkHelper::getVerificationBadge($this->verification_status);
    }

    public function getTotalRatingAttribute()
    {
        return $this->performance?->average_rating ?? 0;
    }

    public function getTotalJobsAttribute()
    {
        return $this->performance?->total_jobs_completed ?? 0;
    }

    public function getTotalEarningsAttribute()
    {
        return $this->performance?->total_earnings ?? 0;
    }

    // Encryption methods
    public function setAttribute($key, $value)
    {
        if (app()->bound(EncryptionService::class)) {
            $encryptionService = app(EncryptionService::class);
            if ($encryptionService->isSensitiveField($key) && !empty($value)) {
                $value = $encryptionService->encryptField($value, $key);
            }
        }
        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (app()->bound(EncryptionService::class)) {
            $encryptionService = app(EncryptionService::class);
            if ($encryptionService->isSensitiveField($key) && !empty($value)) {
                return $encryptionService->decryptField($value, $key);
            }
        }

        return $value;
    }

    public function getMaskedAttribute(string $field): string
    {
        if (app()->bound(EncryptionService::class)) {
            $encryptionService = app(EncryptionService::class);
            $value = $this->getAttribute($field);
            return $encryptionService->maskSensitiveData($value, $field);
        }

        return $this->getAttribute($field);
    }

    public function getVerificationScore(): int
    {
        $score = 0;

        $score += ($this->profile_completion_percentage ?? 0) * 0.4;

        $documentsScore = $this->getDocumentsCompletionScore();
        $score += $documentsScore * 0.35;

        $kycScore = $this->getKycProgressPercentage();
        $score += $kycScore * 0.25;

        return (int) round($score);
    }

    private function getDocumentsCompletionScore(): int
    {
        $requiredDocs = [
            DrivelinkConstants::DOC_TYPE_NIN,
            DrivelinkConstants::DOC_TYPE_LICENSE_FRONT,
            DrivelinkConstants::DOC_TYPE_LICENSE_BACK,
            DrivelinkConstants::DOC_TYPE_PASSPORT_PHOTO,
            DrivelinkConstants::DOC_TYPE_PROFILE_PICTURE
        ];

        $uploadedCount = 0;
        foreach ($requiredDocs as $doc) {
            if (!empty($this->$doc)) {
                $uploadedCount++;
            }
        }

        return (int) (($uploadedCount / count($requiredDocs)) * 100);
    }

    // KYC Methods
    public function isKycStepCompleted(int $step): bool
    {
        switch ($step) {
            case 1:
                return !is_null($this->kyc_step_1_completed_at);
            case 2:
                return !is_null($this->kyc_step_2_completed_at);
            case 3:
                return !is_null($this->kyc_step_3_completed_at);
            default:
                return false;
        }
    }

    public function getCurrentKycStep(): string
    {
        if ($this->kyc_step === 3 && $this->kyc_status === 'completed') {
            return 'completed';
        }

        switch ($this->kyc_step) {
            case 1:
                return 'step_1';
            case 2:
                return 'step_2';
            case 3:
                return 'step_3';
            default:
                return 'not_started';
        }
    }

    public function getKycProgressPercentage(): int
    {
        if ($this->kyc_status === 'completed') {
            return 100;
        }

        $completed = 0;
        if ($this->isKycStepCompleted(1)) $completed++;
        if ($this->isKycStepCompleted(2)) $completed++;
        if ($this->isKycStepCompleted(3)) $completed++;

        return round(($completed / 3) * 100);
    }

    public function canPerformKyc(): bool
    {
        if ($this->kyc_status === 'completed') {
            return false;
        }

        if ($this->kyc_status === 'rejected') {
            return true;
        }

        return in_array($this->kyc_status, ['pending', 'in_progress']);
    }

    public function resetKyc(string $reason = null): void
    {
        $this->update([
            'kyc_step' => 1,
            'kyc_status' => 'pending',
            'kyc_step_1_completed_at' => null,
            'kyc_step_2_completed_at' => null,
            'kyc_step_3_completed_at' => null,
            'kyc_completed_at' => null,
            'kyc_submitted_at' => null,
            'kyc_reviewed_at' => null,
            'kyc_reviewed_by' => null,
            'verification_status' => 'pending',
            'verification_notes' => $reason ? 'KYC reset: ' . $reason : 'KYC process reset for retry',
            'kyc_last_activity_at' => now(),
        ]);
    }

    public function hasCompletedKyc(): bool
    {
        return $this->kyc_status === 'completed' &&
               !is_null($this->kyc_completed_at) &&
               !is_null($this->kyc_step_1_completed_at) &&
               !is_null($this->kyc_step_2_completed_at) &&
               !is_null($this->kyc_step_3_completed_at);
    }

    public function getNextKycStep(): ?string
    {
        $currentStep = $this->getCurrentKycStep();

        return match($currentStep) {
            'not_started' => 'step_1',
            'step_1' => 'step_2',
            'step_2' => 'step_3',
            'step_3' => 'completed',
            'completed' => null,
            default => 'step_1'
        };
    }

    public function getKycStatusBadge(): array
    {
        return match($this->kyc_status) {
            'pending' => ['class' => 'badge-secondary', 'text' => 'Pending'],
            'in_progress' => ['class' => 'badge-warning', 'text' => 'In Progress'],
            'completed' => ['class' => 'badge-success', 'text' => 'Completed'],
            'rejected' => ['class' => 'badge-danger', 'text' => 'Rejected'],
            'expired' => ['class' => 'badge-dark', 'text' => 'Expired'],
            default => ['class' => 'badge-light', 'text' => 'Not Started']
        };
    }

    public function getRequiredKycDocuments(): array
    {
        return [
            'driver_license_scan' => [
                'name' => 'Driver License Scan',
                'description' => 'Clear photo or scan of your driver license',
                'required' => true,
                'formats' => ['JPG', 'PNG', 'PDF'],
                'max_size' => '2MB'
            ],
            'national_id' => [
                'name' => 'National ID',
                'description' => 'Clear photo or scan of your National ID card',
                'required' => true,
                'formats' => ['JPG', 'PNG', 'PDF'],
                'max_size' => '2MB'
            ],
            'passport_photo' => [
                'name' => 'Passport Photo',
                'description' => 'Recent passport-style photograph',
                'required' => true,
                'formats' => ['JPG', 'PNG'],
                'max_size' => '2MB'
            ],
            'utility_bill' => [
                'name' => 'Utility Bill',
                'description' => 'Recent utility bill for address verification',
                'required' => false,
                'formats' => ['JPG', 'PNG', 'PDF'],
                'max_size' => '2MB'
            ],
        ];
    }

    public function getKycDocumentStatus(): array
    {
        $requiredDocs = ['driver_license_scan', 'national_id', 'passport_photo'];
        $uploadedDocs = $this->documents()
            ->whereIn('document_type', $requiredDocs)
            ->get()
            ->keyBy('document_type');

        $status = [];
        foreach ($requiredDocs as $docType) {
            $status[$docType] = [
                'uploaded' => $uploadedDocs->has($docType),
                'status' => $uploadedDocs->has($docType) ?
                    $uploadedDocs[$docType]->verification_status : 'not_uploaded',
                'uploaded_at' => $uploadedDocs->has($docType) ?
                    $uploadedDocs[$docType]->created_at : null,
            ];
        }

        return $status;
    }

    public function getKycSummaryForAdmin(): array
    {
        return [
            'driver_info' => [
                'id' => $this->id,
                'driver_id' => $this->driver_id,
                'name' => $this->full_name,
                'email' => $this->email,
                'phone' => $this->phone,
            ],
            'kyc_status' => [
                'status' => $this->kyc_status,
                'current_step' => $this->getCurrentKycStep(),
                'progress_percentage' => $this->getKycProgressPercentage(),
                'submitted_at' => $this->kyc_submitted_at,
                'completed_at' => $this->kyc_completed_at,
            ],
            'step_completion' => [
                'step_1' => $this->isKycStepCompleted(1),
                'step_2' => $this->isKycStepCompleted(2),
                'step_3' => $this->isKycStepCompleted(3),
            ],
            'documents' => $this->getKycDocumentStatus(),
            'license_info' => [
                'license_number' => $this->license_number,
                'license_class' => $this->license_class,
                'license_expiry_date' => $this->license_expiry_date?->format('Y-m-d'),
            ],
        ];
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeByNationality($query, $nationalityId)
    {
        return $query->where('nationality_id', $nationalityId);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByAge($query, $minAge = null, $maxAge = null)
    {
        if ($minAge) {
            $query->whereDate('date_of_birth', '<=', now()->subYears($minAge));
        }

        if ($maxAge) {
            $query->whereDate('date_of_birth', '>=', now()->subYears($maxAge));
        }

        return $query;
    }

    public function scopeWithBasicDetails($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'middle_name', 'surname', 'nickname',
            'email', 'phone', 'gender', 'status', 'verification_status',
            'is_active', 'profile_picture', 'created_at'
        ]);
    }

    public function scopeWithCompleteProfile($query, $includeDocuments = false, $includePerformance = false)
    {
        $with = [
            'nationality:id,name,code',
            'verifiedBy:id,name,email',
            'primaryBankingDetail' => function($q) {
                $q->select(['id', 'driver_id', 'bank_id', 'account_name', 'is_verified'])
                  ->with(['bank:id,name,code']);
            },
            'primaryNextOfKin:id,driver_id,name,relationship,phone'
        ];

        if ($includeDocuments) {
            $with['documents'] = function($q) {
                $q->select(['id', 'driver_id', 'document_type', 'verification_status', 'verified_at'])
                  ->limit(10);
            };
        }

        if ($includePerformance) {
            $with['performance:id,driver_id,total_jobs_completed,average_rating,total_earnings'] = function($q) {
                $q->whereNotNull('total_jobs_completed');
            };
        }

        return $query->with($with)->select([
            'id', 'driver_id', 'first_name', 'middle_name', 'surname', 'nickname',
            'email', 'phone', 'date_of_birth', 'gender', 'nationality_id',
            'status', 'verification_status', 'is_active', 'verified_by',
            'profile_picture', 'created_at', 'updated_at'
        ]);
    }

    public function scopeWithLocationData($query)
    {
        return $query->with([
            'originLocation' => function($q) {
                $q->select(['id', 'driver_id', 'state_id', 'lga_id', 'address'])
                  ->with(['state:id,name', 'lga:id,name']);
            },
            'residenceLocation' => function($q) {
                $q->select(['id', 'driver_id', 'state_id', 'lga_id', 'address'])
                  ->with(['state:id,name', 'lga:id,name']);
            }
        ]);
    }

    public function scopeForAdminList($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'status', 'verification_status', 'is_active', 'created_at', 'verified_at'
        ])->with([
            'nationality:id,name',
            'verifiedBy:id,name'
        ]);
    }

    public function scopeForMatching($query)
    {
        return $query->verified()
            ->available()
            ->select([
                'id', 'driver_id', 'first_name', 'surname', 'phone',
                'license_class', 'last_active_at', 'residence_state_id', 'residence_lga_id'
            ])
            ->with([
                'preferences:id,driver_id,preferred_work_areas,vehicle_type_preference',
                'performance:id,driver_id,average_rating,total_jobs_completed',
                'residenceState:id,name',
                'residenceLga:id,name'
            ]);
    }

    public function scopeForDashboardStats($query)
    {
        return $query->select([
            'id', 'status', 'verification_status', 'is_active',
            'created_at', 'last_active_at'
        ]);
    }

    public function scopeForBulkOperations($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'status', 'verification_status', 'created_at', 'updated_at'
        ]);
    }

    public function scopeForDocumentVerification($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'status', 'verification_status', 'ocr_verification_status',
            'nin_document', 'frsc_document', 'license_front_image', 'license_back_image',
            'nin_ocr_match_score', 'frsc_ocr_match_score', 'created_at'
        ])->with([
            'documents' => function($q) {
                $q->select(['id', 'driver_id', 'document_type', 'document_path', 'verification_status']);
            }
        ]);
    }

    public function scopeForAdminKycReview($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'kyc_status', 'kyc_step', 'verification_status',
            'kyc_submitted_at', 'kyc_completed_at', 'kyc_retry_count',
            'profile_completion_percentage', 'created_at'
        ]);
    }

    public function scopeKycPending($query)
    {
        return $query->where('kyc_status', 'pending');
    }

    public function scopeKycInProgress($query)
    {
        return $query->where('kyc_status', 'in_progress');
    }

    public function scopeKycCompleted($query)
    {
        return $query->where('kyc_status', 'completed');
    }

    public function scopeKycRejected($query)
    {
        return $query->where('kyc_status', 'rejected');
    }

    public function scopeAwaitingKycReview($query)
    {
        return $query->where('kyc_status', 'completed')
            ->where('verification_status', 'reviewing');
    }

    public function scopeCompletedKycToday($query)
    {
        return $query->where('kyc_status', 'completed')
            ->whereDate('kyc_completed_at', today());
    }

    // Methods
    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->is_active;
    }

    public function hasCompleteProfile()
    {
        return DrivelinkHelper::calculateDriverCompletionPercentage($this) >= 80;
    }

    public function getDocumentCompletionPercentage()
    {
        $requiredDocs = ['nin', 'license_front', 'license_back', 'profile_picture'];
        $uploadedDocs = $this->documents()->whereIn('document_type', $requiredDocs)->count();

        return round(($uploadedDocs / count($requiredDocs)) * 100);
    }

    public function getVerificationCompletionPercentage()
    {
        return DrivelinkHelper::calculateDriverCompletionPercentage($this);
    }

    public function adminUpdateStatus($status, $adminUser)
    {
        $this->update([
            'status' => $status,
            'updated_at' => now()
        ]);
    }

    public function adminUpdateVerification($status, $adminUser, $notes = null)
    {
        $updateData = [
            'verification_status' => $status,
            'verified_by' => $adminUser->id,
            'verification_notes' => $notes,
        ];

        if ($status === 'verified') {
            $updateData['verified_at'] = now();
            $updateData['rejected_at'] = null;
            $updateData['rejection_reason'] = null;
        } elseif ($status === 'rejected') {
            $updateData['rejected_at'] = now();
            $updateData['rejection_reason'] = $notes;
            $updateData['verified_at'] = null;
        }

        $this->update($updateData);
    }

    public function adminUpdateOCRVerification($status, $notes = null)
    {
        $this->update([
            'ocr_verification_status' => $status,
            'ocr_verification_notes' => $notes,
        ]);
    }
}

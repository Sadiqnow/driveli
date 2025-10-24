<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\DrivelinkHelper;
use App\Traits\HasEncryptedFields;
use App\Traits\DriverKycTrait;
use App\Traits\DriverVerificationTrait;
use App\Traits\DriverScopesTrait;
use App\Traits\DriverRelationshipTrait;
use App\Services\EncryptionService;
use App\Constants\DrivelinkConstants;

class Drivers extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasEncryptedFields, EnhancedDriverTrait,
        DriverKycTrait, DriverVerificationTrait, DriverScopesTrait, DriverRelationshipTrait;

    protected $table = 'drivers';
    
    // This is now the primary and only drivers table
    protected static $unguarded = false;

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
        'residential_address',  // Both variants for compatibility
        'residence_state_id',
        'residence_lga_id',
        'vehicle_types',
        'work_regions',
        'special_skills',
        // System fields that should be fillable for admin creation
        'status',
        'verification_status',
        'is_active',
        'registered_at',
        'verification_notes',
        'verified_by',
        // OCR Verification fields
        'ocr_verification_status',
        'ocr_verification_notes',
        'nin_verification_data',
        'nin_verified_at',
        'nin_ocr_match_score',
        'frsc_verification_data',
        'frsc_verified_at',
        'frsc_ocr_match_score',
        // KYC Verification fields
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

        // Additional registration and KYC fields
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
        // Missing KYC Step 2 fields
        'city',
        'postal_code',
        'license_issue_date',
        'available',
    ];

    /**
     * Fields that should never be mass assigned for security reasons
     * Note: OCR verification fields are now fillable to allow updates during verification process
     */
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
        // KYC field casts
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

        // Additional field casts
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

    // Password hashing mutator - secure implementation
    public function setPasswordAttribute($value)
    {
        // Only hash if value is provided
        if (!empty($value)) {
            // More secure validation: check if it's a valid bcrypt/argon2 hash
            if (preg_match('/^\$2[abxy]\$\d{2}\$.{53}$/', $value) ||
                preg_match('/^\$argon2i?\$.+/', $value)) {
                // Already a valid hash
                $this->attributes['password'] = $value;
            } else {
                // Hash the plain text password
                $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
            }
        }
    }

    // Relationships with eager loading optimization
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



    public function documents()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id', 'id');
    }

    public function verificationLogs()
    {
        return $this->hasMany(DriverVerificationLog::class, 'driver_id', 'id');
    }

    public function facialVerifications()
    {
        return $this->hasMany(DriverFacialVerification::class, 'driver_id', 'id');
    }

    public function moderatorActions()
    {
        return $this->hasMany(ModeratorAction::class, 'driver_id', 'id');
    }



    public function employmentHistory()
    {
        return $this->hasMany(DriverEmploymentHistory::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'company_name', 'job_title', 'start_date', 'end_date', 'is_current'])
            ->orderBy('start_date', 'desc');
    }



    public function nextOfKin()
    {
        return $this->hasMany(DriverNextOfKin::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'name', 'relationship', 'phone', 'is_primary']);
    }



    public function bankingDetails()
    {
        return $this->hasMany(DriverBankingDetail::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'bank_id', 'account_number', 'account_name', 'is_primary', 'is_verified'])
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








}

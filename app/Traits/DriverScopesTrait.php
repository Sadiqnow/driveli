<?php

namespace App\Traits;

trait DriverScopesTrait
{
    // ========================================================================================
    // BASIC SCOPES
    // ========================================================================================

    /**
     * Scope for verified drivers
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope for active drivers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for available drivers
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    // ========================================================================================
    // FILTER SCOPES
    // ========================================================================================

    /**
     * Scope by nationality
     */
    public function scopeByNationality($query, $nationalityId)
    {
        return $query->where('nationality_id', $nationalityId);
    }

    /**
     * Scope by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope by age range
     */
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

    // ========================================================================================
    // PROFILE SCOPES
    // ========================================================================================

    /**
     * Scope with basic driver details
     */
    public function scopeWithBasicDetails($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'middle_name', 'surname', 'nickname',
            'email', 'phone', 'gender', 'status', 'verification_status',
            'is_active', 'profile_picture', 'created_at'
        ]);
    }

    /**
     * Scope with complete profile information
     */
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

        // Conditionally load expensive relationships
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

    // ========================================================================================
    // LOCATION SCOPES
    // ========================================================================================

    /**
     * Scope with location data
     */
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

    // ========================================================================================
    // ADMIN SCOPES
    // ========================================================================================

    /**
     * Scope for admin driver list
     */
    public function scopeForAdminList($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'middle_name', 'surname', 'nickname', 'email', 'phone',
            'status', 'verification_status', 'is_active', 'created_at', 'verified_at',
            'verification_notes', 'profile_picture', 'profile_photo'
        ])->with([
            'nationality:id,name',
            'verifiedBy:id,name'
        ]);
    }

    // ========================================================================================
    // MATCHING SCOPES
    // ========================================================================================

    /**
     * Scope for driver matching system
     */
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

    // ========================================================================================
    // DASHBOARD SCOPES
    // ========================================================================================

    /**
     * Scope for dashboard statistics
     */
    public function scopeForDashboardStats($query)
    {
        return $query->select([
            'id', 'status', 'verification_status', 'is_active',
            'created_at', 'last_active_at'
        ]);
    }

    // ========================================================================================
    // BULK OPERATIONS SCOPES
    // ========================================================================================

    /**
     * Scope for bulk operations
     */
    public function scopeForBulkOperations($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'status', 'verification_status', 'created_at', 'updated_at'
        ]);
    }

    // ========================================================================================
    // DOCUMENT VERIFICATION SCOPES
    // ========================================================================================

    /**
     * Scope for document verification
     */
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
}

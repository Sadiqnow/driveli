<?php

namespace App\Models;

trait EnhancedDriverTrait
{
    /**
     * Additional fillable fields for global driver categories
     */
    protected function getGlobalFillableFields(): array
    {
        return [
            // ===== GLOBAL DRIVER CATEGORY FIELDS =====
            'driver_category',
            'employment_preference',
            'country_id',
            'timezone',
            'spoken_languages',
            'preferred_communication_language',
            'preferred_work_regions',
            'international_phone',
            'emergency_contact_details',
            'vehicle_specializations',
            'certifications',
            'background_check_data',
            'rate_per_hour',
            'rate_per_km',
            'currency_preference',
            'service_radius_km',
            'availability_schedule',
            'preferred_job_types',

            // Commercial Truck Driver fields
            'commercial_license_number',
            'cdl_class',
            'hazmat_certification',
            'max_load_capacity_kg',
            'route_experience',
            'safety_certifications',
            'international_driving_permits',

            // Professional Driver fields
            'defensive_driving_certification',
            'executive_vehicle_experience',
            'luxury_car_experience',
            'background_check_status',
            'customer_service_training',
            'etiquette_training',
            'multi_language_communication',

            // Public Driver fields
            'ride_share_permits',
            'personal_vehicle_details',
            'vehicle_insurance_number',
            'vehicle_inspection_date',
            'platform_experience',

            // Executive Driver fields
            'security_clearance_level',
            'vip_protection_training',
            'armored_vehicle_training',
            'diplomatic_protocol_training',
            'reference_verification',

            // Enhanced KYC tracking
            'profile_completion_step',
            'category_specific_requirements',
            'category_verification_completed_at',
        ];
    }

    /**
     * Additional casts for global driver fields
     */
    protected function getGlobalCasts(): array
    {
        return [
            // ===== GLOBAL CATEGORY FIELD CASTS =====
            'spoken_languages' => 'array',
            'preferred_work_regions' => 'array',
            'emergency_contact_details' => 'array',
            'vehicle_specializations' => 'array',
            'certifications' => 'array',
            'background_check_data' => 'array',
            'rate_per_hour' => 'decimal:2',
            'rate_per_km' => 'decimal:2',
            'service_radius_km' => 'integer',
            'availability_schedule' => 'array',
            'preferred_job_types' => 'array',
            'hazmat_certification' => 'boolean',
            'max_load_capacity_kg' => 'integer',
            'route_experience' => 'array',
            'safety_certifications' => 'array',
            'international_driving_permits' => 'array',
            'defensive_driving_certification' => 'boolean',
            'executive_vehicle_experience' => 'array',
            'luxury_car_experience' => 'array',
            'customer_service_training' => 'boolean',
            'etiquette_training' => 'boolean',
            'multi_language_communication' => 'boolean',
            'ride_share_permits' => 'array',
            'personal_vehicle_details' => 'array',
            'vehicle_inspection_date' => 'date',
            'platform_experience' => 'array',
            'vip_protection_training' => 'boolean',
            'armored_vehicle_training' => 'boolean',
            'diplomatic_protocol_training' => 'boolean',
            'reference_verification' => 'array',
            'profile_completion_step' => 'integer',
            'category_specific_requirements' => 'array',
            'category_verification_completed_at' => 'datetime',
        ];
    }

    // ===== GLOBAL RELATIONSHIPS =====
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function globalState()
    {
        return $this->belongsTo(GlobalState::class, 'state_id');
    }

    public function globalCity()
    {
        return $this->belongsTo(GlobalCity::class, 'city_id');
    }

    // ===== DRIVER CATEGORY METHODS =====

    public function isCommercialTruckDriver(): bool
    {
        return $this->driver_category === 'commercial_truck';
    }

    public function isProfessionalDriver(): bool
    {
        return $this->driver_category === 'professional';
    }

    public function isPublicDriver(): bool
    {
        return $this->driver_category === 'public';
    }

    public function isExecutiveDriver(): bool
    {
        return $this->driver_category === 'executive';
    }

    public function getCategoryDisplayName(): string
    {
        return match($this->driver_category) {
            'commercial_truck' => 'Commercial Truck Driver',
            'professional' => 'Professional Driver',
            'public' => 'Public Driver',
            'executive' => 'Executive Driver',
            default => 'Unknown Category'
        };
    }

    public function getEmploymentDisplayName(): string
    {
        return match($this->employment_preference) {
            'part_time' => 'Part Time',
            'full_time' => 'Full Time',
            'contract' => 'Contract',
            'assignment' => 'Assignment Based',
            default => 'Not Specified'
        };
    }

    // ===== ENHANCED KYC METHODS =====

    /**
     * Get KYC progress based on driver category
     */
    public function getCategorySpecificKycProgress(): int
    {
        if (!$this->driver_category) {
            return 0;
        }

        $baseProgress = $this->getKycProgressPercentage();
        $categoryStepComplete = $this->category_verification_completed_at ? 20 : 0;
        $categoryRequirements = $this->getCategoryRequirementCompletion();

        return min(100, $baseProgress + $categoryStepComplete + $categoryRequirements);
    }

    /**
     * Get completion percentage for category-specific requirements
     */
    public function getCategoryRequirementCompletion(): int
    {
        if (!$this->driver_category) {
            return 0;
        }

        $requirements = DriverCategoryRequirement::getRequirementsForCategory(
            $this->driver_category, 
            $this->country_id
        );

        if (!$requirements) {
            return 0;
        }

        $totalRequirements = 0;
        $completedRequirements = 0;

        // Check licenses
        if ($requiredLicenses = $requirements->getRequirementsByType('licenses')) {
            $totalRequirements += count($requiredLicenses);
            foreach ($requiredLicenses as $license) {
                if ($this->hasRequiredLicense($license)) {
                    $completedRequirements++;
                }
            }
        }

        // Check certifications
        if ($requiredCertifications = $requirements->getRequirementsByType('certifications')) {
            $totalRequirements += count($requiredCertifications);
            foreach ($requiredCertifications as $certification) {
                if ($this->hasRequiredCertification($certification)) {
                    $completedRequirements++;
                }
            }
        }

        return $totalRequirements > 0 ? round(($completedRequirements / $totalRequirements) * 20) : 20;
    }

    /**
     * Check if driver has required license
     */
    protected function hasRequiredLicense(string $license): bool
    {
        return match($license) {
            'commercial_cdl' => !empty($this->commercial_license_number),
            'hazmat' => $this->hazmat_certification,
            'defensive_driving' => $this->defensive_driving_certification,
            'professional_license' => !empty($this->license_number),
            'executive_license' => !empty($this->license_number) && $this->security_clearance_level !== 'none',
            default => false
        };
    }

    /**
     * Check if driver has required certification
     */
    protected function hasRequiredCertification(string $certification): bool
    {
        $certs = $this->certifications ?? [];
        return in_array($certification, $certs) || $this->hasSpecificCertification($certification);
    }

    /**
     * Check specific certifications based on category
     */
    protected function hasSpecificCertification(string $certification): bool
    {
        return match($certification) {
            'customer_service' => $this->customer_service_training,
            'etiquette' => $this->etiquette_training,
            'vip_protection' => $this->vip_protection_training,
            'armored_vehicle' => $this->armored_vehicle_training,
            'diplomatic_protocol' => $this->diplomatic_protocol_training,
            default => false
        };
    }

    // ===== GLOBAL SCOPES =====

    public function scopeByCategory($query, string $category)
    {
        return $query->where('driver_category', $category);
    }

    public function scopeByEmploymentType($query, string $employment)
    {
        return $query->where('employment_preference', $employment);
    }

    public function scopeByCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeByCurrency($query, string $currency)
    {
        return $query->where('currency_preference', $currency);
    }

    public function scopeAvailableForRates($query, float $minRate = null, float $maxRate = null)
    {
        if ($minRate) {
            $query->where('rate_per_hour', '>=', $minRate);
        }
        if ($maxRate) {
            $query->where('rate_per_hour', '<=', $maxRate);
        }
        return $query;
    }

    public function scopeWithinServiceRadius($query, float $latitude, float $longitude, int $maxRadius = null)
    {
        // This would require spatial database queries - simplified version
        return $query->when($maxRadius, function($q) use ($maxRadius) {
            $q->where('service_radius_km', '<=', $maxRadius);
        });
    }

    public function scopeCommercialTruckDrivers($query)
    {
        return $query->byCategory('commercial_truck');
    }

    public function scopeProfessionalDrivers($query)
    {
        return $query->byCategory('professional');
    }

    public function scopePublicDrivers($query)
    {
        return $query->byCategory('public');
    }

    public function scopeExecutiveDrivers($query)
    {
        return $query->byCategory('executive');
    }

    // ===== UTILITY METHODS =====

    public function getSpokenLanguagesNames(): array
    {
        if (!$this->spoken_languages) {
            return [];
        }

        return GlobalLanguage::whereIn('code', $this->spoken_languages)
            ->pluck('name')
            ->toArray();
    }

    public function getPreferredCommunicationLanguageName(): string
    {
        $lang = GlobalLanguage::where('code', $this->preferred_communication_language)->first();
        return $lang ? $lang->name : 'English';
    }

    public function getFormattedRate(string $type = 'hour'): string
    {
        $rate = $type === 'hour' ? $this->rate_per_hour : $this->rate_per_km;
        $symbol = $this->country?->currency_symbol ?? '$';
        
        return $rate ? $symbol . number_format($rate, 2) : 'Not set';
    }

    public function getCategorySpecificData(): array
    {
        return match($this->driver_category) {
            'commercial_truck' => $this->getCommercialTruckData(),
            'professional' => $this->getProfessionalDriverData(),
            'public' => $this->getPublicDriverData(),
            'executive' => $this->getExecutiveDriverData(),
            default => []
        };
    }

    protected function getCommercialTruckData(): array
    {
        return [
            'commercial_license' => $this->commercial_license_number,
            'cdl_class' => $this->cdl_class,
            'hazmat_certified' => $this->hazmat_certification,
            'max_load_capacity' => $this->max_load_capacity_kg ? $this->max_load_capacity_kg . ' kg' : null,
            'route_experience' => $this->route_experience,
            'safety_certifications' => $this->safety_certifications,
            'international_permits' => $this->international_driving_permits,
        ];
    }

    protected function getProfessionalDriverData(): array
    {
        return [
            'defensive_driving_certified' => $this->defensive_driving_certification,
            'executive_vehicle_experience' => $this->executive_vehicle_experience,
            'luxury_car_experience' => $this->luxury_car_experience,
            'background_check_status' => $this->background_check_status,
            'customer_service_trained' => $this->customer_service_training,
            'etiquette_trained' => $this->etiquette_training,
            'multi_language' => $this->multi_language_communication,
        ];
    }

    protected function getPublicDriverData(): array
    {
        return [
            'ride_share_permits' => $this->ride_share_permits,
            'personal_vehicle' => $this->personal_vehicle_details,
            'insurance_number' => $this->vehicle_insurance_number,
            'last_inspection' => $this->vehicle_inspection_date,
            'platform_experience' => $this->platform_experience,
        ];
    }

    protected function getExecutiveDriverData(): array
    {
        return [
            'security_clearance' => $this->security_clearance_level,
            'vip_protection_trained' => $this->vip_protection_training,
            'armored_vehicle_trained' => $this->armored_vehicle_training,
            'diplomatic_protocol_trained' => $this->diplomatic_protocol_training,
            'references_verified' => $this->reference_verification,
        ];
    }
}
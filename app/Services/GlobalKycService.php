<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverCategoryRequirement;
use App\Models\Country;
use App\Models\GlobalVehicleType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlobalKycService
{
    /**
     * Initialize global KYC process for driver
     */
    public function initializeKyc(Driver $driver, array $categoryData): array
    {
        DB::beginTransaction();
        
        try {
            // Step 1: Set driver category and basic global info
            $driver->update([
                'driver_category' => $categoryData['driver_category'],
                'employment_preference' => $categoryData['employment_preference'],
                'country_id' => $categoryData['country_id'] ?? $this->getDefaultCountryId(),
                'timezone' => $categoryData['timezone'] ?? $this->getTimezoneForCountry($categoryData['country_id'] ?? null),
                'spoken_languages' => $categoryData['spoken_languages'] ?? ['en'],
                'preferred_communication_language' => $categoryData['preferred_communication_language'] ?? 'en',
                'currency_preference' => $categoryData['currency_preference'] ?? $this->getCurrencyForCountry($categoryData['country_id'] ?? null),
                'kyc_step' => 1,
                'kyc_status' => 'in_progress',
                'profile_completion_step' => 1,
                'kyc_last_activity_at' => now(),
            ]);

            // Step 2: Get category-specific requirements
            $requirements = $this->getCategoryRequirements($driver->driver_category, $driver->country_id);
            
            // Step 3: Set up category-specific requirement tracking
            $driver->update([
                'category_specific_requirements' => $requirements
            ]);

            DB::commit();
            
            return [
                'success' => true,
                'current_step' => 1,
                'progress_percentage' => 30,
                'next_requirements' => $this->getStep1Requirements($driver),
                'category_requirements' => $requirements
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC Initialization failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => 'Failed to initialize KYC process. Please try again.'
            ];
        }
    }

    /**
     * Process KYC Step 1: Basic Profile & Category Selection (30% completion)
     */
    public function processStep1(Driver $driver, array $data): array
    {
        DB::beginTransaction();
        
        try {
            $basicFields = [
                'nickname' => $data['nickname'] ?? null,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'surname' => $data['surname'],
                'phone' => $data['phone'],
                'international_phone' => $data['international_phone'] ?? null,
                'email' => $data['email'] ?? null,
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'nationality_id' => $data['nationality_id'],
                'preferred_work_regions' => $data['preferred_work_regions'] ?? [],
                'emergency_contact_details' => [
                    'name' => $data['emergency_contact_name'] ?? null,
                    'phone' => $data['emergency_contact_phone'] ?? null,
                    'relationship' => $data['emergency_contact_relationship'] ?? null,
                ],
                'availability_schedule' => $data['availability_schedule'] ?? [],
                'employment_preference' => $data['employment_preference'] ?? $driver->employment_preference,
            ];

            // Update driver with step 1 data
            $driver->update(array_merge($basicFields, [
                'kyc_step_1_completed_at' => now(),
                'profile_completion_percentage' => 30,
                'profile_completion_step' => 2,
                'kyc_step' => 2,
                'kyc_last_activity_at' => now(),
            ]));

            DB::commit();
            
            return [
                'success' => true,
                'current_step' => 2,
                'progress_percentage' => 30,
                'next_requirements' => $this->getStep2Requirements($driver),
                'message' => 'Basic profile completed successfully!'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC Step 1 failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => 'Failed to save basic profile. Please try again.'
            ];
        }
    }

    /**
     * Process KYC Step 2: Category-Specific Requirements (65% completion)
     */
    public function processStep2(Driver $driver, array $data): array
    {
        DB::beginTransaction();
        
        try {
            $categoryFields = $this->getCategorySpecificFields($driver->driver_category, $data);
            
            // Update driver with step 2 category-specific data
            $driver->update(array_merge($categoryFields, [
                'kyc_step_2_completed_at' => now(),
                'profile_completion_percentage' => 65,
                'profile_completion_step' => 3,
                'kyc_step' => 3,
                'kyc_last_activity_at' => now(),
            ]));

            DB::commit();
            
            return [
                'success' => true,
                'current_step' => 3,
                'progress_percentage' => 65,
                'next_requirements' => $this->getStep3Requirements($driver),
                'message' => ucfirst($driver->getCategoryDisplayName()) . ' requirements completed successfully!'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC Step 2 failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => 'Failed to save category requirements. Please try again.'
            ];
        }
    }

    /**
     * Process KYC Step 3: Verification & Onboarding (100% completion)
     */
    public function processStep3(Driver $driver, array $data): array
    {
        DB::beginTransaction();
        
        try {
            $verificationFields = [
                'nin_number' => $data['nin_number'] ?? null,
                'license_number' => $data['license_number'],
                'license_class' => $data['license_class'],
                'license_expiry_date' => $data['license_expiry_date'],
                'rate_per_hour' => $data['rate_per_hour'] ?? null,
                'rate_per_km' => $data['rate_per_km'] ?? null,
                'service_radius_km' => $data['service_radius_km'] ?? null,
                'preferred_job_types' => $data['preferred_job_types'] ?? [],
                'vehicle_specializations' => $data['vehicle_specializations'] ?? [],
                'background_check_data' => $data['background_check_data'] ?? [],
            ];

            // Banking details if provided
            if (isset($data['bank_id']) && isset($data['account_number'])) {
                $verificationFields['bank_id'] = $data['bank_id'];
                $verificationFields['account_number'] = $data['account_number'];
                $verificationFields['account_name'] = $data['account_name'] ?? null;
                $verificationFields['bvn'] = $data['bvn'] ?? null;
            }

            // Complete KYC
            $driver->update(array_merge($verificationFields, [
                'kyc_step_3_completed_at' => now(),
                'kyc_completed_at' => now(),
                'kyc_submitted_at' => now(),
                'kyc_status' => 'completed',
                'verification_status' => 'reviewing',
                'profile_completion_percentage' => 100,
                'profile_completion_step' => 4,
                'category_verification_completed_at' => now(),
                'kyc_last_activity_at' => now(),
            ]));

            DB::commit();
            
            return [
                'success' => true,
                'current_step' => 'completed',
                'progress_percentage' => 100,
                'message' => 'KYC completed successfully! Your profile is now under review.',
                'next_action' => 'await_admin_review'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC Step 3 failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => 'Failed to complete verification. Please try again.'
            ];
        }
    }

    /**
     * Get requirements for Step 1
     */
    protected function getStep1Requirements(Driver $driver): array
    {
        return [
            'basic_info' => [
                'first_name' => 'required',
                'surname' => 'required', 
                'phone' => 'required|unique',
                'email' => 'optional|unique',
                'date_of_birth' => 'required',
                'gender' => 'required',
                'nationality_id' => 'required',
            ],
            'contact_info' => [
                'emergency_contact_name' => 'required',
                'emergency_contact_phone' => 'required',
                'emergency_contact_relationship' => 'required',
            ],
            'preferences' => [
                'preferred_work_regions' => 'optional|array',
                'availability_schedule' => 'optional|array',
                'employment_preference' => 'required',
            ]
        ];
    }

    /**
     * Get requirements for Step 2 based on driver category
     */
    protected function getStep2Requirements(Driver $driver): array
    {
        $categoryRequirements = DriverCategoryRequirement::getRequirementsForCategory(
            $driver->driver_category,
            $driver->country_id
        );

        return match($driver->driver_category) {
            'commercial_truck' => $this->getCommercialTruckStep2Requirements($categoryRequirements),
            'professional' => $this->getProfessionalStep2Requirements($categoryRequirements),
            'public' => $this->getPublicStep2Requirements($categoryRequirements),
            'executive' => $this->getExecutiveStep2Requirements($categoryRequirements),
            default => []
        };
    }

    /**
     * Get requirements for Step 3
     */
    protected function getStep3Requirements(Driver $driver): array
    {
        return [
            'identity_verification' => [
                'nin_number' => 'required_if:country_id,1', // Nigeria
                'license_number' => 'required',
                'license_class' => 'required',
                'license_expiry_date' => 'required|after:today',
            ],
            'financial_setup' => [
                'bank_id' => 'optional',
                'account_number' => 'required_with:bank_id',
                'account_name' => 'optional',
                'bvn' => 'optional',
            ],
            'platform_setup' => [
                'rate_per_hour' => 'optional|numeric|min:0',
                'rate_per_km' => 'optional|numeric|min:0',
                'service_radius_km' => 'optional|integer|min:1',
                'preferred_job_types' => 'optional|array',
            ]
        ];
    }

    /**
     * Get Commercial Truck requirements for Step 2
     */
    protected function getCommercialTruckStep2Requirements($categoryRequirements): array
    {
        return [
            'license_requirements' => [
                'commercial_license_number' => 'required',
                'cdl_class' => 'required',
                'hazmat_certification' => 'optional|boolean',
            ],
            'vehicle_expertise' => [
                'vehicle_specializations' => 'required|array|min:1',
                'max_load_capacity_kg' => 'optional|integer|min:1000',
                'route_experience' => 'optional|array',
            ],
            'experience' => [
                'years_of_experience' => 'required|integer|min:1',
                'previous_company' => 'optional|string',
                'safety_certifications' => 'optional|array',
            ],
            'additional' => [
                'international_driving_permits' => 'optional|array',
                'special_skills' => 'optional|string',
            ]
        ];
    }

    /**
     * Get Professional Driver requirements for Step 2
     */
    protected function getProfessionalStep2Requirements($categoryRequirements): array
    {
        return [
            'professional_skills' => [
                'defensive_driving_certification' => 'optional|boolean',
                'customer_service_training' => 'optional|boolean',
                'etiquette_training' => 'optional|boolean',
                'multi_language_communication' => 'optional|boolean',
            ],
            'vehicle_experience' => [
                'executive_vehicle_experience' => 'optional|array',
                'luxury_car_experience' => 'optional|array',
                'vehicle_specializations' => 'required|array|min:1',
            ],
            'background' => [
                'background_check_status' => 'required',
                'background_check_data' => 'optional|array',
                'years_of_experience' => 'required|integer|min:1',
            ]
        ];
    }

    /**
     * Get Public Driver requirements for Step 2  
     */
    protected function getPublicStep2Requirements($categoryRequirements): array
    {
        return [
            'vehicle_info' => [
                'personal_vehicle_details' => 'required|array',
                'vehicle_insurance_number' => 'required',
                'vehicle_inspection_date' => 'required|date|before_or_equal:today',
            ],
            'platform_experience' => [
                'ride_share_permits' => 'optional|array',
                'platform_experience' => 'optional|array',
            ],
            'basic_requirements' => [
                'years_of_experience' => 'required|integer|min:1',
                'has_vehicle' => 'required|boolean',
            ]
        ];
    }

    /**
     * Get Executive Driver requirements for Step 2
     */
    protected function getExecutiveStep2Requirements($categoryRequirements): array
    {
        return [
            'security_clearance' => [
                'security_clearance_level' => 'required',
                'background_check_status' => 'required',
                'reference_verification' => 'required|array|min:2',
            ],
            'specialized_training' => [
                'vip_protection_training' => 'optional|boolean',
                'armored_vehicle_training' => 'optional|boolean',
                'diplomatic_protocol_training' => 'optional|boolean',
                'defensive_driving_certification' => 'required|boolean',
            ],
            'experience' => [
                'years_of_experience' => 'required|integer|min:3',
                'executive_vehicle_experience' => 'required|array',
                'luxury_car_experience' => 'optional|array',
            ]
        ];
    }

    /**
     * Get category-specific fields for Step 2 processing
     */
    protected function getCategorySpecificFields(string $category, array $data): array
    {
        return match($category) {
            'commercial_truck' => $this->getCommercialTruckFields($data),
            'professional' => $this->getProfessionalDriverFields($data),
            'public' => $this->getPublicDriverFields($data),
            'executive' => $this->getExecutiveDriverFields($data),
            default => []
        };
    }

    protected function getCommercialTruckFields(array $data): array
    {
        return [
            'commercial_license_number' => $data['commercial_license_number'] ?? null,
            'cdl_class' => $data['cdl_class'] ?? null,
            'hazmat_certification' => $data['hazmat_certification'] ?? false,
            'max_load_capacity_kg' => $data['max_load_capacity_kg'] ?? null,
            'route_experience' => $data['route_experience'] ?? [],
            'safety_certifications' => $data['safety_certifications'] ?? [],
            'international_driving_permits' => $data['international_driving_permits'] ?? [],
            'years_of_experience' => $data['years_of_experience'] ?? null,
            'previous_company' => $data['previous_company'] ?? null,
        ];
    }

    protected function getProfessionalDriverFields(array $data): array
    {
        return [
            'defensive_driving_certification' => $data['defensive_driving_certification'] ?? false,
            'executive_vehicle_experience' => $data['executive_vehicle_experience'] ?? [],
            'luxury_car_experience' => $data['luxury_car_experience'] ?? [],
            'background_check_status' => $data['background_check_status'] ?? 'pending',
            'customer_service_training' => $data['customer_service_training'] ?? false,
            'etiquette_training' => $data['etiquette_training'] ?? false,
            'multi_language_communication' => $data['multi_language_communication'] ?? false,
            'years_of_experience' => $data['years_of_experience'] ?? null,
        ];
    }

    protected function getPublicDriverFields(array $data): array
    {
        return [
            'ride_share_permits' => $data['ride_share_permits'] ?? [],
            'personal_vehicle_details' => $data['personal_vehicle_details'] ?? [],
            'vehicle_insurance_number' => $data['vehicle_insurance_number'] ?? null,
            'vehicle_inspection_date' => $data['vehicle_inspection_date'] ?? null,
            'platform_experience' => $data['platform_experience'] ?? [],
            'has_vehicle' => $data['has_vehicle'] ?? false,
            'years_of_experience' => $data['years_of_experience'] ?? null,
        ];
    }

    protected function getExecutiveDriverFields(array $data): array
    {
        return [
            'security_clearance_level' => $data['security_clearance_level'] ?? 'none',
            'vip_protection_training' => $data['vip_protection_training'] ?? false,
            'armored_vehicle_training' => $data['armored_vehicle_training'] ?? false,
            'diplomatic_protocol_training' => $data['diplomatic_protocol_training'] ?? false,
            'reference_verification' => $data['reference_verification'] ?? [],
            'defensive_driving_certification' => $data['defensive_driving_certification'] ?? false,
            'executive_vehicle_experience' => $data['executive_vehicle_experience'] ?? [],
            'luxury_car_experience' => $data['luxury_car_experience'] ?? [],
            'years_of_experience' => $data['years_of_experience'] ?? null,
        ];
    }

    /**
     * Helper methods
     */
    protected function getDefaultCountryId(): int
    {
        return Country::where('iso_code_2', 'NG')->first()?->id ?? 1;
    }

    protected function getTimezoneForCountry(?int $countryId): string
    {
        if (!$countryId) return 'Africa/Lagos';
        
        $country = Country::find($countryId);
        return $country?->timezone ?? 'Africa/Lagos';
    }

    protected function getCurrencyForCountry(?int $countryId): string
    {
        if (!$countryId) return 'NGN';
        
        $country = Country::find($countryId);
        return $country?->currency_code ?? 'NGN';
    }

    protected function getCategoryRequirements(string $category, int $countryId): array
    {
        $requirements = DriverCategoryRequirement::getRequirementsForCategory($category, $countryId);
        
        if (!$requirements) {
            return [];
        }

        return [
            'licenses' => $requirements->required_licenses ?? [],
            'certifications' => $requirements->required_certifications ?? [],
            'documents' => $requirements->required_documents ?? [],
            'background_checks' => $requirements->background_check_requirements ?? [],
            'minimum_experience' => $requirements->minimum_experience_years ?? 0,
            'vehicle_requirements' => $requirements->vehicle_requirements ?? []
        ];
    }

    /**
     * Get current KYC step data for driver
     */
    public function getCurrentStepData(Driver $driver): array
    {
        return [
            'current_step' => $driver->kyc_step ?? 1,
            'progress_percentage' => $driver->getCategorySpecificKycProgress(),
            'category' => $driver->driver_category,
            'category_display' => $driver->getCategoryDisplayName(),
            'employment_type' => $driver->employment_preference,
            'employment_display' => $driver->getEmploymentDisplayName(),
            'country' => $driver->country?->name ?? 'Nigeria',
            'requirements' => $this->getStepRequirements($driver, $driver->kyc_step ?? 1),
            'completed_steps' => [
                'step_1' => !is_null($driver->kyc_step_1_completed_at),
                'step_2' => !is_null($driver->kyc_step_2_completed_at),
                'step_3' => !is_null($driver->kyc_step_3_completed_at),
            ]
        ];
    }

    protected function getStepRequirements(Driver $driver, int $step): array
    {
        return match($step) {
            1 => $this->getStep1Requirements($driver),
            2 => $this->getStep2Requirements($driver),
            3 => $this->getStep3Requirements($driver),
            default => []
        };
    }
}
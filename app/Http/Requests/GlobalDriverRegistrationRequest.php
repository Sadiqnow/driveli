<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Country;
use App\Models\DriverCategoryRequirement;

class GlobalDriverRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $step = $this->input('kyc_step', 1);
        
        return match($step) {
            1 => $this->getStep1Rules(),
            2 => $this->getStep2Rules(),
            3 => $this->getStep3Rules(),
            default => $this->getStep1Rules()
        };
    }

    protected function getStep1Rules(): array
    {
        return [
            // Driver Category Selection
            'driver_category' => ['required', Rule::in(['commercial_truck', 'professional', 'public', 'executive'])],
            'employment_preference' => ['required', Rule::in(['part_time', 'full_time', 'contract', 'assignment'])],
            
            // Global Personal Info
            'nickname' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'nationality_id' => ['required', 'exists:nationalities,id'],
            
            // Contact Information
            'phone' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('drivers', 'phone')->ignore($this->driver?->id ?? null)
            ],
            'international_phone' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable', 
                'email', 
                'max:255',
                Rule::unique('drivers', 'email')->ignore($this->driver?->id ?? null)
            ],
            
            // Personal Details
            'date_of_birth' => ['required', 'date', 'before:-18 years'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            
            // Location
            'country_id' => ['required', 'exists:countries,id'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'preferred_work_regions' => ['nullable', 'array'],
            'preferred_work_regions.*' => ['string', 'max:255'],
            
            // Languages
            'spoken_languages' => ['nullable', 'array'],
            'spoken_languages.*' => ['string', 'exists:global_languages,code'],
            'preferred_communication_language' => ['nullable', 'string', 'exists:global_languages,code'],
            
            // Emergency Contact
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'emergency_contact_relationship' => ['required', 'string', 'max:100'],
            
            // Availability
            'availability_schedule' => ['nullable', 'array'],
            'availability_schedule.*.day' => ['required_with:availability_schedule', 'string'],
            'availability_schedule.*.start_time' => ['required_with:availability_schedule', 'string'],
            'availability_schedule.*.end_time' => ['required_with:availability_schedule', 'string'],
        ];
    }

    protected function getStep2Rules(): array
    {
        $category = $this->input('driver_category') ?? $this->driver?->driver_category;
        
        $baseRules = [
            'years_of_experience' => ['required', 'integer', 'min:1', 'max:50'],
            'previous_company' => ['nullable', 'string', 'max:255'],
            'vehicle_specializations' => ['required', 'array', 'min:1'],
            'vehicle_specializations.*' => ['string', 'exists:global_vehicle_types,id'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
        ];

        return array_merge($baseRules, match($category) {
            'commercial_truck' => $this->getCommercialTruckRules(),
            'professional' => $this->getProfessionalDriverRules(),
            'public' => $this->getPublicDriverRules(),
            'executive' => $this->getExecutiveDriverRules(),
            default => []
        });
    }

    protected function getStep3Rules(): array
    {
        $countryId = $this->input('country_id') ?? $this->driver?->country_id;
        $isNigeria = $this->isNigeria($countryId);
        
        return [
            // Identity Verification
            'nin_number' => [$isNigeria ? 'required' : 'nullable', 'string', 'size:11'],
            'license_number' => ['required', 'string', 'max:50'],
            'license_class' => ['required', 'string', 'max:20'],
            'license_expiry_date' => ['required', 'date', 'after:today'],
            'license_issue_date' => ['nullable', 'date', 'before:today'],
            
            // Financial Setup (optional)
            'bank_id' => ['nullable', 'exists:banks,id'],
            'account_number' => ['required_with:bank_id', 'string', 'max:20'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'bvn' => ['nullable', 'string', 'size:11'],
            
            // Platform Setup
            'rate_per_hour' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'rate_per_km' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'currency_preference' => ['nullable', 'string', 'size:3'],
            'service_radius_km' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'preferred_job_types' => ['nullable', 'array'],
            'preferred_job_types.*' => ['string', 'max:100'],
            
            // Background Check Data
            'background_check_data' => ['nullable', 'array'],
            'background_check_data.criminal_record' => ['nullable', 'boolean'],
            'background_check_data.driving_violations' => ['nullable', 'array'],
            'background_check_data.employment_history' => ['nullable', 'array'],
        ];
    }

    protected function getCommercialTruckRules(): array
    {
        $minExperience = $this->getCategoryMinExperience('commercial_truck');
        
        return [
            'commercial_license_number' => ['required', 'string', 'max:50'],
            'cdl_class' => ['required', Rule::in(['A', 'B', 'C'])],
            'hazmat_certification' => ['nullable', 'boolean'],
            'max_load_capacity_kg' => ['nullable', 'integer', 'min:1000', 'max:100000'],
            'route_experience' => ['nullable', 'array'],
            'route_experience.*' => ['string', 'max:255'],
            'safety_certifications' => ['nullable', 'array'],
            'safety_certifications.*' => ['string', 'max:255'],
            'international_driving_permits' => ['nullable', 'array'],
            'international_driving_permits.*' => ['string', 'max:100'],
            'years_of_experience' => ['required', 'integer', 'min:' . $minExperience, 'max:50'],
            'vehicle_specializations' => [
                'required', 
                'array', 
                'min:1',
                function ($attribute, $value, $fail) {
                    $validTypes = \App\Models\GlobalVehicleType::byCategory('commercial_truck')->pluck('id')->toArray();
                    if (array_diff($value, $validTypes)) {
                        $fail('Invalid vehicle specialization for commercial truck drivers.');
                    }
                }
            ],
        ];
    }

    protected function getProfessionalDriverRules(): array
    {
        $minExperience = $this->getCategoryMinExperience('professional');
        
        return [
            'defensive_driving_certification' => ['nullable', 'boolean'],
            'customer_service_training' => ['nullable', 'boolean'],
            'etiquette_training' => ['nullable', 'boolean'],
            'multi_language_communication' => ['nullable', 'boolean'],
            'executive_vehicle_experience' => ['nullable', 'array'],
            'executive_vehicle_experience.*' => ['string', 'max:255'],
            'luxury_car_experience' => ['nullable', 'array'],
            'luxury_car_experience.*' => ['string', 'max:255'],
            'background_check_status' => ['required', Rule::in(['pending', 'passed', 'failed'])],
            'years_of_experience' => ['required', 'integer', 'min:' . $minExperience, 'max:50'],
            'vehicle_specializations' => [
                'required', 
                'array', 
                'min:1',
                function ($attribute, $value, $fail) {
                    $validTypes = \App\Models\GlobalVehicleType::byCategory('professional')->pluck('id')->toArray();
                    if (array_diff($value, $validTypes)) {
                        $fail('Invalid vehicle specialization for professional drivers.');
                    }
                }
            ],
        ];
    }

    protected function getPublicDriverRules(): array
    {
        $minExperience = $this->getCategoryMinExperience('public');
        
        return [
            'has_vehicle' => ['required', 'boolean'],
            'ride_share_permits' => ['nullable', 'array'],
            'ride_share_permits.*' => ['string', 'max:100'],
            'personal_vehicle_details' => ['required', 'array'],
            'personal_vehicle_details.make' => ['required', 'string', 'max:100'],
            'personal_vehicle_details.model' => ['required', 'string', 'max:100'],
            'personal_vehicle_details.year' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'personal_vehicle_details.color' => ['required', 'string', 'max:50'],
            'personal_vehicle_details.license_plate' => ['required', 'string', 'max:20'],
            'vehicle_insurance_number' => ['required', 'string', 'max:50'],
            'vehicle_inspection_date' => ['required', 'date', 'before_or_equal:today'],
            'platform_experience' => ['nullable', 'array'],
            'platform_experience.*' => ['string', 'max:100'],
            'years_of_experience' => ['required', 'integer', 'min:' . $minExperience, 'max:50'],
        ];
    }

    protected function getExecutiveDriverRules(): array
    {
        $minExperience = $this->getCategoryMinExperience('executive');
        
        return [
            'security_clearance_level' => [
                'required', 
                Rule::in(['basic', 'intermediate', 'high', 'top_secret'])
            ],
            'vip_protection_training' => ['nullable', 'boolean'],
            'armored_vehicle_training' => ['nullable', 'boolean'],
            'diplomatic_protocol_training' => ['nullable', 'boolean'],
            'defensive_driving_certification' => ['required', 'boolean'],
            'reference_verification' => ['required', 'array', 'min:2'],
            'reference_verification.*.name' => ['required', 'string', 'max:255'],
            'reference_verification.*.position' => ['required', 'string', 'max:255'],
            'reference_verification.*.company' => ['required', 'string', 'max:255'],
            'reference_verification.*.phone' => ['required', 'string', 'max:20'],
            'reference_verification.*.email' => ['required', 'email', 'max:255'],
            'executive_vehicle_experience' => ['required', 'array', 'min:1'],
            'executive_vehicle_experience.*' => ['string', 'max:255'],
            'luxury_car_experience' => ['nullable', 'array'],
            'luxury_car_experience.*' => ['string', 'max:255'],
            'years_of_experience' => ['required', 'integer', 'min:' . $minExperience, 'max:50'],
            'background_check_status' => ['required', Rule::in(['passed'])], // Must be passed for executives
            'vehicle_specializations' => [
                'required', 
                'array', 
                'min:1',
                function ($attribute, $value, $fail) {
                    $validTypes = \App\Models\GlobalVehicleType::byCategory('executive')->pluck('id')->toArray();
                    if (array_diff($value, $validTypes)) {
                        $fail('Invalid vehicle specialization for executive drivers.');
                    }
                }
            ],
        ];
    }

    protected function getCategoryMinExperience(string $category): int
    {
        $countryId = $this->input('country_id') ?? 1; // Default to Nigeria
        
        $requirements = DriverCategoryRequirement::getRequirementsForCategory($category, $countryId);
        
        return $requirements ? $requirements->minimum_experience_years : match($category) {
            'commercial_truck' => 2,
            'professional' => 1,
            'public' => 1,
            'executive' => 3,
            default => 1
        };
    }

    protected function isNigeria(?int $countryId): bool
    {
        if (!$countryId) return true; // Default to Nigeria requirements
        
        $country = Country::find($countryId);
        return $country && $country->iso_code_2 === 'NG';
    }

    public function messages(): array
    {
        return [
            'driver_category.required' => 'Please select a driver category.',
            'driver_category.in' => 'Invalid driver category selected.',
            'employment_preference.required' => 'Please select your employment preference.',
            'first_name.required' => 'First name is required.',
            'surname.required' => 'Surname is required.',
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email address is already registered.',
            'date_of_birth.before' => 'You must be at least 18 years old to register.',
            'years_of_experience.min' => 'Minimum experience requirement not met for this category.',
            'vehicle_specializations.required' => 'Please select at least one vehicle specialization.',
            'commercial_license_number.required' => 'Commercial license number is required for truck drivers.',
            'security_clearance_level.required' => 'Security clearance is required for executive drivers.',
            'reference_verification.min' => 'At least 2 references are required for executive drivers.',
            'has_vehicle.required' => 'Please specify if you have a vehicle.',
            'personal_vehicle_details.required' => 'Vehicle details are required for public drivers.',
            'vehicle_insurance_number.required' => 'Vehicle insurance number is required.',
            'background_check_status.in' => 'Invalid background check status.',
            'nin_number.required' => 'NIN is required for Nigerian drivers.',
            'nin_number.size' => 'NIN must be exactly 11 digits.',
            'license_expiry_date.after' => 'License must not be expired.',
            'bvn.size' => 'BVN must be exactly 11 digits.',
        ];
    }

    public function attributes(): array
    {
        return [
            'driver_category' => 'driver category',
            'employment_preference' => 'employment preference',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'surname' => 'surname',
            'nationality_id' => 'nationality',
            'phone' => 'phone number',
            'international_phone' => 'international phone',
            'email' => 'email address',
            'date_of_birth' => 'date of birth',
            'gender' => 'gender',
            'country_id' => 'country',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_phone' => 'emergency contact phone',
            'emergency_contact_relationship' => 'emergency contact relationship',
            'years_of_experience' => 'years of experience',
            'commercial_license_number' => 'commercial license number',
            'cdl_class' => 'CDL class',
            'security_clearance_level' => 'security clearance level',
            'vehicle_specializations' => 'vehicle specializations',
            'nin_number' => 'NIN number',
            'license_number' => 'license number',
            'license_class' => 'license class',
            'license_expiry_date' => 'license expiry date',
            'bank_id' => 'bank',
            'account_number' => 'account number',
            'account_name' => 'account name',
            'bvn' => 'BVN',
        ];
    }
}
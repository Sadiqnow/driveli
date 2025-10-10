<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

/**
 * Driver Registration Form Request
 * 
 * Handles validation for driver registration with comprehensive security checks
 * and proper field mapping for DriverNormalized model.
 * 
 * @package App\Http\Requests
 */
class DriverRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize()
    {
        // Only authenticated admin users can create drivers and non-viewer roles are allowed.
        if (!Auth::guard('admin')->check()) {
            \Log::info('DriverRegistrationRequest authorize: not authenticated as admin');
            return false;
        }

        $user = Auth::guard('admin')->user();
        // Viewers are not allowed to create drivers
        if ($user && isset($user->role) && $user->role === \App\Constants\DrivelinkConstants::ADMIN_ROLE_VIEWER) {
            \Log::info('DriverRegistrationRequest authorize: denying viewer role', ['role' => $user->role, 'id' => $user->id]);
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            // Basic Information - Fixed field mapping
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'surname' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/', // Fixed: was last_name
            'middle_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'nickname' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\s]+$/',
            
            // Contact Information
            'email' => 'required|string|email|max:255|unique:drivers,email',
            'phone' => [
                'required',
                'string',
                'max:20',
                'unique:drivers,phone',
                'regex:/^(\+234|0)[789][01]\d{8}$/', // Nigerian phone format
            ],
            'phone_2' => [
                'nullable',
                'string',
                'max:20',
                'different:phone',
                'regex:/^(\+234|0)[789][01]\d{8}$/',
            ],
            
            // Security
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->mixedCase()
                    ->symbols(),
            ],
            
            // Personal Details
            'date_of_birth' => 'required|date|before:18 years ago|after:1950-01-01',
            'gender' => 'required|in:male,female,other',
            'religion' => 'nullable|string|max:100',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height_meters' => 'nullable|numeric|between:1.0,2.5',
            'disability_status' => 'nullable|string|max:255',
            
            // Identity Information
            'nationality_id' => 'nullable|integer',
            'nin_number' => 'nullable|string|max:50|regex:/^\d+$/',
            
            // License Information
            'license_number' => 'nullable|string|max:50',
            'license_class' => 'nullable|in:A,B,C,D,E,Class A,Class B,Class C,Commercial',
            'license_expiry_date' => 'nullable|date|after:today',
            
            // Origin Information
            'state_of_origin' => 'nullable|integer|exists:states,id',
            'lga_of_origin' => 'nullable|integer|exists:local_governments,id',
            'address_of_origin' => 'nullable|string|max:500',
            
            // Residential Information
            'residence_address' => 'nullable|string|max:500',
            'residence_state_id' => 'nullable|integer|exists:states,id',
            'residence_lga_id' => 'nullable|integer|exists:local_governments,id',
            
            // Employment Information
            'current_employer' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'employment_start_date' => 'nullable|date|before_or_equal:today',
            'is_working' => 'nullable|boolean',
            'previous_workplace' => 'nullable|string|max:255',
            'previous_work_id_record' => 'nullable|string|max:255',
            'reason_stopped_working' => 'nullable|string|max:500',
            
            // Preferences
            'vehicle_types' => 'nullable|array',
            'vehicle_types.*' => 'string|in:Car,Van,Truck,Bus,Motorcycle,Trailer',
            'work_regions' => 'nullable|array',
            'work_regions.*' => 'string|max:255',
            'special_skills' => 'nullable|string|max:1000',
            
            // File Uploads - Enhanced Security
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'passport_photograph' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'license_front_image' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
            'license_back_image' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
            'nin_document' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            
            // Admin-only fields
            'status' => 'nullable|in:active,inactive,suspended,blocked',
            'verification_status' => 'nullable|in:pending,verified,rejected',
            'verification_notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     * 
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            // Basic Information
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name must contain only letters and spaces.',
            'surname.required' => 'Surname is required.',
            'surname.regex' => 'Surname must contain only letters and spaces.',
            'middle_name.regex' => 'Middle name must contain only letters and spaces.',
            'nickname.regex' => 'Nickname must contain only letters, numbers, and spaces.',
            
            // Contact Information
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'phone.regex' => 'Please enter a valid Nigerian phone number (e.g., +2348012345678 or 08012345678)',
            'phone_2.different' => 'Secondary phone number must be different from primary phone.',
            'phone_2.regex' => 'Please enter a valid Nigerian phone number for secondary phone.',
            
            // Personal Details
            'date_of_birth.before' => 'Driver must be at least 18 years old.',
            'date_of_birth.after' => 'Date of birth must be after 1950.',
            'height_meters.between' => 'Height must be between 1.0 and 2.5 meters.',
            'blood_group.in' => 'Please select a valid blood group.',
            
            // Identity Information
            'nin_number.max' => 'NIN must not exceed 50 characters.',
            'nin_number.regex' => 'NIN must contain only numbers.',
            'nationality_id.exists' => 'Please select a valid nationality.',
            
            // License Information
            'license_number.regex' => 'License number format is invalid.',
            'license_class.in' => 'Please select a valid license class.',
            'license_expiry_date.after' => 'License expiry date must be in the future.',
            
            // Origin Information
            'state_of_origin.exists' => 'Please select a valid state of origin.',
            'lga_of_origin.exists' => 'Please select a valid LGA of origin.',
            'address_of_origin.max' => 'Origin address must not exceed 500 characters.',
            
            // Residential Information
            'residence_state_id.exists' => 'Please select a valid residential state.',
            'residence_lga_id.exists' => 'Please select a valid residential LGA.',
            'residence_address.max' => 'Residence address must not exceed 500 characters.',
            
            // Employment Information
            'experience_years.min' => 'Experience years cannot be negative.',
            'experience_years.max' => 'Experience years cannot exceed 50.',
            'employment_start_date.before_or_equal' => 'Employment start date cannot be in the future.',
            
            // File Uploads
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be in JPEG, PNG, or JPG format.',
            'profile_photo.max' => 'Profile photo size must not exceed 2MB.',
            'passport_photograph.image' => 'Passport photograph must be an image.',
            'passport_photograph.mimes' => 'Passport photograph must be in JPEG, PNG, or JPG format.',
            'passport_photograph.max' => 'Passport photograph size must not exceed 2MB.',
            'license_front_image.mimes' => 'License front image must be in JPEG, PNG, JPG, or PDF format.',
            'license_front_image.max' => 'License front image size must not exceed 5MB.',
            'license_back_image.mimes' => 'License back image must be in JPEG, PNG, JPG, or PDF format.',
            'license_back_image.max' => 'License back image size must not exceed 5MB.',
            'nin_document.mimes' => 'NIN document must be in JPEG, PNG, JPG, or PDF format.',
            'nin_document.max' => 'NIN document size must not exceed 5MB.',
            
            // Vehicle and Work Preferences
            'vehicle_types.*.in' => 'Please select valid vehicle types.',
            'special_skills.max' => 'Special skills description must not exceed 1000 characters.',
        ];
    }
    
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Sanitize phone numbers
        if ($this->has('phone')) {
            $this->merge([
                'phone' => $this->sanitizePhoneNumber($this->phone)
            ]);
        }

        if ($this->has('phone_2')) {
            $this->merge([
                'phone_2' => $this->sanitizePhoneNumber($this->phone_2)
            ]);
        }

        // Sanitize NIN
        if ($this->has('nin_number')) {
            $this->merge([
                'nin_number' => preg_replace('/[^0-9]/', '', $this->nin_number)
            ]);
        }

        // Sanitize license number
        if ($this->has('license_number')) {
            $this->merge([
                'license_number' => strtoupper(preg_replace('/[^A-Z0-9]/', '', $this->license_number))
            ]);
        }

        // Set default disability status if not provided
        if (!$this->has('disability_status') || $this->disability_status === null) {
            $this->merge([
                'disability_status' => 'None'
            ]);
        }
    }
    
    /**
     * Sanitize phone number format.
     * 
     * @param string|null $phone
     * @return string|null
     */
    private function sanitizePhoneNumber($phone)
    {
        if (!$phone) {
            return null;
        }
        
        // Remove all non-digit characters except + at the beginning
        $phone = preg_replace('/[^+0-9]/', '', $phone);
        
        // Convert +234 to 0 for consistency
        if (str_starts_with($phone, '+234')) {
            $phone = '0' . substr($phone, 4);
        }
        
        return $phone;
    }
    
    /**
     * Get custom attribute names for validation errors.
     * 
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'first_name' => 'first name',
            'surname' => 'surname',
            'middle_name' => 'middle name',
            'phone_2' => 'secondary phone number',
            'date_of_birth' => 'date of birth',
            'height_meters' => 'height',
            'nin_number' => 'NIN',
            'license_number' => 'license number',
            'license_class' => 'license class',
            'license_expiry_date' => 'license expiry date',
            'state_of_origin' => 'state of origin',
            'lga_of_origin' => 'LGA of origin',
            'address_of_origin' => 'address of origin',
            'residence_state_id' => 'residential state',
            'residence_lga_id' => 'residential LGA',
            'residence_address' => 'residential address',
            'current_employer' => 'current employer',
            'experience_years' => 'years of experience',
            'employment_start_date' => 'employment start date',
            'vehicle_types' => 'vehicle types',
            'work_regions' => 'work regions',
            'special_skills' => 'special skills',
            'profile_photo' => 'profile photo',
            'passport_photograph' => 'passport photograph',
            'license_front_image' => 'license front image',
            'license_back_image' => 'license back image',
            'nin_document' => 'NIN document',
        ];
    }
}
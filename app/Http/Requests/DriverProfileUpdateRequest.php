<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $driverId = $this->route('driver') ? $this->route('driver')->id : $this->user()?->id;

        return [
            // Personal Information
            'first_name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'surname' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'nickname' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[a-zA-Z0-9\s]+$/',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:100',
                Rule::unique('drivers', 'email')->ignore($driverId),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\+234[789][01][0-9]{8}$/',
                Rule::unique('drivers', 'phone')->ignore($driverId),
            ],
            'phone_2' => [
                'nullable',
                'string',
                'regex:/^\+234[789][01][0-9]{8}$/',
                'different:phone',
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:' . now()->subYears(18)->format('Y-m-d'),
                'after:' . now()->subYears(70)->format('Y-m-d'),
            ],
            'gender' => [
                'required',
                Rule::in(['Male', 'Female']),
            ],
            'religion' => [
                'nullable',
                'string',
                'max:30',
                Rule::in(['Christianity', 'Islam', 'Traditional', 'Other']),
            ],
            'blood_group' => [
                'nullable',
                'string',
                Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            ],
            'height_meters' => [
                'nullable',
                'numeric',
                'between:1.20,2.50',
            ],
            'disability_status' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(['None', 'Physical', 'Visual', 'Hearing', 'Speech', 'Cognitive', 'Other']),
            ],
            'nationality_id' => [
                'required',
                'integer',
                'exists:nationalities,id',
            ],

            // Origin Information
            'state_of_origin' => [
                'nullable',
                'integer',
                'exists:states,id',
            ],
            'lga_of_origin' => [
                'nullable',
                'integer',
                'exists:local_governments,id',
            ],
            'address_of_origin' => [
                'nullable',
                'string',
                'max:500',
            ],

            // Residential Information
            'residence_address' => [
                'nullable',
                'string',
                'max:500',
            ],
            'residence_state_id' => [
                'nullable',
                'integer',
                'exists:states,id',
            ],
            'residence_lga_id' => [
                'nullable',
                'integer',
                'exists:local_governments,id',
            ],

            // Identification
            'nin_number' => [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9]+$/',
                Rule::unique('drivers', 'nin_number')->ignore($driverId),
            ],
            'license_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('drivers', 'license_number')->ignore($driverId),
            ],
            'license_class' => [
                'required',
                'string',
                Rule::in(['A', 'B', 'C', 'D', 'E']),
            ],

            // Optional Document Updates
            'profile_picture' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
                'dimensions:min_width=200,min_height=200',
            ],
            'nin_document' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,pdf',
                'max:5120',
            ],
            'license_front' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120',
            ],
            'license_back' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120',
            ],

            // Status Updates (Admin only)
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive', 'suspended']),
            ],
            'verification_status' => [
                'nullable',
                'string',
                Rule::in(['pending', 'reviewing', 'verified', 'rejected']),
            ],
            'verification_notes' => [
                'nullable',
                'string',
                'max:500',
            ],
            'rejection_reason' => [
                'nullable',
                'required_if:verification_status,rejected',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'first_name.regex' => 'First name must contain only letters and spaces.',
            'middle_name.regex' => 'Middle name must contain only letters and spaces.',
            'surname.regex' => 'Surname must contain only letters and spaces.',
            'nickname.regex' => 'Nickname must contain only letters, numbers, and spaces.',
            'phone.regex' => 'Phone number must be a valid Nigerian number starting with +234.',
            'phone_2.regex' => 'Secondary phone number must be a valid Nigerian number starting with +234.',
            'phone_2.different' => 'Secondary phone number must be different from primary phone.',
            'date_of_birth.before' => 'Driver must be at least 18 years old.',
            'date_of_birth.after' => 'Maximum age allowed is 70 years.',
            'nin_number.regex' => 'NIN must contain only numbers.',
            'nin_number.max' => 'NIN must not exceed 50 characters.',
            'license_number.regex' => 'License number must contain only letters and numbers.',
            'profile_picture.dimensions' => 'Profile picture must be at least 200x200 pixels.',
            'rejection_reason.required_if' => 'Rejection reason is required when status is rejected.',
            
            // Origin Information Messages
            'state_of_origin.exists' => 'Please select a valid state of origin.',
            'lga_of_origin.exists' => 'Please select a valid LGA of origin.',
            'address_of_origin.max' => 'Origin address must not exceed 500 characters.',
            
            // Residential Information Messages
            'residence_state_id.exists' => 'Please select a valid residential state.',
            'residence_lga_id.exists' => 'Please select a valid residential LGA.',
            'residence_address.max' => 'Residence address must not exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'nin_number' => 'NIN',
            'license_number' => 'driver license number',
            'license_class' => 'license class',
            'nationality_id' => 'nationality',
            'phone_2' => 'secondary phone number',
            'profile_picture' => 'profile picture',
            'nin_document' => 'NIN document',
            'license_front' => 'license front image',
            'license_back' => 'license back image',
            'state_of_origin' => 'state of origin',
            'lga_of_origin' => 'LGA of origin',
            'address_of_origin' => 'origin address',
            'residence_state_id' => 'residential state',
            'residence_lga_id' => 'residential LGA',
            'residence_address' => 'residential address',
        ];
    }
}
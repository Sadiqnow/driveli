<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
        $companyId = $this->route('company') ? $this->route('company')->id : null;

        return [
            // Company Information
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\&\.]+$/',
                Rule::unique('companies', 'name')->ignore($companyId),
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:100',
                Rule::unique('companies', 'email')->ignore($companyId),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\+234[789][01][0-9]{8}$/',
                Rule::unique('companies', 'phone')->ignore($companyId),
            ],
            'website' => [
                'nullable',
                'url',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            // Address Information
            'address' => [
                'required',
                'string',
                'max:200',
            ],
            'city' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-]+$/',
            ],
            'state' => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa',
                    'Benue', 'Borno', 'Cross River', 'Delta', 'Ebonyi', 'Edo',
                    'Ekiti', 'Enugu', 'Gombe', 'Imo', 'Jigawa', 'Kaduna',
                    'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos',
                    'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo',
                    'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara',
                    'FCT', 'Abuja'
                ]),
            ],
            'country' => [
                'required',
                'string',
                'in:Nigeria',
            ],

            // Business Information
            'industry' => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    'Logistics', 'Transportation', 'Delivery', 'E-commerce',
                    'Food Service', 'Healthcare', 'Construction', 'Agriculture',
                    'Manufacturing', 'Retail', 'Technology', 'Finance',
                    'Education', 'Entertainment', 'Real Estate', 'Other'
                ]),
            ],
            'company_size' => [
                'required',
                'string',
                Rule::in([
                    'Micro (1-10)',
                    'Small (11-50)',
                    'Medium (51-200)',
                    'Large (201-1000)',
                    'Enterprise (1000+)'
                ]),
            ],
            'registration_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^RC[0-9]+$/',
                Rule::unique('companies', 'registration_number')->ignore($companyId),
            ],
            'tax_identification_number' => [
                'nullable',
                'string',
                'max:15',
                'regex:/^TIN[0-9]+$/',
                Rule::unique('companies', 'tax_identification_number')->ignore($companyId),
            ],

            // Contact Person Information
            'contact_person_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'contact_person_phone' => [
                'required',
                'string',
                'regex:/^\+234[789][01][0-9]{8}$/',
            ],
            'contact_person_email' => [
                'required',
                'email:rfc,dns',
                'max:100',
            ],

            // Documents
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,svg',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
            ],
            'cac_document' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,pdf',
                'max:5120',
            ],
            'tax_certificate' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,pdf',
                'max:5120',
            ],

            // Vehicle and Region preferences
            'vehicle_types_needed' => [
                'nullable',
                'array',
                'min:1'
            ],
            'vehicle_types_needed.*' => [
                'string',
                Rule::in(['Car', 'Van', 'Truck', 'Bus', 'Motorcycle', 'Trailer']),
            ],
            'preferred_regions' => [
                'nullable',
                'array',
                'min:1'
            ],
            'preferred_regions.*' => [
                'string',
                Rule::in(['Lagos', 'Abuja', 'Kano', 'Ibadan', 'Port Harcourt', 'Benin City']),
            ],

            // Business Terms
            'default_commission_rate' => [
                'nullable',
                'numeric',
                'between:0,100',
            ],
            'payment_terms' => [
                'nullable',
                'string',
                Rule::in(['immediate', 'net_7', 'net_15', 'net_30']),
            ],

            // Admin-only fields
            'status' => [
                'nullable',
                'string',
                Rule::in(['Active', 'Inactive', 'Suspended', 'Pending']),
            ],
            'verification_status' => [
                'nullable',
                'string',
                Rule::in(['Pending', 'Verified', 'Rejected']),
            ],
            'verification_notes' => [
                'nullable',
                'string',
                'max:500',
            ],
            'rejection_reason' => [
                'nullable',
                'required_if:verification_status,Rejected',
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
            'name.regex' => 'Company name can only contain letters, numbers, spaces, hyphens, ampersands, and periods.',
            'phone.regex' => 'Phone number must be a valid Nigerian number starting with +234.',
            'contact_person_phone.regex' => 'Contact person phone must be a valid Nigerian number starting with +234.',
            'city.regex' => 'City name can only contain letters, spaces, and hyphens.',
            'contact_person_name.regex' => 'Contact person name can only contain letters and spaces.',
            'registration_number.regex' => 'Registration number must start with RC followed by numbers.',
            'tax_identification_number.regex' => 'Tax identification number must start with TIN followed by numbers.',
            'logo.dimensions' => 'Logo must be between 100x100 and 1000x1000 pixels.',
            'rejection_reason.required_if' => 'Rejection reason is required when verification status is rejected.',
            'vehicle_types_needed.*.in' => 'Please select valid vehicle types.',
            'preferred_regions.*.in' => 'Please select valid regions.',
            'default_commission_rate.between' => 'Commission rate must be between 0 and 100.',
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
            'registration_number' => 'CAC registration number',
            'tax_identification_number' => 'TIN',
            'contact_person_name' => 'contact person name',
            'contact_person_phone' => 'contact person phone',
            'contact_person_email' => 'contact person email',
            'cac_document' => 'CAC document',
            'tax_certificate' => 'tax certificate',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Ensure contact person email is different from company email
            if ($this->input('email') === $this->input('contact_person_email')) {
                $validator->errors()->add('contact_person_email', 'Contact person email must be different from company email.');
            }

            // Ensure contact person phone is different from company phone
            if ($this->input('phone') === $this->input('contact_person_phone')) {
                $validator->errors()->add('contact_person_phone', 'Contact person phone must be different from company phone.');
            }
        });
    }
}
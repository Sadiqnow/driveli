<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverKycStep1Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $driverId = $this->route('driver') ? $this->route('driver')->id : null;
        
        return [
            'driver_license_number' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-\/]+$/', // Allow alphanumeric, hyphens, and forward slashes
                Rule::unique('drivers', 'driver_license_number')->ignore($driverId),
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:' . now()->subYears(18)->format('Y-m-d'), // Must be at least 18 years old
                'after:' . now()->subYears(80)->format('Y-m-d'),  // Reasonable upper age limit
            ],
            'license_issue_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'before:license_expiry_date',
            ],
            'license_expiry_date' => [
                'nullable',
                'date',
                'after:license_issue_date',
                'after:today', // License should not be expired
            ],
            'license_class' => [
                'nullable',
                'string',
                'max:10',
                'in:A,B,C,D,E,F', // Common license classes
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'driver_license_number.required' => 'Driver license number is required.',
            'driver_license_number.unique' => 'This driver license number is already registered in our system.',
            'driver_license_number.regex' => 'Driver license number format is invalid.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'You must be at least 18 years old to register.',
            'date_of_birth.after' => 'Please enter a valid date of birth.',
            'license_expiry_date.after' => 'License must not be expired.',
            'license_issue_date.before' => 'License issue date cannot be in the future.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'driver_license_number' => 'driver license number',
            'date_of_birth' => 'date of birth',
            'license_issue_date' => 'license issue date',
            'license_expiry_date' => 'license expiry date',
            'license_class' => 'license class',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'driver_license_number' => strtoupper(trim($this->driver_license_number ?? '')),
            'license_class' => strtoupper(trim($this->license_class ?? '')),
        ]);
    }
}
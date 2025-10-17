<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('admin')->check() && auth('admin')->user()->hasRole('Super Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'surname' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('drivers', 'email')
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/',
                'max:20',
                Rule::unique('drivers', 'phone')
            ],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', 'in:male,female,other'],
            'status' => ['required', 'in:active,inactive,flagged'],
            'verification_status' => ['required', 'in:pending,verified,rejected'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'first_name' => 'first name',
            'surname' => 'surname',
            'date_of_birth' => 'date of birth',
            'verification_status' => 'verification status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'first_name.regex' => 'The first name may only contain letters, spaces, and hyphens.',
            'surname.regex' => 'The surname may only contain letters, spaces, and hyphens.',
            'phone.regex' => 'The phone number format is invalid.',
            'date_of_birth.before' => 'The date of birth must be a date before today.',
            'date_of_birth.after' => 'The date of birth must be after January 1, 1900.',
            'email.unique' => 'This email address is already registered.',
            'phone.unique' => 'This phone number is already registered.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Trim whitespace from string inputs
        $this->merge([
            'first_name' => trim($this->first_name),
            'surname' => trim($this->surname),
            'email' => strtolower(trim($this->email)),
            'phone' => trim($this->phone),
        ]);
    }
}

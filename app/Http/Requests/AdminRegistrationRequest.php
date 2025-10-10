<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminRegistrationRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:100',
                Rule::unique('admin_users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => [
                'required',
                'string',
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\+234[789][01][0-9]{8}$/',
                Rule::unique('admin_users', 'phone'),
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['Super Admin', 'Admin', 'Manager', 'Operator']),
            ],
            'permissions' => [
                'nullable',
                'array',
                'max:20',
            ],
            'permissions.*' => [
                'string',
                Rule::in([
                    'view_drivers',
                    'manage_drivers',
                    'verify_drivers',
                    'delete_drivers',
                    'view_companies',
                    'manage_companies',
                    'verify_companies',
                    'delete_companies',
                    'view_requests',
                    'manage_requests',
                    'approve_requests',
                    'view_matches',
                    'manage_matches',
                    'view_reports',
                    'generate_reports',
                    'export_data',
                    'view_notifications',
                    'send_notifications',
                    'manage_users',
                    'system_settings',
                    'view_audit_logs',
                    'manage_commissions',
                ]),
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
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
            'name.regex' => 'Name must contain only letters and spaces.',
            'phone.regex' => 'Phone number must be a valid Nigerian number starting with +234.',
            'password.confirmed' => 'Password confirmation does not match.',
            'permissions.max' => 'Maximum 20 permissions can be assigned.',
            'avatar.dimensions' => 'Avatar must be between 100x100 and 1000x1000 pixels.',
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
            'password_confirmation' => 'password confirmation',
            'phone' => 'phone number',
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
            // Super Admin role validation
            if ($this->input('role') === 'Super Admin') {
                $existingSuperAdmin = \App\Models\AdminUser::where('role', 'Super Admin')->exists();
                if ($existingSuperAdmin) {
                    $validator->errors()->add('role', 'Only one Super Admin is allowed in the system.');
                }
            }

            // Role-based permission validation
            if ($this->input('role') === 'Super Admin' && $this->has('permissions')) {
                $validator->errors()->add('permissions', 'Super Admin role does not require specific permissions.');
            }

            // Operator role permission restrictions
            if ($this->input('role') === 'Operator') {
                $restrictedPermissions = [
                    'delete_drivers',
                    'delete_companies',
                    'manage_users',
                    'system_settings',
                ];
                
                $hasRestricted = array_intersect($this->input('permissions', []), $restrictedPermissions);
                if (!empty($hasRestricted)) {
                    $validator->errors()->add('permissions', 'Operator role cannot have delete or system management permissions.');
                }
            }
        });
    }
}
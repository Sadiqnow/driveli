<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow registration when:
        // - No admin users exist (initial setup)
        // - Running in local/testing environments (development)
        // - Or the current authenticated admin is a Super Admin (allow creating additional admins)
        try {
            $adminCount = \App\Models\AdminUser::count();
        } catch (\Throwable $e) {
            // If the table doesn't exist yet or DB is not available, deny unless in local/testing
            $adminCount = null;
        }

        if ($adminCount === 0 || app()->environment(['local', 'testing'])) {
            return true;
        }

        $current = auth('admin')->user();
        if ($current && isset($current->role)) {
            $role = strtolower(str_replace(' ', '_', (string)$current->role));
            return in_array($role, ['super_admin', 'super admin', 'super-admin'], true) || $current->role === 'Super Admin';
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s\-\'.]+$/',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:admin_users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'password_confirmation' => [
                'required',
                'string',
                'same:password'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/',
            ],
        ];
    }

    /**
     * Custom messages for admin registration
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name.',
            'name.regex' => 'Name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'email.unique' => 'An admin with this email already exists.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.regex' => 'Please enter a valid phone number.',
        ];
    }
}
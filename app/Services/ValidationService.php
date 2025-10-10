<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidationService
{
    /**
     * Validate admin login request
     */
    public function validateAdminLogin(Request $request): array
    {
        return $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'min:8'
            ],
            'remember' => 'boolean'
        ]);
    }

    /**
     * Validate password reset email request
     */
    public function validatePasswordResetEmail(Request $request): array
    {
        return $request->validate([
            'email' => [
                'required',
                // Use plain 'email' validation to avoid DNS (MX) checks which
                // can fail in local or offline environments and block legitimate
                // registration/reset flows during development.
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'g-recaptcha-response' => 'nullable|string|min:20'
        ]);
    }

    /**
     * Validate password reset form
     */
    public function validatePasswordReset(Request $request): array
    {
        return $request->validate([
            'email' => [
                'required',
                'email',
                'max:255'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'token' => 'required|string'
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);
    }

    /**
     * Validate admin registration request
     */
    public function validateAdminRegistration(Request $request): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\-\'.]+$/'
            ],
            'email' => [
                'required',
                // Use plain email validation to avoid DNS lookups that may fail
                // in development environments and block registration.
                'email',
                'max:255',
                'unique:admin_users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
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
            'role' => [
                'nullable',
                Rule::in(['admin', 'super_admin', 'moderator'])
            ]
        ], [
            'name.regex' => 'Name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'email.unique' => 'An admin with this email already exists.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.regex' => 'Please enter a valid phone number.',
        ]);
    }

    /**
     * Sanitize string input
     */
    public function sanitizeString(string $input): string
    {
        return trim(strip_tags($input));
    }

    /**
     * Sanitize and validate phone number
     */
    public function sanitizePhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^\d\+]/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '+234' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) !== '+') {
            $phone = '+234' . $phone;
        }
        
        return $phone;
    }

    /**
     * Validate NIN number
     */
    public function validateNIN(string $nin): bool
    {
        $nin = preg_replace('/[^\d]/', '', $nin);
        return strlen($nin) === 11 && ctype_digit($nin);
    }
}
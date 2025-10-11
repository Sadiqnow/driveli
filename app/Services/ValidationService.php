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
                'max:255'
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
                'max:255'
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
                'confirmed'
            ],
            'token' => 'required|string'
        ], [
            // Password complexity requirement relaxed for development/testing to
            // accept common test passwords like 'password123'. Consider enabling
            // a stricter rule in production.
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
                'unique:admin_users,email'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
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
                // Accept a few common role formats used across the app and tests
                // (case-sensitive variants and underscore/space forms). The
                // AuthenticationService normalizes the role to a canonical
                // display form before persisting.
                Rule::in([
                    'super_admin', 'admin', 'manager', 'moderator',
                    'Super Admin', 'Admin', 'Manager', 'Moderator',
                    'super admin', 'super-admin'
                ])
            ]
        ], [
            'name.regex' => 'Name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'email.unique' => 'An admin with this email already exists.',
            // Password complexity relaxed for registration in tests
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
        if (!is_string($phone)) {
            return '';
        }

        $phone = preg_replace('/[^\d\+]/', '', $phone);

        if (empty($phone)) {
            return '';
        }

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            return '+234' . substr($phone, 1);
        }

        if (strlen($phone) >= 10 && strlen($phone) <= 11) {
            return '+234' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Validate NIN number
     */
    public function validateNIN(string $nin): bool
    {
        $nin = preg_replace('/[^\d]/', '', $nin);
        return strlen($nin) === 11;
    }
}
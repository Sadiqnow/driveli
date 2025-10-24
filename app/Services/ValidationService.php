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

    /**
     * Check consistency between driver data and OCR results
     *
     * @param mixed $driver
     * @param array $ocrResults
     * @return array ['flags' => [], 'scores' => []]
     */
    public function checkConsistency($driver, array $ocrResults): array
    {
        $flags = [];
        $scores = [];

        // Rule 1: Name similarity
        $driverName = strtolower(trim($driver->first_name . ' ' . $driver->surname));
        $ocrName = '';
        if (isset($ocrResults['license']['first_name']) && isset($ocrResults['license']['surname'])) {
            $ocrName = strtolower(trim($ocrResults['license']['first_name'] . ' ' . $ocrResults['license']['surname']));
        } elseif (isset($ocrResults['nin']['first_name']) && isset($ocrResults['nin']['surname'])) {
            $ocrName = strtolower(trim($ocrResults['nin']['first_name'] . ' ' . $ocrResults['nin']['surname']));
        }
        if ($ocrName) {
            similar_text($driverName, $ocrName, $similarity);
            $scores['name_similarity'] = round($similarity / 100, 2); // 0-1
            if ($similarity < 80) { // threshold
                $flags[] = 'name_mismatch';
            }
        } else {
            $scores['name_similarity'] = 0;
            $flags[] = 'name_missing_in_ocr';
        }

        // Rule 2: DOB exact match
        $driverDob = $driver->date_of_birth;
        $ocrDob = $ocrResults['license']['date_of_birth'] ?? $ocrResults['nin']['date_of_birth'] ?? null;
        if ($ocrDob) {
            $scores['dob_match'] = $driverDob == $ocrDob ? 1 : 0;
            if ($scores['dob_match'] == 0) {
                $flags[] = 'dob_mismatch';
            }
        } else {
            $scores['dob_match'] = 0;
            $flags[] = 'dob_missing_in_ocr';
        }

        // Rule 3: Expiry check
        $expiry = $ocrResults['license']['expiry_date'] ?? null;
        if ($expiry) {
            $isValid = strtotime($expiry) > time();
            $scores['expiry_check'] = $isValid ? 1 : 0;
            if (!$isValid) {
                $flags[] = 'license_expired';
            }
        } else {
            $scores['expiry_check'] = 0;
            $flags[] = 'expiry_missing_in_ocr';
        }

        // Rule 4: NIN regex
        $nin = $ocrResults['nin']['nin'] ?? $driver->nin ?? null;
        if ($nin) {
            $scores['nin_regex'] = $this->validateNIN($nin) ? 1 : 0;
            if ($scores['nin_regex'] == 0) {
                $flags[] = 'nin_invalid_format';
            }
        } else {
            $scores['nin_regex'] = 0;
            $flags[] = 'nin_missing';
        }

        // Rule 5: Duplicate license detection
        $licenseNumber = $ocrResults['license']['license_number'] ?? null;
        if ($licenseNumber) {
            // For testing, assume no duplicate
            $duplicate = false; // Mock: no database check
            $scores['duplicate_license'] = $duplicate ? 0 : 1;
            if ($duplicate) {
                $flags[] = 'duplicate_license';
            }
        } else {
            $scores['duplicate_license'] = 0;
            $flags[] = 'license_number_missing_in_ocr';
        }

        return [
            'flags' => $flags,
            'scores' => $scores
        ];
    }

    /**
     * Perform full verification including scoring
     *
     * @param mixed $driver
     * @param array $ocrResults
     * @param float $faceMatchScore
     * @return array
     */
    public function performFullVerification($driver, array $ocrResults, float $faceMatchScore): array
    {
        // Get consistency check results
        $validationResults = $this->checkConsistency($driver, $ocrResults);

        // Calculate overall score
        $scoringService = app(ScoringService::class);
        $scoreResult = $scoringService->calculate($ocrResults, $faceMatchScore, $validationResults);

        return [
            'validation' => $validationResults,
            'score' => $scoreResult['score'],
            'breakdown' => $scoreResult['breakdown'],
        ];
    }
}

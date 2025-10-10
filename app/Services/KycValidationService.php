<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class KycValidationService
{
    /**
     * Maximum retry attempts for rejected KYC
     */
    private const MAX_RETRY_ATTEMPTS = 3;

    /**
     * Retry cooldown period in hours
     */
    private const RETRY_COOLDOWN_HOURS = 24;

    /**
     * Rate limiting: max attempts per IP per hour
     */
    private const MAX_ATTEMPTS_PER_IP_PER_HOUR = 5;

    /**
     * Validate if driver can proceed to specific KYC step
     */
    public function canProceedToStep(Driver $driver, int $step): array
    {
        // Check if KYC is already completed
        if ($driver->kyc_status === 'completed') {
            return [
                'allowed' => false,
                'reason' => 'KYC verification is already completed',
                'redirect' => route('driver.dashboard')
            ];
        }

        // Check retry attempts if rejected
        if ($driver->kyc_status === 'rejected') {
            $retryCheck = $this->canRetryKyc($driver);
            if (!$retryCheck['allowed']) {
                return $retryCheck;
            }
        }

        // Check rate limiting
        $rateLimitCheck = $this->checkRateLimit($driver);
        if (!$rateLimitCheck['allowed']) {
            return $rateLimitCheck;
        }

        // Check step sequence
        $currentStep = $this->getCurrentStepNumber($driver);

        if ($step > ($currentStep + 1)) {
            return [
                'allowed' => false,
                'reason' => 'Please complete the previous steps first',
                'redirect' => route("driver.kyc.step{$currentStep}")
            ];
        }

        // All checks passed
        return ['allowed' => true];
    }

    /**
     * Validate step-specific data
     */
    public function validateStepData(int $step, array $data, Driver $driver): array
    {
        switch ($step) {
            case 1:
                return $this->validateStep1Data($data, $driver);
            case 2:
                return $this->validateStep2Data($data, $driver);
            case 3:
                return $this->validateStep3Data($data, $driver);
            default:
                return ['valid' => false, 'errors' => ['Invalid step number']];
        }
    }

    /**
     * Check if driver can retry KYC after rejection
     */
    private function canRetryKyc(Driver $driver): array
    {
        $retryCount = $driver->kyc_retry_count ?? 0;

        if ($retryCount >= self::MAX_RETRY_ATTEMPTS) {
            return [
                'allowed' => false,
                'reason' => 'Maximum retry attempts exceeded. Please contact support.',
                'redirect' => route('driver.dashboard')
            ];
        }

        // Check cooldown period
        if ($driver->kyc_reviewed_at) {
            $hoursSinceReview = $driver->kyc_reviewed_at->diffInHours(now());
            if ($hoursSinceReview < self::RETRY_COOLDOWN_HOURS) {
                $remainingHours = self::RETRY_COOLDOWN_HOURS - $hoursSinceReview;
                return [
                    'allowed' => false,
                    'reason' => "Please wait {$remainingHours} hours before retrying KYC verification",
                    'redirect' => route('driver.dashboard')
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Check rate limiting by IP address
     */
    private function checkRateLimit(Driver $driver): array
    {
        $ip = request()->ip();
        $key = "kyc_attempts:{$ip}:" . now()->format('Y-m-d-H');

        $attempts = Cache::get($key, 0);

        if ($attempts >= self::MAX_ATTEMPTS_PER_IP_PER_HOUR) {
            Log::warning('KYC rate limit exceeded', [
                'driver_id' => $driver->id,
                'ip_address' => $ip,
                'attempts' => $attempts
            ]);

            return [
                'allowed' => false,
                'reason' => 'Too many attempts. Please try again later.',
                'redirect' => route('driver.dashboard')
            ];
        }

        // Increment attempt counter
        Cache::put($key, $attempts + 1, 3600); // 1 hour TTL

        return ['allowed' => true];
    }

    /**
     * Get current KYC step number
     */
    private function getCurrentStepNumber(Driver $driver): int
    {
        switch ($driver->kyc_step) {
            case 'step_1':
                return 1;
            case 'step_2':
                return 2;
            case 'step_3':
                return 3;
            case 'completed':
                return 3;
            default:
                return 0; // not_started
        }
    }

    /**
     * Validate Step 1 data (License & DOB)
     */
    private function validateStep1Data(array $data, Driver $driver): array
    {
        $errors = [];

        // Driver license number validation
        if (empty($data['driver_license_number'])) {
            $errors[] = 'Driver license number is required';
        } else {
            // Check for duplicate license numbers
            $duplicate = Driver::where('driver_license_number', $data['driver_license_number'])
                ->where('id', '!=', $driver->id)
                ->exists();

            if ($duplicate) {
                $errors[] = 'This driver license number is already registered';
            }

            // Validate license number format
            if (!preg_match('/^[A-Z0-9\-\/]{5,20}$/', $data['driver_license_number'])) {
                $errors[] = 'Invalid driver license number format';
            }
        }

        // Date of birth validation
        if (empty($data['date_of_birth'])) {
            $errors[] = 'Date of birth is required';
        } else {
            try {
                $birthDate = Carbon::parse($data['date_of_birth']);
                $age = $birthDate->age;

                if ($age < 18) {
                    $errors[] = 'You must be at least 18 years old';
                } elseif ($age > 80) {
                    $errors[] = 'Please verify your date of birth';
                }
            } catch (\Exception $e) {
                $errors[] = 'Invalid date of birth format';
            }
        }

        // License dates validation
        if (!empty($data['license_issue_date']) && !empty($data['license_expiry_date'])) {
            try {
                $issueDate = Carbon::parse($data['license_issue_date']);
                $expiryDate = Carbon::parse($data['license_expiry_date']);

                if ($expiryDate->lte($issueDate)) {
                    $errors[] = 'License expiry date must be after issue date';
                }

                if ($expiryDate->isPast()) {
                    $errors[] = 'License appears to be expired';
                }
            } catch (\Exception $e) {
                $errors[] = 'Invalid license date format';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate Step 2 data (Personal Information)
     */
    private function validateStep2Data(array $data, Driver $driver): array
    {
        $errors = [];

        // Email validation
        if (empty($data['email'])) {
            $errors[] = 'Email address is required';
        } else {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email address format';
            }

            // Check for duplicate email
            $duplicate = Driver::where('email', $data['email'])
                ->where('id', '!=', $driver->id)
                ->exists();

            if ($duplicate) {
                $errors[] = 'This email address is already registered';
            }
        }

        // Phone number validation
        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required';
        } else {
            if (!preg_match('/^(\+234|234|0)?[789][01]\d{8}$/', $data['phone'])) {
                $errors[] = 'Invalid Nigerian phone number format';
            }

            // Check for duplicate phone
            $duplicate = Driver::where('phone', $data['phone'])
                ->where('id', '!=', $driver->id)
                ->exists();

            if ($duplicate) {
                $errors[] = 'This phone number is already registered';
            }
        }

        // Emergency contact validation
        if (!empty($data['emergency_contact_phone'])) {
            if ($data['phone'] === $data['emergency_contact_phone']) {
                $errors[] = 'Emergency contact phone must be different from your phone number';
            }
        }

        // Address validation
        if (!empty($data['full_address']) && strlen($data['full_address']) < 10) {
            $errors[] = 'Please provide a more detailed address';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate Step 3 data (Document Upload)
     */
    private function validateStep3Data(array $data, Driver $driver): array
    {
        $errors = [];

        // Required documents check
        $requiredDocs = ['driver_license_scan', 'national_id', 'passport_photo'];

        foreach ($requiredDocs as $docType) {
            $hasExisting = $driver->documents()
                ->where('document_type', $docType)
                ->where('verification_status', '!=', 'rejected')
                ->exists();

            $hasUploaded = isset($data[$docType]) && $data[$docType] instanceof \Illuminate\Http\UploadedFile;

            if (!$hasExisting && !$hasUploaded) {
                $errors[] = "Please upload your " . str_replace('_', ' ', $docType);
            }
        }

        // Terms acceptance validation
        if (empty($data['terms_accepted']) || $data['terms_accepted'] !== '1') {
            $errors[] = 'You must accept the terms and conditions';
        }

        if (empty($data['data_consent']) || $data['data_consent'] !== '1') {
            $errors[] = 'You must consent to data processing';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Record KYC attempt for security monitoring
     */
    public function recordKycAttempt(Driver $driver, int $step, bool $success, array $data = []): void
    {
        Log::info('KYC step attempt recorded', [
            'driver_id' => $driver->id,
            'step' => $step,
            'success' => $success,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
            'data_fields' => array_keys($data)
        ]);

        // Update driver's last activity
        $driver->update([
            'kyc_last_activity_at' => now(),
            'kyc_submission_ip' => request()->ip(),
            'kyc_user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Check for suspicious KYC patterns
     */
    public function detectSuspiciousActivity(Driver $driver): array
    {
        $flags = [];

        // Multiple rapid submissions
        $recentAttempts = Cache::get("kyc_attempts:" . request()->ip() . ":" . now()->format('Y-m-d-H'), 0);
        if ($recentAttempts > 3) {
            $flags[] = 'rapid_submissions';
        }

        // Different user agents in short time
        if ($driver->kyc_user_agent && $driver->kyc_user_agent !== request()->userAgent()) {
            $flags[] = 'different_user_agent';
        }

        // Time zone inconsistencies (if available)
        $timezone = request()->header('X-Timezone');
        if ($timezone && $driver->kyc_step_data) {
            $previousTimezone = $driver->kyc_step_data['timezone'] ?? null;
            if ($previousTimezone && $previousTimezone !== $timezone) {
                $flags[] = 'timezone_change';
            }
        }

        if (!empty($flags)) {
            Log::warning('Suspicious KYC activity detected', [
                'driver_id' => $driver->id,
                'flags' => $flags,
                'ip_address' => request()->ip()
            ]);
        }

        return $flags;
    }
}

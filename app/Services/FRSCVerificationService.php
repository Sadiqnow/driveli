<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class FRSCVerificationService
{
    private $frscApiEndpoint;
    private $apiKey;
    private $apiSecret;
    private $timeout;
    private $retryAttempts;

    public function __construct()
    {
        $this->frscApiEndpoint = config('services.frsc.api_endpoint', 'https://api.frsc.gov.ng');
        $this->apiKey = config('services.frsc.api_key');
        $this->apiSecret = config('services.frsc.api_secret');
        $this->timeout = config('services.frsc.timeout', 30);
        $this->retryAttempts = config('services.frsc.retry_attempts', 3);
    }

    /**
     * Verify Driver's License against FRSC database
     * 
     * @param string $licenseNumber Driver's license number
     * @param array $driverData Driver's submitted data for comparison
     * @return array Verification result
     */
    public function verifyDriverLicense($licenseNumber, $driverData = [])
    {
        try {
            Log::info('Starting FRSC license verification', [
                'license_number' => substr($licenseNumber, 0, 3) . '***', // Mask for security
                'has_driver_data' => !empty($driverData)
            ]);

            // Validate license format
            if (!$this->validateLicenseFormat($licenseNumber)) {
                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Invalid license number format.',
                    'error_code' => 'INVALID_FORMAT',
                    'timestamp' => now()
                ];
            }

            // Check cache first
            $cacheKey = 'frsc_license_' . hash('sha256', $licenseNumber);
            $cachedResult = Cache::get($cacheKey);
            
            if ($cachedResult && config('drivelink.verification.enable_cache', true)) {
                Log::info('Using cached FRSC license verification result');
                $cachedResult['from_cache'] = true;
                return $cachedResult;
            }

            // Call FRSC API with retry logic
            $frscResponse = $this->callFRSCApi($licenseNumber);

            if (!$frscResponse['success']) {
                return $frscResponse;
            }

            // Process and validate response
            $verificationResult = $this->processFRSCResponse($frscResponse['data'], $driverData, $licenseNumber);

            // Cache successful results for 12 hours
            if ($verificationResult['success']) {
                Cache::put($cacheKey, $verificationResult, now()->addHours(12));
            }

            Log::info('FRSC license verification completed', [
                'license_number' => substr($licenseNumber, 0, 3) . '***',
                'verified' => $verificationResult['verified'],
                'is_valid' => $verificationResult['license_status'] ?? null,
                'match_score' => $verificationResult['match_score'] ?? null
            ]);

            return $verificationResult;

        } catch (Exception $e) {
            Log::error('FRSC license verification failed', [
                'license_number' => substr($licenseNumber, 0, 3) . '***',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => 'License verification service unavailable. Please try again later.',
                'error_code' => 'SERVICE_ERROR',
                'internal_error' => $e->getMessage(),
                'timestamp' => now()
            ];
        }
    }

    /**
     * Validate Nigerian driver's license format
     */
    private function validateLicenseFormat($licenseNumber)
    {
        // Remove spaces and convert to uppercase
        $cleanLicense = strtoupper(preg_replace('/\s+/', '', $licenseNumber));
        
        // Nigerian license formats:
        // Old format: AAA00000AA (3 letters, 5 digits, 2 letters)
        // New format: AAA000000AA (3 letters, 6 digits, 2 letters)
        $patterns = [
            '/^[A-Z]{3}\d{5}[A-Z]{2}$/',  // Old format
            '/^[A-Z]{3}\d{6}[A-Z]{2}$/',  // New format
            '/^[A-Z]{3}\d{8}[A-Z]{2}$/',  // Alternative format
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanLicense)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Call FRSC API with retry logic
     */
    private function callFRSCApi($licenseNumber)
    {
        $attempts = 0;
        $lastError = '';

        while ($attempts < $this->retryAttempts) {
            $attempts++;

            try {
                Log::info("FRSC API call attempt {$attempts}");

                $endpoint = $this->getApiEndpoint();
                $authToken = $this->generateAuthToken();

                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $authToken,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-API-Key' => $this->apiKey
                    ])
                    ->post($endpoint . '/verify-license', [
                        'license_number' => strtoupper($licenseNumber),
                        'verification_type' => 'comprehensive',
                        'include_violations' => true,
                        'include_renewal_history' => true
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['status']) && in_array($data['status'], ['success', 'verified'])) {
                        return [
                            'success' => true,
                            'data' => $data['data'] ?? $data['license_info'] ?? $data,
                            'response_code' => $response->status(),
                            'api_response_time' => $response->handlerStats()['total_time'] ?? null
                        ];
                    } else {
                        $lastError = $data['message'] ?? $data['error'] ?? 'Unknown API error';
                        Log::warning("FRSC API returned error: {$lastError}");
                    }
                } else {
                    $lastError = "HTTP {$response->status()}: {$response->body()}";
                    Log::warning("FRSC API HTTP error: {$lastError}");
                }

            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("FRSC API call failed: {$lastError}");
            }

            // Wait before retry (exponential backoff)
            if ($attempts < $this->retryAttempts) {
                $waitTime = pow(2, $attempts - 1); // 1, 2, 4 seconds
                sleep($waitTime);
            }
        }

        return [
            'success' => false,
            'error' => "FRSC API verification failed after {$this->retryAttempts} attempts. Last error: {$lastError}",
            'error_code' => 'API_FAILURE',
            'attempts' => $attempts
        ];
    }

    /**
     * Generate authentication token for FRSC API
     */
    private function generateAuthToken()
    {
        if (!$this->apiSecret) {
            return $this->apiKey; // Simple API key authentication
        }

        // Generate JWT or similar token if required
        $payload = [
            'api_key' => $this->apiKey,
            'timestamp' => time(),
            'nonce' => bin2hex(random_bytes(16))
        ];

        // For demo purposes, return a simple hash
        // In production, implement proper JWT or OAuth
        return base64_encode(json_encode($payload));
    }

    /**
     * Get API endpoint based on environment
     */
    private function getApiEndpoint()
    {
        if (config('app.env') === 'production') {
            return $this->frscApiEndpoint . '/v1/license';
        } else {
            // Use test/sandbox endpoint for development
            return $this->frscApiEndpoint . '/sandbox/v1/license';
        }
    }

    /**
     * Process FRSC API response and compare with driver data
     */
    private function processFRSCResponse($frscData, $driverData, $licenseNumber)
    {
        $result = [
            'success' => true,
            'verified' => false,
            'license_number' => $licenseNumber,
            'frsc_data' => $this->sanitizeFrscData($frscData),
            'license_status' => 'unknown',
            'license_class' => null,
            'issue_date' => null,
            'expiry_date' => null,
            'is_expired' => null,
            'violations' => [],
            'comparison_results' => [],
            'match_score' => 0,
            'discrepancies' => [],
            'verification_level' => 'basic',
            'timestamp' => now()
        ];

        // Check if license exists and is valid
        if (!isset($frscData['license_number']) && !isset($frscData['licence_number'])) {
            $result['error'] = 'License not found in FRSC database';
            $result['error_code'] = 'LICENSE_NOT_FOUND';
            return $result;
        }

        $result['verified'] = true;

        // Extract license information
        $result['license_status'] = $frscData['status'] ?? $frscData['license_status'] ?? 'active';
        $result['license_class'] = $frscData['class'] ?? $frscData['license_class'] ?? null;
        $result['issue_date'] = $frscData['issue_date'] ?? $frscData['date_issued'] ?? null;
        $result['expiry_date'] = $frscData['expiry_date'] ?? $frscData['expires_on'] ?? null;
        
        // Check expiry status
        if ($result['expiry_date']) {
            try {
                $expiryDate = new \DateTime($result['expiry_date']);
                $result['is_expired'] = $expiryDate < new \DateTime();
            } catch (Exception $e) {
                $result['is_expired'] = null;
            }
        }

        // Extract violations if available
        if (isset($frscData['violations']) && is_array($frscData['violations'])) {
            $result['violations'] = $frscData['violations'];
        }

        // Determine overall verification status
        $result['overall_status'] = $this->determineLicenseStatus($result);

        // If no driver data provided, return basic verification
        if (empty($driverData)) {
            $result['verification_level'] = 'basic';
            $result['match_score'] = $result['overall_status'] === 'valid' ? 1.0 : 0.0;
            return $result;
        }

        // Perform detailed comparison if driver data is available
        $result['verification_level'] = 'detailed';
        $comparisonResult = $this->compareWithDriverData($frscData, $driverData);
        
        $result['comparison_results'] = $comparisonResult['field_matches'];
        $result['match_score'] = $comparisonResult['overall_score'];
        $result['discrepancies'] = $comparisonResult['discrepancies'];

        // Adjust match score based on license validity
        if ($result['overall_status'] !== 'valid') {
            $result['match_score'] *= 0.5; // Reduce score for invalid licenses
        }

        return $result;
    }

    /**
     * Sanitize FRSC data for logging and storage
     */
    private function sanitizeFrscData($frscData)
    {
        $sanitized = $frscData;
        
        // Remove sensitive information
        $sensitiveFields = ['photo', 'signature', 'biometric_data'];
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '[SENSITIVE_DATA_REMOVED]';
            }
        }

        // Keep only necessary fields
        $allowedFields = [
            'license_number', 'licence_number', 'firstname', 'middlename', 
            'lastname', 'surname', 'gender', 'date_of_birth', 'birthdate',
            'address', 'state_issued', 'issue_date', 'date_issued',
            'expiry_date', 'expires_on', 'class', 'license_class',
            'status', 'license_status', 'violations', 'restrictions'
        ];

        $filtered = [];
        foreach ($allowedFields as $field) {
            if (isset($sanitized[$field])) {
                $filtered[$field] = $sanitized[$field];
            }
        }

        return $filtered;
    }

    /**
     * Determine overall license status
     */
    private function determineLicenseStatus($result)
    {
        // Check if license is found and active
        if (!$result['verified']) {
            return 'not_found';
        }

        // Check status from FRSC
        $status = strtolower($result['license_status']);
        if (in_array($status, ['suspended', 'revoked', 'cancelled', 'blocked'])) {
            return 'invalid';
        }

        // Check if expired
        if ($result['is_expired'] === true) {
            return 'expired';
        }

        // Check for serious violations
        $seriousViolations = 0;
        foreach ($result['violations'] as $violation) {
            if (isset($violation['severity']) && $violation['severity'] === 'serious') {
                $seriousViolations++;
            }
        }

        if ($seriousViolations > 3) {
            return 'high_risk';
        }

        return 'valid';
    }

    /**
     * Compare FRSC data with driver-provided data
     */
    private function compareWithDriverData($frscData, $driverData)
    {
        $fieldMappings = [
            'first_name' => ['firstname', 'first_name'],
            'middle_name' => ['middlename', 'middle_name'],
            'surname' => ['lastname', 'surname', 'last_name'],
            'date_of_birth' => ['date_of_birth', 'birthdate'],
            'gender' => ['gender'],
            'license_class' => ['class', 'license_class'],
            'license_number' => ['license_number', 'licence_number']
        ];

        $totalScore = 0;
        $comparedFields = 0;
        $fieldMatches = [];
        $discrepancies = [];

        foreach ($fieldMappings as $driverField => $frscFields) {
            if (!isset($driverData[$driverField]) || empty($driverData[$driverField])) {
                continue;
            }

            $driverValue = $driverData[$driverField];
            $frscValue = null;

            // Find corresponding FRSC value
            foreach ($frscFields as $frscField) {
                if (isset($frscData[$frscField]) && !empty($frscData[$frscField])) {
                    $frscValue = $frscData[$frscField];
                    break;
                }
            }

            if ($frscValue === null) {
                continue; // Skip if FRSC doesn't have this field
            }

            $comparedFields++;
            $similarity = $this->calculateFieldSimilarity($driverValue, $frscValue, $driverField);
            $isMatch = $similarity >= $this->getFieldThreshold($driverField);

            $fieldMatches[$driverField] = [
                'driver_value' => $driverValue,
                'frsc_value' => $frscValue,
                'similarity_score' => $similarity,
                'is_match' => $isMatch,
                'threshold' => $this->getFieldThreshold($driverField)
            ];

            $totalScore += $similarity;

            if (!$isMatch) {
                $discrepancies[] = [
                    'field' => $driverField,
                    'driver_value' => $driverValue,
                    'frsc_value' => $frscValue,
                    'similarity_score' => $similarity
                ];
            }
        }

        return [
            'overall_score' => $comparedFields > 0 ? $totalScore / $comparedFields : 0,
            'field_matches' => $fieldMatches,
            'discrepancies' => $discrepancies,
            'compared_fields' => $comparedFields
        ];
    }

    /**
     * Calculate similarity between driver and FRSC field values
     */
    private function calculateFieldSimilarity($driverValue, $frscValue, $fieldType)
    {
        $normalizedDriver = $this->normalizeValue($driverValue, $fieldType);
        $normalizedFrsc = $this->normalizeValue($frscValue, $fieldType);

        if ($normalizedDriver === $normalizedFrsc) {
            return 1.0;
        }

        switch ($fieldType) {
            case 'license_number':
                // Exact match required for license numbers
                return $normalizedDriver === $normalizedFrsc ? 1.0 : 0.0;
                
            case 'date_of_birth':
                return $this->compareDates($normalizedDriver, $normalizedFrsc);
            
            case 'first_name':
            case 'middle_name':
            case 'surname':
                return $this->calculateNameSimilarity($normalizedDriver, $normalizedFrsc);
            
            case 'gender':
            case 'license_class':
                return strtolower($normalizedDriver) === strtolower($normalizedFrsc) ? 1.0 : 0.0;
            
            default:
                similar_text($normalizedDriver, $normalizedFrsc, $percent);
                return $percent / 100;
        }
    }

    /**
     * Normalize value for comparison
     */
    private function normalizeValue($value, $fieldType)
    {
        $normalized = trim($value);

        switch ($fieldType) {
            case 'first_name':
            case 'middle_name':
            case 'surname':
                return strtolower(preg_replace('/\s+/', ' ', $normalized));
            
            case 'license_number':
                return strtoupper(preg_replace('/\s+/', '', $normalized));
            
            case 'date_of_birth':
                try {
                    $date = new \DateTime($normalized);
                    return $date->format('Y-m-d');
                } catch (Exception $e) {
                    return $normalized;
                }
            
            case 'gender':
                $gender = strtolower($normalized);
                if (in_array($gender, ['m', 'male'])) return 'male';
                if (in_array($gender, ['f', 'female'])) return 'female';
                return $gender;
                
            case 'license_class':
                return strtoupper($normalized);
            
            default:
                return $normalized;
        }
    }

    /**
     * Calculate name similarity with phonetic matching
     */
    private function calculateNameSimilarity($name1, $name2)
    {
        if ($name1 === $name2) {
            return 1.0;
        }

        $levenshtein = 1 - (levenshtein($name1, $name2) / max(strlen($name1), strlen($name2)));
        similar_text($name1, $name2, $percent);
        $similarText = $percent / 100;
        $soundex = soundex($name1) === soundex($name2) ? 1.0 : 0.0;

        return ($levenshtein * 0.4) + ($similarText * 0.4) + ($soundex * 0.2);
    }

    /**
     * Compare dates with format flexibility
     */
    private function compareDates($date1, $date2)
    {
        try {
            $d1 = new \DateTime($date1);
            $d2 = new \DateTime($date2);
            return $d1->format('Y-m-d') === $d2->format('Y-m-d') ? 1.0 : 0.0;
        } catch (Exception $e) {
            return $date1 === $date2 ? 1.0 : 0.0;
        }
    }

    /**
     * Get similarity threshold for each field type
     */
    private function getFieldThreshold($fieldType)
    {
        $thresholds = [
            'first_name' => 0.8,
            'middle_name' => 0.8,
            'surname' => 0.8,
            'date_of_birth' => 1.0,
            'gender' => 1.0,
            'license_class' => 1.0,
            'license_number' => 1.0
        ];

        return $thresholds[$fieldType] ?? 0.8;
    }

    /**
     * Check license expiry and renewal requirements
     */
    public function checkLicenseRenewal($licenseNumber)
    {
        try {
            $verification = $this->verifyDriverLicense($licenseNumber);
            
            if (!$verification['verified']) {
                return [
                    'needs_renewal' => true,
                    'reason' => 'License not found or invalid',
                    'urgency' => 'high'
                ];
            }

            $expiryDate = $verification['expiry_date'];
            if (!$expiryDate) {
                return [
                    'needs_renewal' => false,
                    'reason' => 'No expiry date available',
                    'urgency' => 'unknown'
                ];
            }

            $expiry = new \DateTime($expiryDate);
            $now = new \DateTime();
            $daysUntilExpiry = $now->diff($expiry)->days;

            if ($expiry < $now) {
                return [
                    'needs_renewal' => true,
                    'reason' => 'License has expired',
                    'urgency' => 'immediate',
                    'days_expired' => $daysUntilExpiry
                ];
            } elseif ($daysUntilExpiry <= 30) {
                return [
                    'needs_renewal' => true,
                    'reason' => 'License expires soon',
                    'urgency' => 'high',
                    'days_until_expiry' => $daysUntilExpiry
                ];
            } elseif ($daysUntilExpiry <= 90) {
                return [
                    'needs_renewal' => false,
                    'reason' => 'License expires in 3 months',
                    'urgency' => 'medium',
                    'days_until_expiry' => $daysUntilExpiry
                ];
            } else {
                return [
                    'needs_renewal' => false,
                    'reason' => 'License is valid',
                    'urgency' => 'low',
                    'days_until_expiry' => $daysUntilExpiry
                ];
            }

        } catch (Exception $e) {
            Log::error('License renewal check failed', [
                'license_number' => substr($licenseNumber, 0, 3) . '***',
                'error' => $e->getMessage()
            ]);

            return [
                'needs_renewal' => null,
                'reason' => 'Unable to check renewal status',
                'urgency' => 'unknown',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get license violation history
     */
    public function getLicenseViolations($licenseNumber)
    {
        $verification = $this->verifyDriverLicense($licenseNumber);
        
        if (!$verification['verified']) {
            return [
                'success' => false,
                'violations' => [],
                'error' => 'License not found'
            ];
        }

        return [
            'success' => true,
            'violations' => $verification['violations'] ?? [],
            'violation_count' => count($verification['violations'] ?? []),
            'license_status' => $verification['license_status']
        ];
    }

    /**
     * Check API service status
     */
    public function checkServiceStatus()
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->generateAuthToken(),
                    'X-API-Key' => $this->apiKey
                ])
                ->get($this->frscApiEndpoint . '/status');

            return [
                'available' => $response->successful(),
                'response_time' => $response->handlerStats()['total_time'] ?? null,
                'status_code' => $response->status(),
                'message' => $response->successful() ? 'Service available' : 'Service unavailable'
            ];
        } catch (Exception $e) {
            return [
                'available' => false,
                'response_time' => null,
                'status_code' => null,
                'message' => 'Service check failed: ' . $e->getMessage()
            ];
        }
    }
}
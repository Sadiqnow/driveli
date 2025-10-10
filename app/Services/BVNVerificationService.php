<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class BVNVerificationService
{
    private $cbnApiEndpoint;
    private $nibssApiEndpoint;
    private $apiKey;
    private $apiSecret;
    private $timeout;
    private $retryAttempts;
    private $preferredProvider;

    public function __construct()
    {
        $this->cbnApiEndpoint = config('services.cbn.api_endpoint', 'https://api.cbn.gov.ng');
        $this->nibssApiEndpoint = config('services.nibss.api_endpoint', 'https://api.nibss.com.ng');
        $this->apiKey = config('services.bvn.api_key');
        $this->apiSecret = config('services.bvn.api_secret');
        $this->timeout = config('services.bvn.timeout', 30);
        $this->retryAttempts = config('services.bvn.retry_attempts', 3);
        $this->preferredProvider = config('services.bvn.preferred_provider', 'nibss');
    }

    /**
     * Verify BVN against CBN/NIBSS database
     * 
     * @param string $bvn The 11-digit BVN to verify
     * @param array $driverData Driver's submitted data for comparison
     * @return array Verification result
     */
    public function verifyBVN($bvn, $driverData = [])
    {
        try {
            Log::info('Starting BVN verification', [
                'bvn' => substr($bvn, 0, 3) . '********', // Mask for security
                'provider' => $this->preferredProvider,
                'has_driver_data' => !empty($driverData)
            ]);

            // Validate BVN format
            if (!$this->validateBVNFormat($bvn)) {
                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Invalid BVN format. BVN must be exactly 11 digits.',
                    'error_code' => 'INVALID_FORMAT',
                    'timestamp' => now()
                ];
            }

            // Check cache first
            $cacheKey = 'bvn_verification_' . hash('sha256', $bvn);
            $cachedResult = Cache::get($cacheKey);
            
            if ($cachedResult && config('drivelink.verification.enable_cache', true)) {
                Log::info('Using cached BVN verification result');
                $cachedResult['from_cache'] = true;
                return $cachedResult;
            }

            // Call BVN API with retry logic
            $bvnResponse = $this->callBVNApi($bvn);

            if (!$bvnResponse['success']) {
                return $bvnResponse;
            }

            // Process and validate response
            $verificationResult = $this->processBVNResponse($bvnResponse['data'], $driverData, $bvn);

            // Cache successful results for 6 hours (BVN changes less frequently)
            if ($verificationResult['success']) {
                Cache::put($cacheKey, $verificationResult, now()->addHours(6));
            }

            Log::info('BVN verification completed', [
                'bvn' => substr($bvn, 0, 3) . '********',
                'verified' => $verificationResult['verified'],
                'match_score' => $verificationResult['match_score'] ?? null,
                'account_status' => $verificationResult['account_status'] ?? null
            ]);

            return $verificationResult;

        } catch (Exception $e) {
            Log::error('BVN verification failed', [
                'bvn' => substr($bvn, 0, 3) . '********',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => 'BVN verification service unavailable. Please try again later.',
                'error_code' => 'SERVICE_ERROR',
                'internal_error' => $e->getMessage(),
                'timestamp' => now()
            ];
        }
    }

    /**
     * Validate BVN format
     */
    private function validateBVNFormat($bvn)
    {
        // Remove any spaces or special characters
        $cleanBvn = preg_replace('/[^\d]/', '', $bvn);
        
        // Must be exactly 11 digits
        return strlen($cleanBvn) === 11 && ctype_digit($cleanBvn);
    }

    /**
     * Call BVN API with retry logic
     */
    private function callBVNApi($bvn)
    {
        $attempts = 0;
        $lastError = '';

        while ($attempts < $this->retryAttempts) {
            $attempts++;

            try {
                Log::info("BVN API call attempt {$attempts} using {$this->preferredProvider}");

                $endpoint = $this->getApiEndpoint();
                $authHeaders = $this->getAuthHeaders();

                $response = Http::timeout($this->timeout)
                    ->withHeaders($authHeaders)
                    ->post($endpoint, [
                        'bvn' => $bvn,
                        'verification_type' => 'standard',
                        'include_image' => false, // Set to true if you need photo
                        'include_watchlist_status' => true
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['responseCode']) && $data['responseCode'] === '00') {
                        // NIBSS format
                        return [
                            'success' => true,
                            'data' => $data,
                            'provider' => $this->preferredProvider,
                            'response_code' => $response->status(),
                            'api_response_time' => $response->handlerStats()['total_time'] ?? null
                        ];
                    } elseif (isset($data['status']) && $data['status'] === 'success') {
                        // CBN format
                        return [
                            'success' => true,
                            'data' => $data['data'] ?? $data,
                            'provider' => $this->preferredProvider,
                            'response_code' => $response->status(),
                            'api_response_time' => $response->handlerStats()['total_time'] ?? null
                        ];
                    } else {
                        $lastError = $data['message'] ?? $data['responseMessage'] ?? 'Unknown API error';
                        Log::warning("BVN API returned error: {$lastError}");
                    }
                } else {
                    $lastError = "HTTP {$response->status()}: {$response->body()}";
                    Log::warning("BVN API HTTP error: {$lastError}");
                }

                // Try fallback provider if available
                if ($attempts === 1 && $this->preferredProvider === 'nibss') {
                    $this->preferredProvider = 'cbn';
                    Log::info('Trying fallback provider: CBN');
                } elseif ($attempts === 1 && $this->preferredProvider === 'cbn') {
                    $this->preferredProvider = 'nibss';
                    Log::info('Trying fallback provider: NIBSS');
                }

            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("BVN API call failed: {$lastError}");
            }

            // Wait before retry (exponential backoff)
            if ($attempts < $this->retryAttempts) {
                $waitTime = pow(2, $attempts - 1); // 1, 2, 4 seconds
                sleep($waitTime);
            }
        }

        return [
            'success' => false,
            'error' => "BVN API verification failed after {$this->retryAttempts} attempts. Last error: {$lastError}",
            'error_code' => 'API_FAILURE',
            'attempts' => $attempts
        ];
    }

    /**
     * Get API endpoint based on provider and environment
     */
    private function getApiEndpoint()
    {
        $isProduction = config('app.env') === 'production';
        
        if ($this->preferredProvider === 'nibss') {
            return $this->nibssApiEndpoint . ($isProduction ? '/bvnverification' : '/sandbox/bvnverification');
        } else {
            return $this->cbnApiEndpoint . ($isProduction ? '/v1/bvn/verify' : '/sandbox/v1/bvn/verify');
        }
    }

    /**
     * Get authentication headers for the selected provider
     */
    private function getAuthHeaders()
    {
        $baseHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        if ($this->preferredProvider === 'nibss') {
            return array_merge($baseHeaders, [
                'Sandbox-Key' => $this->apiKey,
                'Authorization' => 'Bearer ' . $this->generateNibssToken()
            ]);
        } else {
            return array_merge($baseHeaders, [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'X-API-Key' => $this->apiSecret
            ]);
        }
    }

    /**
     * Generate NIBSS authentication token
     */
    private function generateNibssToken()
    {
        if (!$this->apiSecret) {
            return $this->apiKey;
        }

        // NIBSS may require JWT or specific token format
        $payload = [
            'iss' => config('app.name'),
            'sub' => 'bvn_verification',
            'aud' => 'nibss_api',
            'exp' => time() + 3600, // 1 hour
            'iat' => time(),
            'api_key' => $this->apiKey
        ];

        // For demo purposes, return a base64 encoded payload
        // In production, implement proper JWT signing
        return base64_encode(json_encode($payload));
    }

    /**
     * Process BVN API response and compare with driver data
     */
    private function processBVNResponse($bvnData, $driverData, $bvn)
    {
        $result = [
            'success' => true,
            'verified' => false,
            'bvn' => $bvn,
            'bvn_data' => $this->sanitizeBvnData($bvnData),
            'account_status' => 'unknown',
            'bank_registration_date' => null,
            'watchlist_status' => null,
            'comparison_results' => [],
            'match_score' => 0,
            'discrepancies' => [],
            'verification_level' => 'basic',
            'provider' => $this->preferredProvider,
            'timestamp' => now()
        ];

        // Extract data based on provider format
        $extractedData = $this->extractBvnData($bvnData);

        // Check if BVN exists and is valid
        if (!$extractedData['bvn_found']) {
            $result['error'] = 'BVN not found in database';
            $result['error_code'] = 'BVN_NOT_FOUND';
            return $result;
        }

        $result['verified'] = true;
        $result['account_status'] = $extractedData['status'];
        $result['bank_registration_date'] = $extractedData['registration_date'];
        $result['watchlist_status'] = $extractedData['watchlist_status'];

        // Check if BVN is active and not on watchlist
        if (!$this->isBvnActive($extractedData)) {
            $result['verified'] = false;
            $result['error'] = 'BVN is inactive or on watchlist';
            $result['error_code'] = 'BVN_INACTIVE';
        }

        // If no driver data provided, return basic verification
        if (empty($driverData)) {
            $result['verification_level'] = 'basic';
            $result['match_score'] = $result['verified'] ? 1.0 : 0.0;
            return $result;
        }

        // Perform detailed comparison if driver data is available
        $result['verification_level'] = 'detailed';
        $comparisonResult = $this->compareWithDriverData($extractedData, $driverData);
        
        $result['comparison_results'] = $comparisonResult['field_matches'];
        $result['match_score'] = $comparisonResult['overall_score'];
        $result['discrepancies'] = $comparisonResult['discrepancies'];

        // Adjust match score based on BVN status
        if (!$result['verified']) {
            $result['match_score'] *= 0.3; // Severely reduce score for inactive BVN
        }

        return $result;
    }

    /**
     * Extract BVN data from API response (handle different provider formats)
     */
    private function extractBvnData($bvnData)
    {
        $extracted = [
            'bvn_found' => false,
            'bvn' => null,
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
            'date_of_birth' => null,
            'gender' => null,
            'phone' => null,
            'email' => null,
            'status' => 'unknown',
            'registration_date' => null,
            'watchlist_status' => 'clear'
        ];

        // Handle NIBSS format
        if (isset($bvnData['responseCode'])) {
            if ($bvnData['responseCode'] === '00' && isset($bvnData['bvn'])) {
                $extracted['bvn_found'] = true;
                $extracted['bvn'] = $bvnData['bvn'];
                $extracted['first_name'] = $bvnData['firstName'] ?? null;
                $extracted['middle_name'] = $bvnData['middleName'] ?? null;
                $extracted['last_name'] = $bvnData['lastName'] ?? null;
                $extracted['date_of_birth'] = $bvnData['dateOfBirth'] ?? null;
                $extracted['gender'] = $bvnData['gender'] ?? null;
                $extracted['phone'] = $bvnData['phoneNumber'] ?? null;
                $extracted['email'] = $bvnData['email'] ?? null;
                $extracted['status'] = 'active'; // NIBSS doesn't typically provide status
                $extracted['registration_date'] = $bvnData['registrationDate'] ?? null;
                $extracted['watchlist_status'] = $bvnData['watchListStatus'] ?? 'clear';
            }
        }
        // Handle CBN format
        elseif (isset($bvnData['bvn']) || isset($bvnData['customer'])) {
            $customer = $bvnData['customer'] ?? $bvnData;
            
            $extracted['bvn_found'] = true;
            $extracted['bvn'] = $customer['bvn'] ?? $bvnData['bvn'];
            $extracted['first_name'] = $customer['first_name'] ?? $customer['firstName'];
            $extracted['middle_name'] = $customer['middle_name'] ?? $customer['middleName'];
            $extracted['last_name'] = $customer['last_name'] ?? $customer['lastName'];
            $extracted['date_of_birth'] = $customer['date_of_birth'] ?? $customer['dateOfBirth'];
            $extracted['gender'] = $customer['gender'];
            $extracted['phone'] = $customer['phone_number'] ?? $customer['phoneNumber'];
            $extracted['email'] = $customer['email'];
            $extracted['status'] = $customer['status'] ?? 'active';
            $extracted['registration_date'] = $customer['registration_date'] ?? $customer['createdAt'];
            $extracted['watchlist_status'] = $customer['watchlist_status'] ?? 'clear';
        }

        return $extracted;
    }

    /**
     * Check if BVN is active and not on watchlist
     */
    private function isBvnActive($extractedData)
    {
        // Check watchlist status
        if (in_array(strtolower($extractedData['watchlist_status']), ['blocked', 'flagged', 'watchlist'])) {
            return false;
        }

        // Check status
        if (in_array(strtolower($extractedData['status']), ['inactive', 'suspended', 'closed'])) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize BVN data for logging and storage
     */
    private function sanitizeBvnData($bvnData)
    {
        $sanitized = $bvnData;
        
        // Remove sensitive information
        $sensitiveFields = ['image', 'photo', 'base64Image', 'signature'];
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '[SENSITIVE_DATA_REMOVED]';
            }
        }

        // Mask phone and email partially
        if (isset($sanitized['phoneNumber'])) {
            $sanitized['phoneNumber'] = substr($sanitized['phoneNumber'], 0, 3) . '****' . substr($sanitized['phoneNumber'], -3);
        }
        
        if (isset($sanitized['email'])) {
            $parts = explode('@', $sanitized['email']);
            if (count($parts) === 2) {
                $sanitized['email'] = substr($parts[0], 0, 2) . '***@' . $parts[1];
            }
        }

        return $sanitized;
    }

    /**
     * Compare BVN data with driver-provided data
     */
    private function compareWithDriverData($bvnData, $driverData)
    {
        $fieldMappings = [
            'first_name' => 'first_name',
            'middle_name' => 'middle_name',
            'surname' => 'last_name',
            'date_of_birth' => 'date_of_birth',
            'gender' => 'gender',
            'phone' => 'phone',
            'email' => 'email'
        ];

        $totalScore = 0;
        $comparedFields = 0;
        $fieldMatches = [];
        $discrepancies = [];

        foreach ($fieldMappings as $driverField => $bvnField) {
            if (!isset($driverData[$driverField]) || empty($driverData[$driverField])) {
                continue;
            }

            $driverValue = $driverData[$driverField];
            $bvnValue = $bvnData[$bvnField] ?? null;

            if ($bvnValue === null || empty($bvnValue)) {
                continue; // Skip if BVN doesn't have this field
            }

            $comparedFields++;
            $similarity = $this->calculateFieldSimilarity($driverValue, $bvnValue, $driverField);
            $isMatch = $similarity >= $this->getFieldThreshold($driverField);

            $fieldMatches[$driverField] = [
                'driver_value' => $driverValue,
                'bvn_value' => $bvnValue,
                'similarity_score' => $similarity,
                'is_match' => $isMatch,
                'threshold' => $this->getFieldThreshold($driverField)
            ];

            $totalScore += $similarity;

            if (!$isMatch) {
                $discrepancies[] = [
                    'field' => $driverField,
                    'driver_value' => $driverValue,
                    'bvn_value' => $bvnValue,
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
     * Calculate similarity between driver and BVN field values
     */
    private function calculateFieldSimilarity($driverValue, $bvnValue, $fieldType)
    {
        $normalizedDriver = $this->normalizeValue($driverValue, $fieldType);
        $normalizedBvn = $this->normalizeValue($bvnValue, $fieldType);

        if ($normalizedDriver === $normalizedBvn) {
            return 1.0;
        }

        switch ($fieldType) {
            case 'date_of_birth':
                return $this->compareDates($normalizedDriver, $normalizedBvn);
            
            case 'first_name':
            case 'middle_name':
            case 'surname':
                return $this->calculateNameSimilarity($normalizedDriver, $normalizedBvn);
            
            case 'phone':
                return $this->comparePhones($normalizedDriver, $normalizedBvn);
            
            case 'email':
                return strtolower($normalizedDriver) === strtolower($normalizedBvn) ? 1.0 : 0.0;
            
            case 'gender':
                return strtolower($normalizedDriver) === strtolower($normalizedBvn) ? 1.0 : 0.0;
            
            default:
                similar_text($normalizedDriver, $normalizedBvn, $percent);
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
            
            case 'phone':
                $phone = preg_replace('/\D/', '', $normalized);
                if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
                    $phone = '234' . substr($phone, 1);
                }
                return $phone;
            
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
     * Compare phone numbers
     */
    private function comparePhones($phone1, $phone2)
    {
        $clean1 = preg_replace('/^234/', '', $phone1);
        $clean2 = preg_replace('/^234/', '', $phone2);
        
        return $clean1 === $clean2 ? 1.0 : 0.0;
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
            'phone' => 0.9, // Allow slight variations in phone format
            'email' => 1.0
        ];

        return $thresholds[$fieldType] ?? 0.8;
    }

    /**
     * Check multiple BVNs (batch processing)
     */
    public function batchVerifyBVNs($bvns)
    {
        $results = [];
        
        foreach ($bvns as $bvn) {
            $results[$bvn] = $this->verifyBVN($bvn);
            
            // Add delay to avoid rate limiting
            usleep(200000); // 0.2 second delay
        }
        
        return $results;
    }

    /**
     * Get BVN account information (limited data for privacy)
     */
    public function getBVNAccountInfo($bvn)
    {
        $verification = $this->verifyBVN($bvn);
        
        if (!$verification['verified']) {
            return [
                'success' => false,
                'error' => 'BVN not found or inactive'
            ];
        }

        return [
            'success' => true,
            'bvn' => $bvn,
            'account_status' => $verification['account_status'],
            'registration_date' => $verification['bank_registration_date'],
            'watchlist_status' => $verification['watchlist_status'],
            'is_verified' => $verification['verified']
        ];
    }

    /**
     * Check API service status
     */
    public function checkServiceStatus()
    {
        try {
            $endpoint = $this->getApiEndpoint() . '/status';
            $headers = $this->getAuthHeaders();

            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->get($endpoint);

            return [
                'available' => $response->successful(),
                'response_time' => $response->handlerStats()['total_time'] ?? null,
                'status_code' => $response->status(),
                'provider' => $this->preferredProvider,
                'message' => $response->successful() ? 'Service available' : 'Service unavailable'
            ];
        } catch (Exception $e) {
            return [
                'available' => false,
                'response_time' => null,
                'status_code' => null,
                'provider' => $this->preferredProvider,
                'message' => 'Service check failed: ' . $e->getMessage()
            ];
        }
    }
}
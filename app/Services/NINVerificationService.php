<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class NINVerificationService
{
    private $nimcApiEndpoint;
    private $apiKey;
    private $timeout;
    private $retryAttempts;

    public function __construct()
    {
        $this->nimcApiEndpoint = config('services.nimc.api_endpoint', 'https://api.nimc.gov.ng');
        $this->apiKey = config('services.nimc.api_key');
        $this->timeout = config('services.nimc.timeout', 30);
        $this->retryAttempts = config('services.nimc.retry_attempts', 3);
    }

    /**
     * Verify NIN against NIMC database
     * 
     * @param string $nin The 11-digit NIN to verify
     * @param array $driverData Driver's submitted data for comparison
     * @return array Verification result
     */
    public function verifyNIN($nin, $driverData = [])
    {
        try {
            Log::info('Starting NIN verification', [
                'nin' => substr($nin, 0, 3) . '********', // Log only first 3 digits for security
                'has_driver_data' => !empty($driverData)
            ]);

            // Validate NIN format
            if (!$this->validateNINFormat($nin)) {
                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Invalid NIN format. NIN must be exactly 11 digits.',
                    'error_code' => 'INVALID_FORMAT',
                    'timestamp' => now()
                ];
            }

            // Check cache first to avoid duplicate API calls
            $cacheKey = 'nin_verification_' . hash('sha256', $nin);
            $cachedResult = Cache::get($cacheKey);
            
            if ($cachedResult && config('drivelink.verification.enable_cache', true)) {
                Log::info('Using cached NIN verification result');
                $cachedResult['from_cache'] = true;
                return $cachedResult;
            }

            // Call NIMC API with retry logic
            $nimcResponse = $this->callNIMCApi($nin);

            if (!$nimcResponse['success']) {
                return $nimcResponse;
            }

            // Process and validate response
            $verificationResult = $this->processNIMCResponse($nimcResponse['data'], $driverData, $nin);

            // Cache successful results for 24 hours
            if ($verificationResult['success']) {
                Cache::put($cacheKey, $verificationResult, now()->addHours(24));
            }

            Log::info('NIN verification completed', [
                'nin' => substr($nin, 0, 3) . '********',
                'verified' => $verificationResult['verified'],
                'match_score' => $verificationResult['match_score'] ?? null
            ]);

            return $verificationResult;

        } catch (Exception $e) {
            Log::error('NIN verification failed', [
                'nin' => substr($nin, 0, 3) . '********',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => 'NIN verification service unavailable. Please try again later.',
                'error_code' => 'SERVICE_ERROR',
                'internal_error' => $e->getMessage(),
                'timestamp' => now()
            ];
        }
    }

    /**
     * Validate NIN format
     */
    private function validateNINFormat($nin)
    {
        // Remove any spaces or special characters
        $cleanNin = preg_replace('/[^\d]/', '', $nin);
        
        // Must be exactly 11 digits
        return strlen($cleanNin) === 11 && ctype_digit($cleanNin);
    }

    /**
     * Call NIMC API with retry logic
     */
    private function callNIMCApi($nin)
    {
        $attempts = 0;
        $lastError = '';

        while ($attempts < $this->retryAttempts) {
            $attempts++;

            try {
                Log::info("NIMC API call attempt {$attempts}");

                // Use different endpoints based on environment
                $endpoint = $this->getApiEndpoint();
                
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])
                    ->post($endpoint . '/verify', [
                        'nin' => $nin,
                        'verification_type' => 'full',
                        'include_photo' => false // Set to true if you need photo verification
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['status']) && $data['status'] === 'success') {
                        return [
                            'success' => true,
                            'data' => $data['data'] ?? $data,
                            'response_code' => $response->status(),
                            'api_response_time' => $response->handlerStats()['total_time'] ?? null
                        ];
                    } else {
                        $lastError = $data['message'] ?? 'Unknown API error';
                        Log::warning("NIMC API returned error: {$lastError}");
                    }
                } else {
                    $lastError = "HTTP {$response->status()}: {$response->body()}";
                    Log::warning("NIMC API HTTP error: {$lastError}");
                }

            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("NIMC API call failed: {$lastError}");
            }

            // Wait before retry (exponential backoff)
            if ($attempts < $this->retryAttempts) {
                $waitTime = pow(2, $attempts - 1); // 1, 2, 4 seconds
                sleep($waitTime);
            }
        }

        return [
            'success' => false,
            'error' => "NIMC API verification failed after {$this->retryAttempts} attempts. Last error: {$lastError}",
            'error_code' => 'API_FAILURE',
            'attempts' => $attempts
        ];
    }

    /**
     * Get API endpoint based on environment
     */
    private function getApiEndpoint()
    {
        if (config('app.env') === 'production') {
            return $this->nimcApiEndpoint . '/v2/nin';
        } else {
            // Use sandbox/test endpoint for development
            return $this->nimcApiEndpoint . '/sandbox/v2/nin';
        }
    }

    /**
     * Process NIMC API response and compare with driver data
     */
    private function processNIMCResponse($nimcData, $driverData, $nin)
    {
        $result = [
            'success' => true,
            'verified' => false,
            'nin' => $nin,
            'nimc_data' => $this->sanitizeNimcData($nimcData),
            'comparison_results' => [],
            'match_score' => 0,
            'discrepancies' => [],
            'verification_level' => 'basic',
            'timestamp' => now()
        ];

        // Check if NIN exists in NIMC database
        if (!isset($nimcData['nin']) || $nimcData['nin'] !== $nin) {
            $result['error'] = 'NIN not found in NIMC database or mismatch';
            $result['error_code'] = 'NIN_NOT_FOUND';
            return $result;
        }

        $result['verified'] = true;

        // If no driver data provided, return basic verification
        if (empty($driverData)) {
            $result['verification_level'] = 'basic';
            $result['match_score'] = 1.0;
            return $result;
        }

        // Perform detailed comparison if driver data is available
        $result['verification_level'] = 'detailed';
        $comparisonResult = $this->compareWithDriverData($nimcData, $driverData);
        
        $result['comparison_results'] = $comparisonResult['field_matches'];
        $result['match_score'] = $comparisonResult['overall_score'];
        $result['discrepancies'] = $comparisonResult['discrepancies'];

        // Determine verification status based on match score
        $result['verification_status'] = $this->determineVerificationStatus($result['match_score']);

        return $result;
    }

    /**
     * Sanitize NIMC data for logging and storage
     */
    private function sanitizeNimcData($nimcData)
    {
        $sanitized = $nimcData;
        
        // Remove or mask sensitive information
        if (isset($sanitized['photo'])) {
            $sanitized['photo'] = '[PHOTO_DATA_REMOVED]';
        }
        
        if (isset($sanitized['signature'])) {
            $sanitized['signature'] = '[SIGNATURE_DATA_REMOVED]';
        }

        // Keep only necessary fields
        $allowedFields = [
            'nin', 'firstname', 'middlename', 'surname', 'gender', 
            'birthdate', 'birthstate', 'birthcountry', 'birthlga',
            'residencestate', 'residencelga', 'residenceaddress',
            'phone', 'email', 'profession', 'religion', 'maritalstatus',
            'educationallevel', 'nok_firstname', 'nok_middlename', 
            'nok_surname', 'nok_state', 'nok_lga', 'nok_address'
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
     * Compare NIMC data with driver-provided data
     */
    private function compareWithDriverData($nimcData, $driverData)
    {
        $fieldMappings = [
            'first_name' => ['firstname', 'first_name'],
            'middle_name' => ['middlename', 'middle_name'],
            'surname' => ['surname', 'last_name'],
            'date_of_birth' => ['birthdate', 'date_of_birth'],
            'gender' => ['gender'],
            'phone' => ['phone'],
            'email' => ['email'],
            'state_of_origin' => ['birthstate', 'state_of_origin'],
            'lga_of_origin' => ['birthlga', 'lga_of_origin']
        ];

        $totalScore = 0;
        $comparedFields = 0;
        $fieldMatches = [];
        $discrepancies = [];

        foreach ($fieldMappings as $driverField => $nimcFields) {
            if (!isset($driverData[$driverField]) || empty($driverData[$driverField])) {
                continue;
            }

            $driverValue = $driverData[$driverField];
            $nimcValue = null;

            // Find corresponding NIMC value
            foreach ($nimcFields as $nimcField) {
                if (isset($nimcData[$nimcField]) && !empty($nimcData[$nimcField])) {
                    $nimcValue = $nimcData[$nimcField];
                    break;
                }
            }

            if ($nimcValue === null) {
                continue; // Skip if NIMC doesn't have this field
            }

            $comparedFields++;
            $similarity = $this->calculateFieldSimilarity($driverValue, $nimcValue, $driverField);
            $isMatch = $similarity >= $this->getFieldThreshold($driverField);

            $fieldMatches[$driverField] = [
                'driver_value' => $driverValue,
                'nimc_value' => $nimcValue,
                'similarity_score' => $similarity,
                'is_match' => $isMatch,
                'threshold' => $this->getFieldThreshold($driverField)
            ];

            $totalScore += $similarity;

            if (!$isMatch) {
                $discrepancies[] = [
                    'field' => $driverField,
                    'driver_value' => $driverValue,
                    'nimc_value' => $nimcValue,
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
     * Calculate similarity between driver and NIMC field values
     */
    private function calculateFieldSimilarity($driverValue, $nimcValue, $fieldType)
    {
        // Normalize values for comparison
        $normalizedDriver = $this->normalizeValue($driverValue, $fieldType);
        $normalizedNimc = $this->normalizeValue($nimcValue, $fieldType);

        if ($normalizedDriver === $normalizedNimc) {
            return 1.0;
        }

        switch ($fieldType) {
            case 'date_of_birth':
                return $this->compareDates($normalizedDriver, $normalizedNimc);
            
            case 'first_name':
            case 'middle_name':
            case 'surname':
                return $this->calculateNameSimilarity($normalizedDriver, $normalizedNimc);
            
            case 'phone':
                return $this->comparePhones($normalizedDriver, $normalizedNimc);
            
            case 'email':
                return strtolower($normalizedDriver) === strtolower($normalizedNimc) ? 1.0 : 0.0;
            
            default:
                similar_text($normalizedDriver, $normalizedNimc, $percent);
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
            
            default:
                return $normalized;
        }
    }

    /**
     * Calculate name similarity with phonetic matching
     */
    private function calculateNameSimilarity($name1, $name2)
    {
        // Exact match
        if ($name1 === $name2) {
            return 1.0;
        }

        // Levenshtein distance
        $levenshtein = 1 - (levenshtein($name1, $name2) / max(strlen($name1), strlen($name2)));
        
        // Similar text
        similar_text($name1, $name2, $percent);
        $similarText = $percent / 100;

        // Soundex for phonetic similarity
        $soundex = soundex($name1) === soundex($name2) ? 1.0 : 0.0;

        // Weighted average
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
        // Remove country codes and compare
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
            'date_of_birth' => 1.0, // Exact match required
            'gender' => 1.0, // Exact match required
            'phone' => 1.0, // Exact match required
            'email' => 1.0, // Exact match required
            'state_of_origin' => 0.9,
            'lga_of_origin' => 0.9
        ];

        return $thresholds[$fieldType] ?? 0.8;
    }

    /**
     * Determine overall verification status
     */
    private function determineVerificationStatus($matchScore)
    {
        if ($matchScore >= 0.95) {
            return 'verified';
        } elseif ($matchScore >= 0.8) {
            return 'partial_match';
        } elseif ($matchScore >= 0.6) {
            return 'review_required';
        } else {
            return 'failed';
        }
    }

    /**
     * Get verification status for multiple NINs (batch processing)
     */
    public function batchVerifyNINs($nins)
    {
        $results = [];
        
        foreach ($nins as $nin) {
            $results[$nin] = $this->verifyNIN($nin);
            
            // Add small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
        }
        
        return $results;
    }

    /**
     * Check API service status
     */
    public function checkServiceStatus()
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get($this->nimcApiEndpoint . '/status');

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
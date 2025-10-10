<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OCRVerificationService
{
    private $ocrApiKey;
    private $ocrEndpoint;
    
    public function __construct()
    {
        $this->ocrApiKey = config('services.ocr.api_key');
        $this->ocrEndpoint = config('services.ocr.endpoint');
        
        if (!$this->ocrApiKey || !$this->ocrEndpoint) {
            throw new \InvalidArgumentException('OCR service configuration missing. Please set OCR_API_KEY and OCR_ENDPOINT in your environment file.');
        }
        
        if ($this->ocrApiKey === 'demo_key' || $this->ocrApiKey === 'your_api_key_here') {
            throw new \InvalidArgumentException('Invalid OCR API key. Please set a valid API key in your environment file.');
        }
    }

    /**
     * Process NIN document OCR verification
     */
    public function verifyNINDocument($driver, $ninDocumentPath)
    {
        try {
            // Extract text from NIN document using OCR
            $ocrResult = $this->extractTextFromImage($ninDocumentPath);
            
            if (!$ocrResult['success']) {
                return [
                    'success' => false,
                    'error' => 'OCR extraction failed',
                    'details' => $ocrResult['error']
                ];
            }

            $extractedText = $ocrResult['text'];
            
            // Parse NIN document data
            $ninData = $this->parseNINDocument($extractedText);
            
            // Compare with driver input data
            $comparison = $this->compareNINData($driver, $ninData);
            
            // Store verification data
            $verificationData = [
                'ocr_text' => $extractedText,
                'parsed_data' => $ninData,
                'comparison_result' => $comparison,
                'verification_date' => now(),
                'verification_method' => 'OCR_AUTO',
                'match_score' => $comparison['overall_score']
            ];

            // Update driver with verification data
            $driver->update([
                'nin_verification_data' => json_encode($verificationData),
                'nin_verified_at' => now(),
                'nin_ocr_match_score' => $comparison['overall_score'],
                'ocr_verification_status' => $comparison['overall_score'] >= 80 ? 'passed' : 'failed'
            ]);

            return [
                'success' => true,
                'verification_data' => $verificationData,
                'match_score' => $comparison['overall_score'],
                'status' => $comparison['overall_score'] >= 80 ? 'passed' : 'failed'
            ];

        } catch (\Exception $e) {
            Log::error('NIN OCR Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Verification process failed',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Process FRSC license document OCR verification
     */
    public function verifyFRSCDocument($driver, $frscDocumentPath)
    {
        try {
            // Extract text from FRSC document using OCR
            $ocrResult = $this->extractTextFromImage($frscDocumentPath);
            
            if (!$ocrResult['success']) {
                return [
                    'success' => false,
                    'error' => 'OCR extraction failed',
                    'details' => $ocrResult['error']
                ];
            }

            $extractedText = $ocrResult['text'];
            
            // Parse FRSC document data
            $frscData = $this->parseFRSCDocument($extractedText);
            
            // Compare with driver input data
            $comparison = $this->compareFRSCData($driver, $frscData);
            
            // Store verification data
            $verificationData = [
                'ocr_text' => $extractedText,
                'parsed_data' => $frscData,
                'comparison_result' => $comparison,
                'verification_date' => now(),
                'verification_method' => 'OCR_AUTO',
                'match_score' => $comparison['overall_score']
            ];

            // Update driver with verification data
            $driver->update([
                'frsc_verification_data' => json_encode($verificationData),
                'frsc_verified_at' => now(),
                'frsc_ocr_match_score' => $comparison['overall_score'],
                'ocr_verification_status' => $comparison['overall_score'] >= 80 ? 'passed' : 'failed'
            ]);

            return [
                'success' => true,
                'verification_data' => $verificationData,
                'match_score' => $comparison['overall_score'],
                'status' => $comparison['overall_score'] >= 80 ? 'passed' : 'failed'
            ];

        } catch (\Exception $e) {
            Log::error('FRSC OCR Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Verification process failed',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract text from image using OCR API
     */
    private function extractTextFromImage($imagePath)
    {
        try {
            $response = Http::asMultipart()
                ->post($this->ocrEndpoint, [
                    'apikey' => $this->ocrApiKey,
                    'language' => 'eng',
                    'file' => fopen($this->getFullImagePath($imagePath), 'r'),
                    'detectOrientation' => 'true',
                    'scale' => 'true',
                    'OCREngine' => '2'
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['OCRExitCode'] == 1 && isset($result['ParsedResults'][0])) {
                    return [
                        'success' => true,
                        'text' => $result['ParsedResults'][0]['ParsedText'],
                        'raw_result' => $result
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'OCR processing failed',
                'response' => $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'OCR API call failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse NIN document text to extract structured data
     */
    private function parseNINDocument($ocrText)
    {
        $data = [
            'name' => null,
            'nin' => null,
            'date_of_birth' => null,
            'gender' => null,
            'address' => null,
            'state_of_origin' => null
        ];

        // Extract NIN (11-digit number)
        if (preg_match('/\b(\d{11})\b/', $ocrText, $matches)) {
            $data['nin'] = $matches[1];
        }

        // Extract full name patterns
        $lines = explode("\n", $ocrText);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for name patterns
            if (preg_match('/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)$/i', $line) && strlen($line) > 5) {
                if (!$data['name'] && !preg_match('/NIGERIA|NATIONAL|IDENTITY|CARD/i', $line)) {
                    $data['name'] = $line;
                }
            }

            // Extract date of birth
            if (preg_match('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{4})/', $line, $matches)) {
                $data['date_of_birth'] = $matches[1];
            }

            // Extract gender
            if (preg_match('/\b(MALE|FEMALE)\b/i', $line, $matches)) {
                $data['gender'] = strtolower($matches[1]);
            }
        }

        return $data;
    }

    /**
     * Parse FRSC license document text to extract structured data
     */
    private function parseFRSCDocument($ocrText)
    {
        $data = [
            'name' => null,
            'license_number' => null,
            'license_class' => null,
            'expiry_date' => null,
            'issue_date' => null
        ];

        // Extract license number patterns
        if (preg_match('/([A-Z]{3}\s?\d{2}\s?[A-Z]{2}\s?\d{2})/i', $ocrText, $matches)) {
            $data['license_number'] = str_replace(' ', '', $matches[1]);
        }

        // Extract license class
        if (preg_match('/CLASS\s*[:\-]?\s*([A-Z]{1,3})/i', $ocrText, $matches)) {
            $data['license_class'] = strtoupper($matches[1]);
        }

        // Extract expiry date
        if (preg_match('/EXPIR[YE]\s*(?:DATE)?\s*[:\-]?\s*(\d{1,2}[-\/]\d{1,2}[-\/]\d{4})/i', $ocrText, $matches)) {
            $data['expiry_date'] = $matches[1];
        }

        // Extract issue date
        if (preg_match('/ISSUE\s*(?:DATE)?\s*[:\-]?\s*(\d{1,2}[-\/]\d{1,2}[-\/]\d{4})/i', $ocrText, $matches)) {
            $data['issue_date'] = $matches[1];
        }

        // Extract name from license
        $lines = explode("\n", $ocrText);
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (preg_match('/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)$/i', $line) && strlen($line) > 5) {
                if (!$data['name'] && !preg_match('/FEDERAL|ROAD|SAFETY|CORPS|NIGERIA|LICENSE/i', $line)) {
                    $data['name'] = $line;
                }
            }
        }

        return $data;
    }

    /**
     * Compare NIN data with driver input
     */
    private function compareNINData($driver, $ninData)
    {
        $scores = [
            'name_match' => 0,
            'nin_match' => 0,
            'dob_match' => 0,
            'gender_match' => 0
        ];

        // Compare names
        if ($ninData['name']) {
            $driverName = strtolower(trim($driver->first_name . ' ' . ($driver->middle_name ? $driver->middle_name . ' ' : '') . $driver->surname));
            $ninName = strtolower($ninData['name']);
            $scores['name_match'] = $this->calculateNameSimilarity($driverName, $ninName);
        }

        // Compare NIN
        if ($ninData['nin'] && $driver->nin_number) {
            $scores['nin_match'] = $ninData['nin'] === $driver->nin_number ? 100 : 0;
        }

        // Compare date of birth
        if ($ninData['date_of_birth'] && $driver->date_of_birth) {
            $scores['dob_match'] = $this->compareDates($driver->date_of_birth, $ninData['date_of_birth']) ? 100 : 0;
        }

        // Compare gender
        if ($ninData['gender'] && $driver->gender) {
            $scores['gender_match'] = strtolower($ninData['gender']) === strtolower($driver->gender) ? 100 : 0;
        }

        // Calculate overall score
        $weights = ['name_match' => 0.4, 'nin_match' => 0.4, 'dob_match' => 0.15, 'gender_match' => 0.05];
        $overallScore = 0;
        foreach ($scores as $key => $score) {
            $overallScore += $score * $weights[$key];
        }

        return [
            'individual_scores' => $scores,
            'overall_score' => round($overallScore, 2),
            'passed' => $overallScore >= 80,
            'extracted_data' => $ninData
        ];
    }

    /**
     * Compare FRSC data with driver input
     */
    private function compareFRSCData($driver, $frscData)
    {
        $scores = [
            'name_match' => 0,
            'license_number_match' => 0,
            'license_class_match' => 0,
            'expiry_date_match' => 0
        ];

        // Compare names (fixed reference to surname field)
        if ($frscData['name']) {
            $driverName = strtolower(trim($driver->first_name . ' ' . ($driver->middle_name ? $driver->middle_name . ' ' : '') . $driver->surname));
            $frscName = strtolower($frscData['name']);
            $scores['name_match'] = $this->calculateNameSimilarity($driverName, $frscName);
        }

        // Compare license number
        if ($frscData['license_number'] && $driver->license_number) {
            $scores['license_number_match'] = $frscData['license_number'] === $driver->license_number ? 100 : 0;
        }

        // Compare license class
        if ($frscData['license_class'] && $driver->license_class) {
            $scores['license_class_match'] = strtoupper($frscData['license_class']) === strtoupper($driver->license_class) ? 100 : 0;
        }

        // Compare expiry date
        if ($frscData['expiry_date'] && $driver->license_expiry_date) {
            $scores['expiry_date_match'] = $this->compareDates($driver->license_expiry_date, $frscData['expiry_date']) ? 100 : 0;
        }

        // Calculate overall score
        $weights = ['name_match' => 0.3, 'license_number_match' => 0.5, 'license_class_match' => 0.15, 'expiry_date_match' => 0.05];
        $overallScore = 0;
        foreach ($scores as $key => $score) {
            $overallScore += $score * $weights[$key];
        }

        return [
            'individual_scores' => $scores,
            'overall_score' => round($overallScore, 2),
            'passed' => $overallScore >= 80,
            'extracted_data' => $frscData
        ];
    }

    /**
     * Calculate name similarity using Levenshtein distance
     */
    private function calculateNameSimilarity($name1, $name2)
    {
        $name1 = strtolower(trim($name1));
        $name2 = strtolower(trim($name2));
        
        if ($name1 === $name2) return 100;
        
        $maxLen = max(strlen($name1), strlen($name2));
        if ($maxLen == 0) return 0;
        
        $distance = levenshtein($name1, $name2);
        return round((1 - $distance / $maxLen) * 100, 2);
    }

    /**
     * Compare dates with flexible formatting
     */
    private function compareDates($date1, $date2)
    {
        try {
            $d1 = \Carbon\Carbon::parse($date1)->format('Y-m-d');
            $d2 = \Carbon\Carbon::parse($date2)->format('Y-m-d');
            return $d1 === $d2;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get full image path for OCR processing
     */
    private function getFullImagePath($imagePath)
    {
        // If path is already absolute, use it as is
        if (file_exists($imagePath)) {
            return $imagePath;
        }
        
        // Try storage path first
        $storagePath = Storage::path($imagePath);
        if (file_exists($storagePath)) {
            return $storagePath;
        }
        
        // Try public storage path
        $publicPath = storage_path('app/public/' . $imagePath);
        if (file_exists($publicPath)) {
            return $publicPath;
        }
        
        // Default to storage path
        return $storagePath;
    }

    /**
     * Get OCR verification summary for driver
     */
    public function getVerificationSummary($driver)
    {
        $summary = [
            'nin_verification' => [
                'status' => 'pending',
                'score' => 0,
                'verified_at' => null
            ],
            'frsc_verification' => [
                'status' => 'pending', 
                'score' => 0,
                'verified_at' => null
            ],
            'overall_status' => 'pending'
        ];

        if ($driver->nin_verification_data) {
            $ninData = json_decode($driver->nin_verification_data, true);
            $summary['nin_verification'] = [
                'status' => $driver->nin_ocr_match_score >= 80 ? 'passed' : 'failed',
                'score' => $driver->nin_ocr_match_score,
                'verified_at' => $driver->nin_verified_at
            ];
        }

        if ($driver->frsc_verification_data) {
            $frscData = json_decode($driver->frsc_verification_data, true);
            $summary['frsc_verification'] = [
                'status' => $driver->frsc_ocr_match_score >= 80 ? 'passed' : 'failed',
                'score' => $driver->frsc_ocr_match_score,
                'verified_at' => $driver->frsc_verified_at
            ];
        }

        // Determine overall status
        $ninPassed = $summary['nin_verification']['status'] === 'passed';
        $frscPassed = $summary['frsc_verification']['status'] === 'passed';
        
        if ($ninPassed && $frscPassed) {
            $summary['overall_status'] = 'passed';
        } elseif ($summary['nin_verification']['status'] === 'failed' || $summary['frsc_verification']['status'] === 'failed') {
            $summary['overall_status'] = 'failed';
        } else {
            $summary['overall_status'] = 'pending';
        }

        return $summary;
    }
}
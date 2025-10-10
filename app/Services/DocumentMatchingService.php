<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DocumentMatchingService
{
    /**
     * Compare driver-entered data with OCR extracted data
     * 
     * @param array $driverData Data entered by driver during registration
     * @param array $ocrData Data extracted from OCR
     * @param string $documentType Type of document being compared
     * @return array Comparison results with match scores
     */
    public function compareDocumentData($driverData, $ocrData, $documentType)
    {
        $comparisonResult = [
            'overall_match_score' => 0,
            'field_matches' => [],
            'discrepancies' => [],
            'match_status' => 'failed',
            'requires_manual_review' => false,
            'document_type' => $documentType,
            'timestamp' => now()
        ];

        try {
            Log::info('Starting document data comparison', [
                'document_type' => $documentType,
                'driver_fields' => array_keys($driverData),
                'ocr_fields' => array_keys($ocrData)
            ]);

            // Define comparison fields based on document type
            $comparisonFields = $this->getComparisonFields($documentType);
            
            $totalFields = count($comparisonFields);
            $matchedFields = 0;
            $totalScore = 0;

            foreach ($comparisonFields as $fieldConfig) {
                $field = $fieldConfig['field'];
                $weight = $fieldConfig['weight'];
                $threshold = $fieldConfig['threshold'];

                $fieldResult = $this->compareField(
                    $driverData[$field] ?? '',
                    $ocrData[$field] ?? '',
                    $field,
                    $threshold
                );

                $comparisonResult['field_matches'][$field] = $fieldResult;
                
                $weightedScore = $fieldResult['match_score'] * $weight;
                $totalScore += $weightedScore;

                if ($fieldResult['is_match']) {
                    $matchedFields++;
                } else {
                    $comparisonResult['discrepancies'][] = [
                        'field' => $field,
                        'driver_value' => $driverData[$field] ?? '',
                        'ocr_value' => $ocrData[$field] ?? '',
                        'match_score' => $fieldResult['match_score'],
                        'reason' => $fieldResult['reason']
                    ];
                }
            }

            // Calculate overall match score
            $comparisonResult['overall_match_score'] = $totalFields > 0 ? $totalScore / $totalFields : 0;

            // Determine match status
            $comparisonResult['match_status'] = $this->determineMatchStatus(
                $comparisonResult['overall_match_score'],
                $comparisonResult['discrepancies'],
                $documentType
            );

            // Check if manual review is required
            $comparisonResult['requires_manual_review'] = $this->requiresManualReview(
                $comparisonResult['overall_match_score'],
                $comparisonResult['discrepancies']
            );

            Log::info('Document comparison completed', [
                'document_type' => $documentType,
                'overall_score' => $comparisonResult['overall_match_score'],
                'match_status' => $comparisonResult['match_status'],
                'matched_fields' => $matchedFields,
                'total_fields' => $totalFields
            ]);

            return $comparisonResult;

        } catch (\Exception $e) {
            Log::error('Document comparison failed', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $comparisonResult['match_status'] = 'error';
            $comparisonResult['requires_manual_review'] = true;
            $comparisonResult['error'] = $e->getMessage();

            return $comparisonResult;
        }
    }

    /**
     * Get comparison fields configuration for each document type
     */
    private function getComparisonFields($documentType)
    {
        $configurations = [
            'nin' => [
                ['field' => 'first_name', 'weight' => 0.25, 'threshold' => 0.8],
                ['field' => 'surname', 'weight' => 0.25, 'threshold' => 0.8],
                ['field' => 'date_of_birth', 'weight' => 0.3, 'threshold' => 0.9],
                ['field' => 'nin', 'weight' => 0.2, 'threshold' => 1.0]
            ],
            'license' => [
                ['field' => 'first_name', 'weight' => 0.2, 'threshold' => 0.8],
                ['field' => 'surname', 'weight' => 0.2, 'threshold' => 0.8],
                ['field' => 'date_of_birth', 'weight' => 0.25, 'threshold' => 0.9],
                ['field' => 'license_number', 'weight' => 0.35, 'threshold' => 0.95]
            ],
            'bvn' => [
                ['field' => 'first_name', 'weight' => 0.25, 'threshold' => 0.8],
                ['field' => 'surname', 'weight' => 0.25, 'threshold' => 0.8],
                ['field' => 'date_of_birth', 'weight' => 0.3, 'threshold' => 0.9],
                ['field' => 'bvn', 'weight' => 0.2, 'threshold' => 1.0]
            ],
            'passport' => [
                ['field' => 'first_name', 'weight' => 0.2, 'threshold' => 0.8],
                ['field' => 'surname', 'weight' => 0.2, 'threshold' => 0.8],
                ['field' => 'date_of_birth', 'weight' => 0.25, 'threshold' => 0.9],
                ['field' => 'passport_number', 'weight' => 0.35, 'threshold' => 0.95]
            ]
        ];

        return $configurations[$documentType] ?? $configurations['nin'];
    }

    /**
     * Compare individual field values
     */
    private function compareField($driverValue, $ocrValue, $fieldName, $threshold)
    {
        $result = [
            'driver_value' => $driverValue,
            'ocr_value' => $ocrValue,
            'match_score' => 0,
            'is_match' => false,
            'reason' => '',
            'normalized_driver' => '',
            'normalized_ocr' => ''
        ];

        // Handle empty values
        if (empty($driverValue) && empty($ocrValue)) {
            $result['match_score'] = 1.0;
            $result['is_match'] = true;
            $result['reason'] = 'Both values are empty';
            return $result;
        }

        if (empty($driverValue) || empty($ocrValue)) {
            $result['match_score'] = 0;
            $result['is_match'] = false;
            $result['reason'] = 'One value is missing';
            return $result;
        }

        // Normalize values for comparison
        $normalizedDriver = $this->normalizeValue($driverValue, $fieldName);
        $normalizedOcr = $this->normalizeValue($ocrValue, $fieldName);

        $result['normalized_driver'] = $normalizedDriver;
        $result['normalized_ocr'] = $normalizedOcr;

        // Calculate similarity based on field type
        $similarity = $this->calculateSimilarity($normalizedDriver, $normalizedOcr, $fieldName);
        
        $result['match_score'] = $similarity;
        $result['is_match'] = $similarity >= $threshold;
        $result['reason'] = $this->getComparisonReason($similarity, $threshold, $fieldName);

        return $result;
    }

    /**
     * Normalize values for comparison
     */
    private function normalizeValue($value, $fieldName)
    {
        $normalized = trim($value);

        switch ($fieldName) {
            case 'first_name':
            case 'surname':
            case 'middle_name':
                // Remove extra spaces, convert to lowercase for comparison
                $normalized = preg_replace('/\s+/', ' ', strtolower($normalized));
                // Remove common titles and suffixes
                $normalized = preg_replace('/\b(mr|mrs|miss|dr|prof|jr|sr|ii|iii)\b\.?/i', '', $normalized);
                break;

            case 'date_of_birth':
                // Try to parse and standardize date format
                $normalized = $this->standardizeDateForComparison($normalized);
                break;

            case 'nin':
            case 'bvn':
            case 'license_number':
            case 'passport_number':
                // Remove spaces and special characters for ID numbers
                $normalized = preg_replace('/[^\w]/', '', strtoupper($normalized));
                break;

            case 'phone':
                // Standardize phone format
                $normalized = preg_replace('/\D/', '', $normalized);
                if (strlen($normalized) === 11 && substr($normalized, 0, 1) === '0') {
                    $normalized = '234' . substr($normalized, 1);
                }
                break;
        }

        return trim($normalized);
    }

    /**
     * Calculate similarity between two values
     */
    private function calculateSimilarity($value1, $value2, $fieldName)
    {
        if ($value1 === $value2) {
            return 1.0;
        }

        switch ($fieldName) {
            case 'nin':
            case 'bvn':
            case 'license_number':
            case 'passport_number':
                // Exact match required for IDs
                return $value1 === $value2 ? 1.0 : 0.0;

            case 'date_of_birth':
                // Date comparison with some tolerance
                return $this->compareDates($value1, $value2);

            case 'first_name':
            case 'surname':
            case 'middle_name':
                // Use fuzzy string matching for names
                return $this->fuzzyStringMatch($value1, $value2);

            default:
                // Generic string similarity
                return $this->calculateStringSimilarity($value1, $value2);
        }
    }

    /**
     * Compare dates with tolerance for different formats
     */
    private function compareDates($date1, $date2)
    {
        try {
            $d1 = \DateTime::createFromFormat('Y-m-d', $date1) ?: 
                  \DateTime::createFromFormat('d/m/Y', $date1) ?: 
                  \DateTime::createFromFormat('m/d/Y', $date1);
                  
            $d2 = \DateTime::createFromFormat('Y-m-d', $date2) ?: 
                  \DateTime::createFromFormat('d/m/Y', $date2) ?: 
                  \DateTime::createFromFormat('m/d/Y', $date2);

            if (!$d1 || !$d2) {
                return $this->calculateStringSimilarity($date1, $date2);
            }

            return $d1->format('Y-m-d') === $d2->format('Y-m-d') ? 1.0 : 0.0;
            
        } catch (\Exception $e) {
            return $this->calculateStringSimilarity($date1, $date2);
        }
    }

    /**
     * Fuzzy string matching for names
     */
    private function fuzzyStringMatch($str1, $str2)
    {
        // Levenshtein distance similarity
        $levenshtein = 1 - (levenshtein($str1, $str2) / max(strlen($str1), strlen($str2)));
        
        // Similar text percentage
        similar_text($str1, $str2, $percent);
        $similarText = $percent / 100;

        // Soundex comparison for phonetic similarity
        $soundex = soundex($str1) === soundex($str2) ? 1.0 : 0.0;

        // Token-based comparison for multi-word names
        $tokens1 = explode(' ', $str1);
        $tokens2 = explode(' ', $str2);
        $tokenMatch = $this->calculateTokenSimilarity($tokens1, $tokens2);

        // Weighted average of different similarity measures
        $weights = [0.3, 0.3, 0.2, 0.2]; // levenshtein, similar_text, soundex, token
        $scores = [$levenshtein, $similarText, $soundex, $tokenMatch];

        return array_sum(array_map(function($w, $s) { return $w * $s; }, $weights, $scores));
    }

    /**
     * Calculate token-based similarity for multi-word strings
     */
    private function calculateTokenSimilarity($tokens1, $tokens2)
    {
        if (empty($tokens1) && empty($tokens2)) {
            return 1.0;
        }

        if (empty($tokens1) || empty($tokens2)) {
            return 0.0;
        }

        $matches = 0;
        $total = max(count($tokens1), count($tokens2));

        foreach ($tokens1 as $token1) {
            $bestMatch = 0;
            foreach ($tokens2 as $token2) {
                $similarity = $this->calculateStringSimilarity($token1, $token2);
                $bestMatch = max($bestMatch, $similarity);
            }
            if ($bestMatch > 0.8) {
                $matches++;
            }
        }

        return $matches / $total;
    }

    /**
     * Calculate basic string similarity
     */
    private function calculateStringSimilarity($str1, $str2)
    {
        if (empty($str1) && empty($str2)) {
            return 1.0;
        }

        if (empty($str1) || empty($str2)) {
            return 0.0;
        }

        similar_text($str1, $str2, $percent);
        return $percent / 100;
    }

    /**
     * Standardize date format for comparison
     */
    private function standardizeDateForComparison($dateString)
    {
        try {
            $date = \DateTime::createFromFormat('Y-m-d', $dateString) ?: 
                   \DateTime::createFromFormat('d/m/Y', $dateString) ?: 
                   \DateTime::createFromFormat('m/d/Y', $dateString) ?: 
                   \DateTime::createFromFormat('d-m-Y', $dateString) ?: 
                   new \DateTime($dateString);

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return $dateString;
        }
    }

    /**
     * Get comparison reason based on score and threshold
     */
    private function getComparisonReason($score, $threshold, $fieldName)
    {
        if ($score >= $threshold) {
            return 'Values match within acceptable threshold';
        }

        if ($score >= 0.5) {
            return 'Partial match - may require manual review';
        }

        return 'Values do not match';
    }

    /**
     * Determine overall match status
     */
    private function determineMatchStatus($overallScore, $discrepancies, $documentType)
    {
        // High confidence match
        if ($overallScore >= 0.9 && count($discrepancies) === 0) {
            return 'verified';
        }

        // Good match with minor discrepancies
        if ($overallScore >= 0.8 && count($discrepancies) <= 1) {
            return 'partial_match';
        }

        // Moderate match - needs review
        if ($overallScore >= 0.6) {
            return 'review_required';
        }

        // Poor match
        return 'failed';
    }

    /**
     * Check if manual review is required
     */
    private function requiresManualReview($overallScore, $discrepancies)
    {
        // Require manual review if:
        // 1. Overall score is between 0.6 and 0.9
        // 2. There are discrepancies in critical fields
        // 3. Score is very low

        if ($overallScore < 0.6 || $overallScore > 0.9) {
            return $overallScore < 0.9; // Only if score is low
        }

        // Check for critical field discrepancies
        $criticalFields = ['nin', 'license_number', 'bvn', 'passport_number', 'date_of_birth'];
        
        foreach ($discrepancies as $discrepancy) {
            if (in_array($discrepancy['field'], $criticalFields)) {
                return true;
            }
        }

        return count($discrepancies) > 2; // Too many discrepancies
    }

    /**
     * Generate comparison report
     */
    public function generateComparisonReport($comparisonResult)
    {
        $report = [
            'summary' => [
                'overall_score' => round($comparisonResult['overall_match_score'] * 100, 2) . '%',
                'status' => $comparisonResult['match_status'],
                'requires_review' => $comparisonResult['requires_manual_review'],
                'total_fields_compared' => count($comparisonResult['field_matches']),
                'discrepancies_found' => count($comparisonResult['discrepancies'])
            ],
            'details' => $comparisonResult,
            'recommendations' => $this->generateRecommendations($comparisonResult),
            'generated_at' => now()->toISOString()
        ];

        return $report;
    }

    /**
     * Generate recommendations based on comparison results
     */
    private function generateRecommendations($comparisonResult)
    {
        $recommendations = [];

        if ($comparisonResult['match_status'] === 'verified') {
            $recommendations[] = 'Document verification successful. Proceed with account activation.';
        }

        if ($comparisonResult['match_status'] === 'partial_match') {
            $recommendations[] = 'Minor discrepancies found. Consider manual review or accept with caution.';
        }

        if ($comparisonResult['match_status'] === 'review_required') {
            $recommendations[] = 'Manual review required before approval.';
            $recommendations[] = 'Contact driver to clarify discrepancies.';
        }

        if ($comparisonResult['match_status'] === 'failed') {
            $recommendations[] = 'Document verification failed. Request new documents.';
            $recommendations[] = 'Reject application or require document resubmission.';
        }

        if (!empty($comparisonResult['discrepancies'])) {
            foreach ($comparisonResult['discrepancies'] as $discrepancy) {
                $recommendations[] = "Verify {$discrepancy['field']}: Driver entered '{$discrepancy['driver_value']}', document shows '{$discrepancy['ocr_value']}'";
            }
        }

        return $recommendations;
    }
}
<?php

namespace App\Services\DriverVerification;

use Illuminate\Support\Facades\Log;

class ScoringService
{
    /**
     * Calculate verification score based on various factors
     *
     * @param array $verificationData
     * @return int Score between 0-100
     */
    public function calculateScore($verificationData)
    {
        try {
            $score = 0;
            $maxScore = 100;
            $weights = [
                'facial_match' => 30,
                'document_verification' => 25,
                'background_check' => 20,
                'reference_check' => 15,
                'data_consistency' => 10,
            ];

            // Facial matching score
            if (isset($verificationData['facial_score'])) {
                $facialScore = min(100, max(0, $verificationData['facial_score'] * 100));
                $score += ($facialScore * $weights['facial_match']) / 100;
            } else {
                // Default facial score if not provided
                $score += (80 * $weights['facial_match']) / 100; // Assume 80% match if not checked
            }

            // Document verification score
            $docScore = $this->calculateDocumentScore($verificationData);
            $score += ($docScore * $weights['document_verification']) / 100;

            // Background check score
            $bgScore = $this->calculateBackgroundScore($verificationData);
            $score += ($bgScore * $weights['background_check']) / 100;

            // Reference check score
            $refScore = $this->calculateReferenceScore($verificationData);
            $score += ($refScore * $weights['reference_check']) / 100;

            // Data consistency score
            $consistencyScore = $this->calculateConsistencyScore($verificationData);
            $score += ($consistencyScore * $weights['data_consistency']) / 100;

            $finalScore = min(100, max(0, round($score)));
            // Ensure perfect score is achievable
            if ($score >= 99.99) {
                $finalScore = 100;
            }

            Log::info('Verification score calculated', [
                'final_score' => $finalScore,
                'components' => [
                    'facial' => $facialScore ?? 0,
                    'document' => $docScore,
                    'background' => $bgScore,
                    'reference' => $refScore,
                    'consistency' => $consistencyScore,
                ]
            ]);

            return $finalScore;

        } catch (\Exception $e) {
            Log::error('Score calculation error: ' . $e->getMessage());
            return 50; // Default neutral score
        }
    }

    /**
     * Calculate document verification score
     */
    private function calculateDocumentScore($data)
    {
        $score = 0;

        // License verification
        if (isset($data['license_verified']) && $data['license_verified']) {
            $score += 40;
        }

        // ID verification
        if (isset($data['id_verified']) && $data['id_verified']) {
            $score += 30;
        }

        // Address verification
        if (isset($data['address_verified']) && $data['address_verified']) {
            $score += 20;
        }

        // Document expiry check
        if (isset($data['documents_valid']) && $data['documents_valid']) {
            $score += 10;
        }

        return min(100, $score);
    }

    /**
     * Calculate background check score
     */
    private function calculateBackgroundScore($data)
    {
        $score = 0;

        // Criminal record check
        if (isset($data['criminal_check_passed']) && $data['criminal_check_passed']) {
            $score += 40;
        }

        // Driving record check
        if (isset($data['driving_record_good']) && $data['driving_record_good']) {
            $score += 30;
        }

        // Previous employment verification
        if (isset($data['employment_verified']) && $data['employment_verified']) {
            $score += 30;
        }

        return min(100, $score);
    }

    /**
     * Calculate reference check score
     */
    private function calculateReferenceScore($data)
    {
        $score = 0;

        if (isset($data['references'])) {
            $totalRefs = count($data['references']);
            $positiveRefs = 0;

            foreach ($data['references'] as $ref) {
                if (isset($ref['verified']) && $ref['verified']) {
                    $positiveRefs++;
                }
            }

            if ($totalRefs > 0) {
                $score = ($positiveRefs / $totalRefs) * 100;
            }
        }

        return $score;
    }

    /**
     * Calculate data consistency score
     */
    private function calculateConsistencyScore($data)
    {
        $score = 100;

        // Check if name matches across documents
        if (isset($data['name_matches']) && !$data['name_matches']) {
            $score -= 30;
        }

        // Check if dates are consistent
        if (isset($data['dates_consistent']) && !$data['dates_consistent']) {
            $score -= 20;
        }

        // Check if addresses match
        if (isset($data['addresses_match']) && !$data['addresses_match']) {
            $score -= 20;
        }

        // Check for data completeness
        if (isset($data['data_completeness'])) {
            $score = ($score * $data['data_completeness']) / 100;
        }

        return max(0, $score);
    }
}

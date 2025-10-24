<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ScoringService
{
    /**
     * Calculate verification score based on OCR results, face match, and validation results
     *
     * @param array $ocrResults OCR extraction results with confidence scores
     * @param float $faceMatchScore Face matching score (0-1)
     * @param array $validationResults Validation consistency scores
     * @param array $options Additional options (e.g., custom weights)
     * @return array ['score' => float, 'breakdown' => array]
     */
    public function calculate(array $ocrResults, float $faceMatchScore, array $validationResults, array $options = []): array
    {
        // Get weights from options or default
        $weights = $options['weights'] ?? [
            'ocr_accuracy' => 0.4,
            'face_match' => 0.4,
            'validation_consistency' => 0.2,
        ];

        // Calculate individual factor scores
        $factorScores = [
            'ocr_accuracy' => $this->calculateOcrAccuracy($ocrResults),
            'face_match' => $faceMatchScore,
            'validation_consistency' => $this->calculateValidationConsistency($validationResults),
        ];

        // Calculate weighted score
        $totalScore = 0;
        $breakdown = [];

        foreach ($factorScores as $factor => $score) {
            $weightedScore = $score * $weights[$factor];
            $totalScore += $weightedScore;
            $breakdown[$factor] = [
                'raw_score' => $score,
                'weight' => $weights[$factor],
                'weighted_contribution' => $weightedScore,
            ];
        }

        // Normalize to 0-100 scale
        $normalizedScore = round($totalScore * 100, 2);

        return [
            'score' => $normalizedScore,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate OCR accuracy score based on confidence values
     *
     * @param array $ocrResults
     * @return float
     */
    private function calculateOcrAccuracy(array $ocrResults): float
    {
        $confidences = [];

        // Extract confidence scores from OCR results
        foreach ($ocrResults as $document => $fields) {
            if (isset($fields['confidence'])) {
                $confidences[] = $fields['confidence'];
            } else {
                // If no confidence, assume 0.5 as neutral
                $confidences[] = 0.5;
            }
        }

        if (empty($confidences)) {
            return 0.0;
        }

        // Return average confidence
        return round(array_sum($confidences) / count($confidences), 3);
    }

    /**
     * Calculate validation consistency score
     *
     * @param array $validationResults
     * @return float
     */
    private function calculateValidationConsistency(array $validationResults): float
    {
        if (empty($validationResults) || !isset($validationResults['scores'])) {
            return 0.0;
        }

        $scores = $validationResults['scores'];
        if (empty($scores)) {
            return 0.0;
        }

        // Return average of validation scores
        return round(array_sum($scores) / count($scores), 3);
    }

    /**
     * Get verification rules from database or fallback to config
     *
     * @return array
     */
    private function getRules(): array
    {
        try {
            $rules = DB::table('verification_rules')
                ->where('active', true)
                ->get()
                ->toArray();

            if (!empty($rules)) {
                return $rules;
            }
        } catch (\Exception $e) {
            // Fallback to config if DB not available
        }

        // Fallback to config
        $configRules = Config::get('verification.weights', []);
        $rules = [];
        foreach ($configRules as $factor => $weight) {
            $rules[] = [
                'factor' => $factor,
                'weight' => $weight,
                'rules' => Config::get("verification.rules.{$factor}", []),
            ];
        }

        return $rules;
    }
}

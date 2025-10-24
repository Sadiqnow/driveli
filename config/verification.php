<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Verification Scoring Configuration
    |--------------------------------------------------------------------------
    |
    | Weights for different verification factors. Weights should sum to 1.0
    | for proper normalization. Each factor contributes to the overall score.
    |
    */

    'weights' => [
        'ocr_accuracy' => 0.4,      // Weight for OCR result accuracy (0-1)
        'face_match' => 0.4,        // Weight for face matching score (0-1)
        'validation_consistency' => 0.2, // Weight for validation consistency (0-1)
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring Rules
    |--------------------------------------------------------------------------
    |
    | Rules for computing individual factor scores.
    |
    */

    'rules' => [
        'ocr_accuracy' => [
            'method' => 'average_confidence', // Average confidence from OCR results
            'threshold' => 0.8, // Minimum acceptable confidence
        ],
        'face_match' => [
            'threshold' => 0.7, // Minimum acceptable face match score
        ],
        'validation_consistency' => [
            'method' => 'average_scores', // Average of validation scores
            'threshold' => 0.8, // Minimum acceptable consistency
        ],
    ],
];

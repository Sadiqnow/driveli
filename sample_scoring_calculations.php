<?php

require_once 'vendor/autoload.php';

use App\Services\ScoringService;

// Initialize the service with default weights (since Laravel facades not available)
$scoringService = new ScoringService();
$options = [
    'weights' => [
        'ocr_accuracy' => 0.4,
        'face_match' => 0.4,
        'validation_consistency' => 0.2,
    ]
];

// Sample 1: High confidence OCR, good face match, consistent validation
$ocrResults1 = [
    'license' => ['confidence' => 0.95],
    'nin' => ['confidence' => 0.92],
];

$faceMatchScore1 = 0.88;

$validationResults1 = [
    'scores' => [
        'name_similarity' => 0.95,
        'dob_match' => 1.0,
        'expiry_check' => 1.0,
        'nin_regex' => 1.0,
        'duplicate_license' => 1.0,
    ]
];

$result1 = $scoringService->calculate($ocrResults1, $faceMatchScore1, $validationResults1);

echo "Sample 1 - High Quality Verification:\n";
echo "Score: " . $result1['score'] . "/100\n";
echo "Breakdown:\n";
foreach ($result1['breakdown'] as $factor => $details) {
    echo "  {$factor}: {$details['raw_score']} * {$details['weight']} = {$details['weighted_contribution']}\n";
}
echo "\n";

// Sample 2: Low OCR confidence, poor face match, inconsistent validation
$ocrResults2 = [
    'license' => ['confidence' => 0.65],
    'nin' => ['confidence' => 0.58],
];

$faceMatchScore2 = 0.45;

$validationResults2 = [
    'scores' => [
        'name_similarity' => 0.72,
        'dob_match' => 0.0,
        'expiry_check' => 1.0,
        'nin_regex' => 0.0,
        'duplicate_license' => 1.0,
    ]
];

$result2 = $scoringService->calculate($ocrResults2, $faceMatchScore2, $validationResults2);

echo "Sample 2 - Low Quality Verification:\n";
echo "Score: " . $result2['score'] . "/100\n";
echo "Breakdown:\n";
foreach ($result2['breakdown'] as $factor => $details) {
    echo "  {$factor}: {$details['raw_score']} * {$details['weight']} = {$details['weighted_contribution']}\n";
}
echo "\n";

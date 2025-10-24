<?php

require_once 'vendor/autoload.php';

use App\Services\ValidationService;

// Mock driver object
class MockDriver {
    public $id = 1;
    public $first_name = 'John';
    public $surname = 'Doe';
    public $date_of_birth = '1990-01-01';
    public $nin = '12345678901';
    public $license_number = 'DL123456';
}

$driver = new MockDriver();

// Sample OCR results
$ocrResults = [
    'license' => [
        'first_name' => 'John',
        'surname' => 'Doe',
        'date_of_birth' => '1990-01-01',
        'expiry_date' => '2025-12-31',
        'license_number' => 'DL123456'
    ],
    'nin' => [
        'nin' => '12345678901',
        'first_name' => 'John',
        'surname' => 'Doe',
        'date_of_birth' => '1990-01-01'
    ]
];

// Initialize ValidationService
$validationService = new ValidationService();

// Run consistency check
$result = $validationService->checkConsistency($driver, $ocrResults);

// Output the result
echo "Validation Result:\n";
echo "Flags: " . implode(', ', $result['flags']) . "\n";
echo "Scores:\n";
foreach ($result['scores'] as $rule => $score) {
    echo "  $rule: $score\n";
}

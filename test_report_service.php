<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ReportService;
use App\Models\Drivers;
use Illuminate\Support\Facades\DB;

echo "Testing ReportService...\n\n";

// Get a driver
$driver = Drivers::first();
if (!$driver) {
    echo "No driver found. Creating test driver...\n";
    $driver = Drivers::create([
        'driver_id' => 'TEST001',
        'first_name' => 'Test',
        'last_name' => 'Driver',
        'email' => 'test@example.com',
        'phone' => '08012345678',
        'verification_status' => 'pending'
    ]);
}

$reportService = app(ReportService::class);

// Test data
$ocrResults = [
    'license' => ['confidence' => 0.85, 'text' => 'Valid license'],
    'id_card' => ['confidence' => 0.90, 'text' => 'Valid ID']
];
$faceMatch = 0.88;
$validationResults = ['scores' => [0.9, 0.85, 0.95]];
$score = 87.5;

echo "1. Testing saveResults...\n";
$result = $reportService->saveResults($driver, $score, $ocrResults, $faceMatch, $validationResults);
echo "   Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($result['success']) {
    echo "   Log ID: {$result['log_id']}\n";
    echo "   Status: {$result['status']}\n";
    echo "   Score: {$result['score']}\n";
} else {
    echo "   Error: {$result['error']}\n";
}

echo "\n2. Testing generate report...\n";
$reportResult = $reportService->generate($driver->id);
echo "   Result: " . ($reportResult['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($reportResult['success']) {
    $report = $reportResult['report'];
    echo "   Driver: {$report['driver']['name']}\n";
    echo "   Status: {$report['driver']['verification_status']}\n";
    echo "   Score: {$report['driver']['overall_score']}\n";
    echo "   Logs count: " . count($report['logs']) . "\n";
    echo "   Documents count: " . count($report['documents']) . "\n";
    echo "   Recommendations count: " . count($report['recommendations']) . "\n";

    // Show sample JSON
    echo "\n3. Sample report JSON (first 500 chars):\n";
    echo substr(json_encode($report, JSON_PRETTY_PRINT), 0, 500) . "...\n";
} else {
    echo "   Error: {$reportResult['error']}\n";
}

echo "\n4. Checking database updates...\n";
$updatedDriver = Drivers::find($driver->id);
echo "   Driver status: {$updatedDriver->verification_status}\n";
echo "   Driver score: {$updatedDriver->overall_verification_score}\n";

$logsCount = DB::table('driver_verification_logs')->where('driver_id', $driver->id)->count();
echo "   Total logs for driver: {$logsCount}\n";

$latestLog = DB::table('driver_verification_logs')
    ->where('driver_id', $driver->id)
    ->latest()
    ->first();

if ($latestLog) {
    echo "\n5. Latest log details:\n";
    echo "   Action: {$latestLog->action}\n";
    echo "   Status: {$latestLog->status}\n";
    echo "   Confidence score: {$latestLog->confidence_score}\n";
    echo "   Notes: {$latestLog->notes}\n";

    $verificationData = json_decode($latestLog->verification_data, true);
    $resultData = json_decode($latestLog->result_data, true);

    echo "   Has OCR results: " . (isset($verificationData['ocr_results']) ? 'Yes' : 'No') . "\n";
    echo "   Has face match: " . (isset($verificationData['face_match_score']) ? 'Yes' : 'No') . "\n";
    echo "   Has validation results: " . (isset($verificationData['validation_results']) ? 'Yes' : 'No') . "\n";
    echo "   Has result breakdown: " . (isset($resultData['breakdown']) ? 'Yes' : 'No') . "\n";
}

echo "\nTest completed.\n";

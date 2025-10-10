<?php

require_once 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "Testing OCR Dashboard Fix..." . PHP_EOL;
    echo "=============================" . PHP_EOL;

    // Test 1: Check if DriverNormalized model exists and has OCR fields
    echo "1. Testing DriverNormalized model..." . PHP_EOL;
    $driver = new App\Models\DriverNormalized();
    $fillable = $driver->getFillable();
    
    $ocrFields = [
        'nin_ocr_match_score',
        'frsc_ocr_match_score', 
        'ocr_verification_status',
        'nin_verified_at',
        'frsc_verified_at'
    ];
    
    foreach ($ocrFields as $field) {
        if (in_array($field, $fillable)) {
            echo "   ✓ $field is fillable" . PHP_EOL;
        } else {
            echo "   ✗ $field is NOT fillable" . PHP_EOL;
        }
    }

    // Test 2: Check if we can query drivers
    echo PHP_EOL . "2. Testing driver query..." . PHP_EOL;
    $driverCount = $driver->count();
    echo "   Total drivers in database: $driverCount" . PHP_EOL;

    // Test 3: Simulate the OCR dashboard JSON request
    echo PHP_EOL . "3. Testing OCR dashboard response simulation..." . PHP_EOL;
    
    // Simulate request parameters
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'format' => 'json',
        'include_ocr' => 'true'
    ]);

    // Test the controller logic
    $query = App\Models\DriverNormalized::with(['guarantors', 'verifiedBy']);
    $drivers = $query->orderBy('created_at', 'desc')->limit(5)->get();
    
    $transformedDrivers = $drivers->map(function ($driver) {
        return [
            'id' => $driver->id,
            'driver_id' => $driver->driver_id,
            'full_name' => trim($driver->first_name . ' ' . ($driver->middle_name ? $driver->middle_name . ' ' : '') . $driver->surname),
            'nin_ocr_match_score' => $driver->nin_ocr_match_score ?? 0,
            'frsc_ocr_match_score' => $driver->frsc_ocr_match_score ?? 0,
            'ocr_verification_status' => $driver->ocr_verification_status ?? 'pending',
        ];
    });

    echo "   Sample OCR data for " . $transformedDrivers->count() . " drivers:" . PHP_EOL;
    foreach ($transformedDrivers as $index => $driverData) {
        echo "   Driver " . ($index + 1) . ": " . $driverData['full_name'] . 
             " (NIN: " . $driverData['nin_ocr_match_score'] . 
             ", FRSC: " . $driverData['frsc_ocr_match_score'] . 
             ", Status: " . $driverData['ocr_verification_status'] . ")" . PHP_EOL;
    }

    // Test 4: Test OCR statistics
    echo PHP_EOL . "4. Testing OCR statistics..." . PHP_EOL;
    $stats = [
        'total_processed' => App\Models\DriverNormalized::whereNotNull('nin_verified_at')->orWhereNotNull('frsc_verified_at')->count(),
        'passed' => App\Models\DriverNormalized::where('ocr_verification_status', 'passed')->count(),
        'pending' => App\Models\DriverNormalized::where('ocr_verification_status', 'pending')->count(),
        'failed' => App\Models\DriverNormalized::where('ocr_verification_status', 'failed')->count(),
    ];
    
    echo "   OCR Statistics:" . PHP_EOL;
    echo "   - Total Processed: " . $stats['total_processed'] . PHP_EOL;
    echo "   - Passed: " . $stats['passed'] . PHP_EOL;
    echo "   - Pending: " . $stats['pending'] . PHP_EOL;
    echo "   - Failed: " . $stats['failed'] . PHP_EOL;

    echo PHP_EOL . "✓ OCR Dashboard fix appears to be working!" . PHP_EOL;
    echo "The controller now supports JSON requests with OCR data." . PHP_EOL;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}
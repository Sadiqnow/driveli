<?php

require 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DriverNormalized;

try {
    echo "Testing driver creation with KYC fields...\n";
    
    $driver = DriverNormalized::create([
        'driver_id' => 'DR' . rand(100000, 999999),
        'first_name' => 'Test',
        'surname' => 'Driver',
        'email' => 'test' . rand(1000, 9999) . '@example.com',
        'phone' => '2348012345678',
        'password' => 'password123',
        'status' => 'active',
        'verification_status' => 'pending',
        'license_number' => '123456789',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
        'created_by' => 1,
    ]);
    
    echo "✅ Driver created successfully!\n";
    echo "Driver ID: {$driver->driver_id}\n";
    echo "KYC Status: {$driver->kyc_status}\n";
    echo "KYC Step: {$driver->kyc_step}\n";
    echo "KYC Rejection Reason: " . ($driver->kyc_rejection_reason ?? 'null') . "\n";
    
    // Clean up - delete the test driver
    $driver->delete();
    echo "✅ Test driver cleaned up\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
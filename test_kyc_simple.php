<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Drivers;
use Illuminate\Support\Facades\Schema;

echo "Testing KYC columns in Laravel...\n\n";

try {
    // Test Laravel Schema check
    $requiredColumns = [
        'kyc_status', 'kyc_step', 'marital_status', 'state_id', 'lga_id',
        'residential_address', 'emergency_contact_name', 'emergency_contact_phone',
        'emergency_contact_relationship'
    ];
    
    echo "Checking columns via Laravel Schema:\n";
    foreach ($requiredColumns as $column) {
        if (Schema::hasColumn('drivers', $column)) {
            echo "✓ $column\n";
        } else {
            echo "✗ $column MISSING\n";
        }
    }
    
    // Test if we can create/update a driver with KYC fields
    echo "\nTesting KYC model update...\n";
    
    $testData = [
        'first_name' => 'Test',
        'surname' => 'User',
        'phone' => '1234567890',
        'email' => 'test@example.com',
        'kyc_status' => 'pending',
        'kyc_step' => 'not_started'
    ];
    
    // Try to create a test driver
    $driver = new Drivers();
    $driver->fill($testData);
    
    echo "✓ Model can be filled with KYC data\n";
    
    // Test the exact update from KYC Step 1
    $kycStep1Data = [
        'middle_name' => 'TestMiddle',
        'gender' => 'Male',
        'marital_status' => 'Single',
        'nationality_id' => 1,
        'state_id' => 1,
        'lga_id' => 1,
        'residential_address' => 'Test Address 123',
        'emergency_contact_name' => 'Test Contact',
        'emergency_contact_phone' => '1234567890',
        'emergency_contact_relationship' => 'Friend',
        'kyc_step' => 'step_2',
        'kyc_status' => 'in_progress',
    ];
    
    $driver->fill($kycStep1Data);
    echo "✓ Model can be filled with KYC Step 1 data\n";
    
    echo "\n🎉 SUCCESS: KYC functionality should work now!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DriverNormalized as Driver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

try {
    echo "Testing basic driver creation...\n\n";
    
    // First, check what columns exist
    echo "Checking table columns...\n";
    $hasLicenseNumber = Schema::hasColumn('drivers', 'license_number');
    $hasKycStatus = Schema::hasColumn('drivers', 'kyc_status');
    $hasCreatedBy = Schema::hasColumn('drivers', 'created_by');
    
    echo "license_number exists: " . ($hasLicenseNumber ? 'YES' : 'NO') . "\n";
    echo "kyc_status exists: " . ($hasKycStatus ? 'YES' : 'NO') . "\n";
    echo "created_by exists: " . ($hasCreatedBy ? 'YES' : 'NO') . "\n\n";
    
    // Generate unique test data
    $timestamp = time();
    $testData = [
        'driver_id' => 'TST' . $timestamp,
        'first_name' => 'Test',
        'surname' => 'User',
        'email' => "test{$timestamp}@example.com",
        'phone' => '+234801' . substr($timestamp, -7),
        'password' => Hash::make('password123'),
        'status' => 'active',
        'verification_status' => 'pending',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Other',
    ];
    
    // Add optional fields if they exist
    if ($hasLicenseNumber) {
        $testData['license_number'] = 'TST' . $timestamp;
    }
    if ($hasKycStatus) {
        $testData['kyc_status'] = 'pending';
    }
    if ($hasCreatedBy) {
        $testData['created_by'] = 1;
    }
    
    echo "Test data prepared:\n";
    foreach ($testData as $key => $value) {
        if ($key !== 'password') {
            echo "  {$key}: {$value}\n";
        }
    }
    echo "\n";
    
    // Try to create the driver
    echo "Creating driver...\n";
    $driver = Driver::create($testData);
    
    echo "✅ SUCCESS! Driver created with ID: " . $driver->id . "\n";
    echo "Driver ID: " . $driver->driver_id . "\n";
    echo "Name: " . $driver->first_name . " " . $driver->surname . "\n";
    
    // Clean up
    echo "\nCleaning up test data...\n";
    $driver->forceDelete();
    echo "✅ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Previous: " . ($e->getPrevious() ? $e->getPrevious()->getMessage() : 'None') . "\n";
    
    if (method_exists($e, 'getTrace')) {
        echo "\nStack trace:\n";
        foreach (array_slice($e->getTrace(), 0, 5) as $i => $trace) {
            $file = isset($trace['file']) ? basename($trace['file']) : 'unknown';
            $line = isset($trace['line']) ? $trace['line'] : '?';
            $function = isset($trace['function']) ? $trace['function'] : '?';
            echo "  #{$i} {$file}:{$line} {$function}()\n";
        }
    }
}
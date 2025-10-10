<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\DriverNormalized;
use App\Helpers\DrivelinkHelper;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    echo "Testing Driver Creation...\n";
    
    // Test database connection
    $connection = DB::connection();
    echo "Database connection: OK\n";
    
    // Test DriverNormalized model
    $driversCount = DriverNormalized::count();
    echo "Current drivers count: {$driversCount}\n";
    
    // Test DrivelinkHelper
    $driverId = DrivelinkHelper::generateDriverId();
    echo "Generated Driver ID: {$driverId}\n";
    
    // Test basic driver creation
    $testData = [
        'driver_id' => $driverId,
        'first_name' => 'Test',
        'surname' => 'Driver',
        'email' => 'test' . time() . '@example.com',
        'phone' => '080' . rand(10000000, 99999999),
        'password' => 'password123',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'nationality_id' => 1,
        'nin_number' => '12345678901',
        'license_number' => 'LIC' . rand(100000, 999999),
        'license_class' => 'Class C',
        'license_expiry_date' => '2025-12-31',
        'status' => 'active',
        'verification_status' => 'pending',
        'is_active' => true,
        'registered_at' => now(),
    ];
    
    echo "Creating test driver...\n";
    $driver = DriverNormalized::create($testData);
    
    if ($driver) {
        echo "SUCCESS: Driver created with ID: {$driver->id}\n";
        echo "Driver details:\n";
        echo "- Name: {$driver->full_name}\n";
        echo "- Email: {$driver->email}\n";
        echo "- Phone: {$driver->phone}\n";
        echo "- Status: {$driver->status}\n";
        echo "- Verification: {$driver->verification_status}\n";
    } else {
        echo "FAILED: Could not create driver\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
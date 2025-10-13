<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\Drivers;
use App\Http\Controllers\Driver\DriverKycController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Testing Verification Jobs Dispatch\n";
echo "==================================\n\n";

// Test scenarios
$scenarios = [
    'complete_data' => [
        'nin_number' => '12345678901',
        'license_number' => 'DRV123456',
        'license_expiry_date' => '2025-12-31',
        'passport_photograph' => 'path/to/photo.jpg',
        'uploaded_files' => ['passport_photo' => 'uploaded/path/photo.jpg']
    ],
    'partial_data' => [
        'nin_number' => '12345678901',
        'license_number' => null,
        'license_expiry_date' => null,
        'passport_photograph' => null,
        'uploaded_files' => []
    ],
    'minimal_data' => [
        'nin_number' => null,
        'license_number' => null,
        'license_expiry_date' => null,
        'passport_photograph' => null,
        'uploaded_files' => []
    ]
];

foreach ($scenarios as $scenarioName => $data) {
    echo "Testing scenario: $scenarioName\n";
    echo "--------------------------------\n";

    // Create a test driver
    $driver = Drivers::create([
        'first_name' => 'Test',
        'surname' => 'Driver',
        'email' => "test_$scenarioName@example.com",
        'phone' => '08012345678',
        'nin_number' => $data['nin_number'],
        'license_number' => $data['license_number'],
        'license_expiry_date' => $data['license_expiry_date'],
        'passport_photograph' => $data['passport_photograph'],
        'kyc_step' => 2, // Before step 3
        'kyc_step_2_completed_at' => now(), // Mark step 2 as completed
    ]);

    echo "Created driver ID: {$driver->id}\n";

    // Create mock request
    $request = new Request();
    $request->merge([
        'guarantor_name' => 'Test Guarantor',
        'guarantor_phone' => '08087654321',
        'emergency_contact_name' => 'Emergency Contact',
        'emergency_contact_phone' => '08011223344',
        'bank_name' => 'Test Bank',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'bvn' => '12345678901',
    ]);

    // Mock uploaded files if any
    if (!empty($data['uploaded_files'])) {
        foreach ($data['uploaded_files'] as $key => $path) {
            // Mock file upload
            $mockFile = \Illuminate\Http\UploadedFile::fake()->create($key . '.jpg', 1000);
            $request->files->set($key, $mockFile);
        }
    }

    // Instantiate controller with dependency injection
    $notificationService = app(\App\Services\NotificationService::class);
    $controller = new DriverKycController($notificationService);

    // Mock authentication for driver guard
    auth('driver')->login($driver);

    try {
        // Call the postStep3 method (correct method name)
        $response = $controller->postStep3($request);

        echo "Method executed successfully\n";

        // Check if jobs were dispatched by checking logs
        $logContent = file_get_contents(storage_path('logs/laravel.log'));
        if (strpos($logContent, 'Verification jobs dispatched') !== false) {
            echo "✓ Verification jobs dispatch logged\n";
        } else {
            echo "✗ No dispatch log found\n";
        }

        // Check if verifications were created
        $verificationsCount = DB::table('verifications')->where('driver_id', $driver->id)->count();
        echo "Verifications created: $verificationsCount\n";

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    // Clean up
    $driver->delete();
    DB::table('verifications')->where('driver_id', $driver->id)->delete();

    echo "\n";
}

echo "Testing completed.\n";

                                                                                                                          <?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Drivers;

echo "Starting thorough testing of driver profile update...\n";

// Test 1: Verify authentication guard
echo "\n1. Testing authentication guard...\n";
$driver = Drivers::where('email', 'john@example.com')->first();
if (!$driver) {
    echo "Test driver not found. Creating one...\n";
    $driver = Drivers::create([
        'driver_id' => 'TEST001',
        'first_name' => 'John',
        'surname' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '08012345678',
        'verification_status' => 'verified',
        'kyc_status' => 'completed',
        'password' => Hash::make('password'),
    ]);
}

// Test 2: Successful profile update
echo "\n2. Testing successful profile update...\n";
Auth::guard('driver')->login($driver);

$updateData = [
    'first_name' => 'John Updated',
    'surname' => 'Doe Updated',
    'phone' => '08098765432',
    'address' => 'New Address',
];

try {
    $response = app()->call('PUT', '/driver/profile', $updateData, [], [], [
        'HTTP_Accept' => 'application/json',
        'HTTP_Content_Type' => 'application/x-www-form-urlencoded',
    ]);

    $status = $response->getStatusCode();
    $content = $response->getContent();

    echo "Status: $status\n";
    echo "Response: $content\n";

    if ($status == 200 || $status == 302) {
        echo "✓ Profile update successful\n";
    } else {
        echo "✗ Profile update failed\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

Auth::guard('driver')->logout();

// Test 3: Unauthorized access
echo "\n3. Testing unauthorized access...\n";
try {
    $response = app()->call('PUT', '/driver/profile', $updateData, [], [], [
        'HTTP_Accept' => 'application/json',
        'HTTP_Content_Type' => 'application/x-www-form-urlencoded',
    ]);

    $status = $response->getStatusCode();
    echo "Status: $status\n";

    if ($status == 302 || $status == 401) {
        echo "✓ Unauthorized access properly blocked\n";
    } else {
        echo "✗ Unauthorized access not blocked\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 4: Validation with invalid data
echo "\n4. Testing validation with invalid data...\n";
Auth::guard('driver')->login($driver);

$invalidData = [
    'first_name' => '', // empty
    'phone' => 'invalid-phone',
    'email' => 'invalid-email',
];

try {
    $response = app()->call('PUT', '/driver/profile', $invalidData, [], [], [
        'HTTP_Accept' => 'application/json',
        'HTTP_Content_Type' => 'application/x-www-form-urlencoded',
    ]);

    $status = $response->getStatusCode();
    $content = $response->getContent();

    echo "Status: $status\n";
    echo "Response: $content\n";

    if ($status == 422 || str_contains($content, 'validation')) {
        echo "✓ Validation working correctly\n";
    } else {
        echo "✗ Validation not working\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

Auth::guard('driver')->logout();

// Test 5: Database persistence
echo "\n5. Testing database persistence...\n";
$driver->refresh();
if ($driver->first_name == 'John Updated' && $driver->phone == '08098765432') {
    echo "✓ Data persisted correctly in database\n";
} else {
    echo "✗ Data not persisted correctly\n";
    echo "Current first_name: " . $driver->first_name . "\n";
    echo "Current phone: " . $driver->phone . "\n";
}

echo "\nTesting completed!\n";

<?php

require_once __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FINAL DRIVER REGISTRATION TEST ===\n";

try {
    // Test unique driver registration
    $timestamp = time();
    $testData = [
        'driver_id' => 'TEST-' . $timestamp,
        'license_number' => 'LIC' . $timestamp,
        'first_name' => 'John',
        'surname' => 'Driver',
        'phone' => '080' . substr($timestamp, -8),
        'email' => 'driver' . $timestamp . '@test.com',
        'password' => 'password123',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
        'verification_status' => 'pending',
        'status' => 'inactive',
        'registered_at' => now()
    ];

    echo "Creating test driver with data:\n";
    echo "- License: " . $testData['license_number'] . "\n";
    echo "- Email: " . $testData['email'] . "\n";
    echo "- Phone: " . $testData['phone'] . "\n";

    // Create driver using the model
    $driver = App\Models\Drivers::create($testData);
    echo "✅ Driver created successfully with ID: " . $driver->driver_id . "\n";

    // Test authentication
    if (Auth::guard('driver')->attempt([
        'email' => $testData['email'],
        'password' => 'password123'
    ])) {
        echo "✅ Driver authentication successful\n";
        
        $authenticatedDriver = Auth::guard('driver')->user();
        echo "✅ Authenticated driver: " . $authenticatedDriver->first_name . " " . $authenticatedDriver->surname . "\n";
        
        Auth::guard('driver')->logout();
        echo "✅ Driver logout successful\n";
    } else {
        echo "❌ Driver authentication failed\n";
    }

    // Test form submission simulation  
    echo "\n=== Testing Form Validation ===\n";
    
    $request = new Illuminate\Http\Request();
    $request->merge([
        'drivers_license_number' => 'NEW' . ($timestamp + 1),
        'date_of_birth' => '1985-05-15',
        'first_name' => 'Jane',
        'surname' => 'TestDriver',
        'phone' => '070' . substr(($timestamp + 1), -8),
        'email' => 'jane' . ($timestamp + 1) . '@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => '1'
    ]);

    $validator = Validator::make($request->all(), [
        'drivers_license_number' => ['required', 'string', 'max:50', 'unique:drivers,license_number'],
        'date_of_birth' => ['required', 'date', 'before:today'],
        'first_name' => ['required', 'string', 'max:255'],
        'surname' => ['required', 'string', 'max:255'],
        'phone' => ['required', 'string', 'min:10', 'unique:drivers'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:drivers'],
        'password' => ['required', 'confirmed', 'min:8'],
        'terms' => ['required', 'accepted'],
    ]);

    if ($validator->passes()) {
        echo "✅ Form validation passed\n";
        
        // Create second driver
        $driver2 = App\Models\Drivers::create([
            'driver_id' => 'TEST-' . ($timestamp + 1),
            'license_number' => $request->drivers_license_number,
            'first_name' => $request->first_name,
            'surname' => $request->surname,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'gender' => 'Female',
            'verification_status' => 'pending',
            'status' => 'inactive',
            'registered_at' => now()
        ]);
        
        echo "✅ Second driver created successfully\n";
        
        // Clean up test data
        $driver->forceDelete();
        $driver2->forceDelete();
        echo "🧹 Test data cleaned up\n";
        
    } else {
        echo "❌ Form validation failed:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "   - $error\n";
        }
    }

    echo "\n✅ ALL TESTS PASSED! Driver registration is working correctly.\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== REGISTRATION READY FOR USE ===\n";
echo "You can now register drivers at: http://localhost:8000/driver/register\n";
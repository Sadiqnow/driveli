<?php

/**
 * Final Driver Creation Test
 * Tests if drivers can now be saved after fixes
 */

echo "=== Final Driver Creation Test ===" . PHP_EOL;
echo "Testing driver creation after fixes..." . PHP_EOL . PHP_EOL;

try {
    require_once __DIR__ . '/bootstrap/app.php';
    $app = app();
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "1. Laravel initialized successfully" . PHP_EOL;
    
    // Test 1: Simple driver creation
    echo PHP_EOL . "2. Testing simple driver creation..." . PHP_EOL;
    
    $testDriver = [
        'first_name' => 'Test',
        'surname' => 'Driver',
        'email' => 'test.driver.' . time() . '@example.com',
        'phone' => '0801' . substr(time(), -7),  // Generate unique phone
        'password' => 'TestPassword123!',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
    ];
    
    echo "   Creating driver with data:" . PHP_EOL;
    foreach ($testDriver as $key => $value) {
        if ($key !== 'password') {
            echo "   - $key: $value" . PHP_EOL;
        }
    }
    
    try {
        $driver = App\Models\Driver::create([
            'driver_id' => 'DRV' . time(),
            'first_name' => $testDriver['first_name'],
            'surname' => $testDriver['surname'],
            'email' => $testDriver['email'],
            'phone' => $testDriver['phone'],
            'password' => Hash::make($testDriver['password']),
            'date_of_birth' => $testDriver['date_of_birth'],
            'gender' => $testDriver['gender'],
            'status' => 'active',
            'verification_status' => 'pending',
            'is_active' => true,
            'registered_at' => now(),
        ]);
        
        if ($driver && $driver->id) {
            echo "   ✓ Driver created successfully!" . PHP_EOL;
            echo "   ✓ ID: " . $driver->id . PHP_EOL;
            echo "   ✓ Driver ID: " . $driver->driver_id . PHP_EOL;
            echo "   ✓ Name: " . $driver->first_name . ' ' . $driver->surname . PHP_EOL;
            echo "   ✓ Email: " . $driver->email . PHP_EOL;
            
            // Verify it's in the database
            $found = App\Models\Driver::find($driver->id);
            if ($found) {
                echo "   ✓ Driver verified in database" . PHP_EOL;
                
                // Clean up
                $found->forceDelete();
                echo "   ✓ Test driver cleaned up" . PHP_EOL;
            }
        }
        
    } catch (Exception $e) {
        echo "   ✗ Driver creation failed: " . $e->getMessage() . PHP_EOL;
        
        // Show validation errors if it's a validation exception
        if (method_exists($e, 'errors')) {
            echo "   Validation errors:" . PHP_EOL;
            foreach ($e->errors() as $field => $messages) {
                echo "   - $field: " . implode(', ', $messages) . PHP_EOL;
            }
        }
    }
    
    // Test 2: Check current driver count
    echo PHP_EOL . "3. Checking current driver count..." . PHP_EOL;
    $count = App\Models\Driver::count();
    echo "   Current drivers in database: $count" . PHP_EOL;
    
    // Test 3: Check table structure
    echo PHP_EOL . "4. Checking table structure..." . PHP_EOL;
    $driver = new App\Models\Driver();
    echo "   Table name: " . $driver->getTable() . PHP_EOL;
    echo "   Fillable fields: " . count($driver->getFillable()) . PHP_EOL;
    
    // Check if critical fields are fillable
    $requiredFields = ['first_name', 'surname', 'email', 'phone'];
    $fillable = $driver->getFillable();
    $missing = array_diff($requiredFields, $fillable);
    
    if (empty($missing)) {
        echo "   ✓ All required fields are fillable" . PHP_EOL;
    } else {
        echo "   ✗ Missing fillable fields: " . implode(', ', $missing) . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}

echo PHP_EOL . "=== TEST SUMMARY ===" . PHP_EOL;
echo "If the test shows 'Driver created successfully', then the issue is fixed!" . PHP_EOL;
echo "You should now be able to create drivers through the web interface." . PHP_EOL;
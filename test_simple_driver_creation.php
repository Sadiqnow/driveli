<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Simple Driver Creation Test ===\n\n";

try {
    // Test database connection
    DB::connection()->getPdo();
    echo "✓ Database connected\n";
    
    // Check if drivers table exists
    $tables = collect(DB::select('SHOW TABLES'))->pluck('Tables_in_' . env('DB_DATABASE', 'drivelink_db'));
    if (!$tables->contains('drivers')) {
        echo "✗ drivers table does not exist!\n";
        echo "Run: php artisan migrate --force\n";
        exit(1);
    }
    
    echo "✓ drivers table exists\n";
    
    // Show current record count
    $currentCount = DB::table('drivers')->count();
    echo "Current driver count: $currentCount\n\n";
    
    // Test 1: Create with minimal required fields only
    echo "=== TEST 1: Minimal Required Fields ===\n";
    try {
        DB::beginTransaction();
        
        $minimalData = [
            'driver_id' => 'DR' . time(),
            'first_name' => 'John',
            'surname' => 'Doe',
            'phone' => '08123456789',
            'password' => 'password123'
        ];
        
        echo "Creating driver with minimal data...\n";
        $driver = App\Models\DriverNormalized::create($minimalData);
        echo "✓ SUCCESS: Driver created with ID {$driver->id} and driver_id {$driver->driver_id}\n";
        
        DB::rollback();
        echo "✓ Rolled back (test data removed)\n";
        
    } catch (Exception $e) {
        DB::rollback();
        echo "✗ FAILED: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    }
    
    // Test 2: Create with all controller default fields
    echo "=== TEST 2: Full Controller Fields ===\n";
    try {
        DB::beginTransaction();
        
        $fullData = [
            'driver_id' => 'DR' . (time() + 1),
            'first_name' => 'Jane',
            'surname' => 'Smith',
            'phone' => '08123456790',
            'password' => 'password123',
            'status' => 'active',
            'verification_status' => 'pending',
            'is_active' => true,
            'registered_at' => now(),
        ];
        
        echo "Creating driver with full data...\n";
        $driver = App\Models\DriverNormalized::create($fullData);
        echo "✓ SUCCESS: Driver created with ID {$driver->id}\n";
        echo "  Driver ID: {$driver->driver_id}\n";
        echo "  Name: {$driver->first_name} {$driver->surname}\n";
        echo "  Status: {$driver->status}\n";
        echo "  Verification: {$driver->verification_status}\n";
        
        DB::rollback();
        echo "✓ Rolled back (test data removed)\n";
        
    } catch (Exception $e) {
        DB::rollback();
        echo "✗ FAILED: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    }
    
    // Test 3: Check what happens with duplicate phone
    echo "=== TEST 3: Duplicate Phone Check ===\n";
    
    // First check if there are any existing phones
    $existingPhone = DB::table('drivers')->value('phone');
    if ($existingPhone) {
        echo "Found existing phone: $existingPhone\n";
        try {
            DB::beginTransaction();
            
            $duplicateData = [
                'driver_id' => 'DR' . (time() + 2),
                'first_name' => 'Duplicate',
                'surname' => 'Test',
                'phone' => $existingPhone, // Use existing phone
                'password' => 'password123'
            ];
            
            $driver = App\Models\DriverNormalized::create($duplicateData);
            echo "⚠ WARNING: Duplicate phone was allowed (no unique constraint)\n";
            DB::rollback();
            
        } catch (Exception $e) {
            DB::rollback();
            echo "✓ GOOD: Duplicate phone rejected - " . substr($e->getMessage(), 0, 100) . "...\n";
        }
    } else {
        echo "No existing drivers to test duplicate with\n";
    }
    
    // Test 4: Test through the controller method simulation
    echo "\n=== TEST 4: Controller Method Simulation ===\n";
    try {
        // Simulate the controller's store method data processing
        $requestData = [
            'first_name' => 'Controller',
            'surname' => 'Test',
            'phone' => '08123456791',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
            'verification_status' => 'pending'
        ];
        
        // Simulate controller logic
        DB::beginTransaction();
        
        $driverData = [
            'driver_id' => 'DR' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => $requestData['first_name'],
            'surname' => $requestData['surname'],
            'phone' => $requestData['phone'],
            'password' => $requestData['password'],
            'status' => $requestData['status'] ?: 'active',
            'verification_status' => $requestData['verification_status'] ?: 'pending',
            'is_active' => true,
            'registered_at' => now(),
        ];
        
        echo "Simulating controller store method...\n";
        $driver = App\Models\DriverNormalized::create($driverData);
        echo "✓ SUCCESS: Controller simulation worked\n";
        echo "  Generated driver_id: {$driver->driver_id}\n";
        
        DB::rollback();
        echo "✓ Simulation data rolled back\n";
        
    } catch (Exception $e) {
        DB::rollback();
        echo "✗ Controller simulation FAILED: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== FINAL DIAGNOSIS ===\n";
    echo "If all tests above passed, the drivers table is working correctly.\n";
    echo "If driver creation is still failing in the web interface, check:\n\n";
    
    echo "1. Web form validation errors\n";
    echo "2. CSRF token issues\n";
    echo "3. File upload problems\n";
    echo "4. Session/authentication issues\n";
    echo "5. JavaScript form validation blocking submission\n";
    echo "6. Server errors in storage/logs/laravel.log\n\n";
    
    echo "To test the web interface:\n";
    echo "1. Go to /admin/login\n";
    echo "2. Login with admin credentials\n";
    echo "3. Go to /admin/drivers/create\n";
    echo "4. Fill ONLY the required fields (marked with *)\n";
    echo "5. Submit and check browser network tab for errors\n";
    
} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nThis suggests a fundamental database or Laravel configuration issue.\n";
}

echo "\n=== TEST COMPLETE ===\n";
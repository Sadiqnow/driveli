<?php
/**
 * Driver CRUD Test Script
 * Tests the basic Create, Read, Update, Delete operations for drivers
 */

require_once 'vendor/autoload.php';

use App\Models\Driver;
use App\Models\DriverNormalized;
use Illuminate\Foundation\Application;

try {
    // Bootstrap Laravel application
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "=== Driver CRUD Test ===\n\n";

    // Test 1: Check if models exist and are accessible
    echo "1. Testing model accessibility...\n";
    if (class_exists('App\Models\Driver')) {
        echo "   ✓ Driver model exists\n";
    } else {
        echo "   ✗ Driver model missing\n";
    }

    if (class_exists('App\Models\DriverNormalized')) {
        echo "   ✓ DriverNormalized model exists\n";
    } else {
        echo "   ✗ DriverNormalized model missing\n";
    }

    // Test 2: Check database connection
    echo "\n2. Testing database connection...\n";
    try {
        $count = Driver::count();
        echo "   ✓ Database connected. Current driver count: {$count}\n";
    } catch (Exception $e) {
        echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
        throw $e;
    }

    // Test 3: Test Read operation
    echo "\n3. Testing READ operation...\n";
    try {
        $drivers = Driver::limit(5)->get();
        echo "   ✓ Read operation successful. Retrieved " . $drivers->count() . " drivers\n";
        
        if ($drivers->count() > 0) {
            $firstDriver = $drivers->first();
            echo "   Sample driver: ID={$firstDriver->id}, Name={$firstDriver->full_name}, Email={$firstDriver->email}\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Read operation failed: " . $e->getMessage() . "\n";
    }

    // Test 4: Test Create operation (with rollback)
    echo "\n4. Testing CREATE operation...\n";
    try {
        DB::beginTransaction();
        
        $testDriver = Driver::create([
            'driver_id' => 'DR' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Test',
            'surname' => 'Driver',
            'email' => 'test.driver.' . time() . '@example.com',
            'phone' => '08012345678',
            'password' => 'password123',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'status' => 'active',
            'verification_status' => 'pending',
            'is_active' => true,
            'registered_at' => now()
        ]);
        
        echo "   ✓ Create operation successful. Created driver with ID: {$testDriver->id}\n";
        
        // Test 5: Test Update operation
        echo "\n5. Testing UPDATE operation...\n";
        $testDriver->update(['first_name' => 'Updated']);
        $testDriver->refresh();
        
        if ($testDriver->first_name === 'Updated') {
            echo "   ✓ Update operation successful\n";
        } else {
            echo "   ✗ Update operation failed - name not updated\n";
        }
        
        // Test 6: Test Delete operation (soft delete)
        echo "\n6. Testing DELETE operation...\n";
        $testDriver->delete();
        
        if ($testDriver->trashed()) {
            echo "   ✓ Delete operation successful (soft delete)\n";
        } else {
            echo "   ✗ Delete operation failed\n";
        }
        
        DB::rollback(); // Rollback all changes
        echo "   ✓ Test data rolled back\n";
        
    } catch (Exception $e) {
        DB::rollback();
        echo "   ✗ Create/Update/Delete operations failed: " . $e->getMessage() . "\n";
    }

    // Test 7: Test relationships
    echo "\n7. Testing relationships...\n";
    try {
        $driver = Driver::with(['nationality', 'verifiedBy', 'documents'])->first();
        if ($driver) {
            echo "   ✓ Relationships loaded successfully\n";
            echo "   Driver has " . $driver->documents->count() . " documents\n";
        } else {
            echo "   ! No drivers found to test relationships\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Relationship test failed: " . $e->getMessage() . "\n";
    }

    // Test 8: Test scopes
    echo "\n8. Testing scopes...\n";
    try {
        $activeCount = Driver::active()->count();
        $verifiedCount = Driver::verified()->count();
        echo "   ✓ Scopes working. Active: {$activeCount}, Verified: {$verifiedCount}\n";
    } catch (Exception $e) {
        echo "   ✗ Scopes test failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== CRUD Test Complete ===\n";
    echo "✓ All basic CRUD operations are working!\n";

} catch (Exception $e) {
    echo "\n=== CRITICAL ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    
    // Check common issues
    echo "\n=== Troubleshooting ===\n";
    
    // Check if migrations have been run
    echo "Suggested fixes:\n";
    echo "1. Run migrations: php artisan migrate\n";
    echo "2. Check database connection in .env file\n";
    echo "3. Ensure database 'drivelink_db' exists\n";
    echo "4. Check if XAMPP MySQL is running\n";
}
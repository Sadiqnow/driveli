<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DriverNormalized;
use App\Http\Requests\DriverRegistrationRequest;
use App\Http\Controllers\Admin\DriverController;
use Illuminate\Http\Request;

echo "=== COMPREHENSIVE DRIVER CREATION TEST ===\n\n";

try {
    echo "1. Testing database connection...\n";
    $connection = DB::connection();
    echo "✓ Database connected: " . $connection->getDatabaseName() . "\n\n";
    
    // Check if table exists
    echo "2. Checking drivers table...\n";
    if (!Schema::hasTable('drivers')) {
        echo "✗ Table 'drivers' does not exist!\n";
        echo "Run: php artisan migrate\n";
        exit(1);
    }
    echo "✓ drivers table exists\n\n";
    
    // Get column list
    echo "3. Checking table structure...\n";
    $columns = Schema::getColumnListing('drivers');
    $requiredColumns = [
        'id', 'driver_id', 'first_name', 'surname', 'email', 'phone', 
        'password', 'date_of_birth', 'gender', 'nationality_id',
        'nin_number', 'license_number', 'license_class', 'license_expiry_date',
        'status', 'verification_status', 'is_active', 'created_at', 'updated_at'
    ];
    
    $missingColumns = array_diff($requiredColumns, $columns);
    if (!empty($missingColumns)) {
        echo "✗ Missing columns: " . implode(', ', $missingColumns) . "\n";
        echo "Run the missing columns migration\n\n";
    } else {
        echo "✓ All required columns present\n\n";
    }
    
    // Check lookup tables
    echo "4. Checking lookup tables...\n";
    $lookupTables = ['nationalities', 'admin_users'];
    foreach ($lookupTables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "✓ $table exists with $count records\n";
        } else {
            echo "✗ $table missing\n";
        }
    }
    echo "\n";
    
    // Test model instantiation
    echo "5. Testing model instantiation...\n";
    $model = new DriverNormalized();
    echo "✓ DriverNormalized model loaded\n";
    echo "Table: " . $model->getTable() . "\n";
    echo "Fillable count: " . count($model->getFillable()) . "\n\n";
    
    // Test driver creation with minimal required data
    echo "6. Testing driver creation...\n";
    
    $testData = [
        'driver_id' => 'TEST_' . time(),
        'first_name' => 'John',
        'surname' => 'Doe',
        'email' => 'test_' . time() . '@example.com',
        'phone' => '080' . str_pad(mt_rand(10000000, 99999999), 8, '0'),
        'password' => 'Password123!',
        'date_of_birth' => '1990-01-15',
        'gender' => 'Male',
        'nationality_id' => 1,
        'nin_number' => str_pad(mt_rand(10000000000, 99999999999), 11, '0'),
        'license_number' => 'ABC' . mt_rand(1000000, 9999999),
        'license_class' => 'Class C',
        'license_expiry_date' => '2026-12-31',
        'status' => 'active',
        'verification_status' => 'pending',
        'is_active' => true,
        'registered_at' => now(),
    ];
    
    echo "Creating test driver with data:\n";
    foreach ($testData as $key => $value) {
        if ($key !== 'password') {
            echo "  $key: $value\n";
        }
    }
    
    try {
        $driver = DriverNormalized::create($testData);
        echo "\n✓ Driver created successfully!\n";
        echo "ID: " . $driver->id . "\n";
        echo "Driver Code: " . $driver->driver_id . "\n";
        echo "Full Name: " . $driver->full_name . "\n\n";
        
        // Test retrieving the driver
        echo "7. Testing driver retrieval...\n";
        $retrievedDriver = DriverNormalized::find($driver->id);
        if ($retrievedDriver) {
            echo "✓ Driver retrieved successfully\n";
            echo "Email: " . $retrievedDriver->email . "\n";
            echo "Status: " . $retrievedDriver->status . "\n";
        } else {
            echo "✗ Failed to retrieve created driver\n";
        }
        
        // Test updating the driver
        echo "\n8. Testing driver update...\n";
        $retrievedDriver->verification_status = 'verified';
        $retrievedDriver->save();
        echo "✓ Driver updated successfully\n";
        
        // Clean up
        echo "\n9. Cleaning up test data...\n";
        $driver->delete();
        echo "✓ Test driver deleted\n\n";
        
    } catch (\Exception $e) {
        echo "\n✗ Driver creation failed!\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n\n";
        
        // Diagnose the error
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
            echo "DIAGNOSIS: Column mismatch - run the missing columns migration\n";
        } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "DIAGNOSIS: Duplicate data - check unique constraints\n";
        } elseif (strpos($e->getMessage(), 'Data too long') !== false) {
            echo "DIAGNOSIS: Data length issue - check column sizes\n";
        } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            echo "DIAGNOSIS: Foreign key issue - check lookup data\n";
        } else {
            echo "DIAGNOSIS: General error - check logs for more details\n";
        }
    }
    
    // Test the controller method
    echo "\n10. Testing controller method...\n";
    try {
        $controller = new DriverController();
        echo "✓ DriverController instantiated\n";
        
        // Check if the create method exists
        if (method_exists($controller, 'create')) {
            echo "✓ create method exists\n";
        }
        
        if (method_exists($controller, 'store')) {
            echo "✓ store method exists\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Controller test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Driver creation system analysis complete.\n";
    echo "If all tests passed, the system should be working.\n";
    echo "If tests failed, follow the diagnosis recommendations.\n\n";
    
    // Final recommendations
    echo "SETUP CHECKLIST:\n";
    echo "1. Run: php artisan migrate\n";
    echo "2. Run: php artisan db:seed --class=RequiredLookupDataSeeder\n";
    echo "3. Ensure storage/app/public/driver_documents directory exists\n";
    echo "4. Test driver creation from admin panel\n\n";
    
} catch (\Exception $e) {
    echo "CRITICAL ERROR:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "\nThe system may not be properly configured.\n";
}

echo "=== END TEST ===\n";
<?php

/**
 * Driver Saving Debug Script
 * Tests the driver creation process to identify saving issues
 */

echo "=== Driver Saving Debug Test ===" . PHP_EOL;
echo "Date: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "Testing driver saving functionality..." . PHP_EOL . PHP_EOL;

try {
    // Initialize Laravel
    require_once __DIR__ . '/bootstrap/app.php';
    $app = app();
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "1. ✓ Laravel bootstrap successful" . PHP_EOL;
    
    // Test database connection
    try {
        $pdo = app('db')->connection()->getPdo();
        echo "2. ✓ Database connection established" . PHP_EOL;
        echo "   Database: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . PHP_EOL;
    } catch (Exception $e) {
        echo "2. ✗ Database connection failed: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
    
    // Check if drivers table exists
    try {
        $tables = app('db')->connection()->select("SHOW TABLES LIKE 'drivers'");
        if (count($tables) > 0) {
            echo "3. ✓ drivers table exists" . PHP_EOL;
        } else {
            echo "3. ✗ drivers table not found" . PHP_EOL;
            echo "   Available tables:" . PHP_EOL;
            $allTables = app('db')->connection()->select("SHOW TABLES");
            foreach ($allTables as $table) {
                $tableName = array_values((array)$table)[0];
                if (strpos($tableName, 'driver') !== false) {
                    echo "   - $tableName" . PHP_EOL;
                }
            }
        }
    } catch (Exception $e) {
        echo "3. ✗ Table check failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Check table structure
    try {
        $columns = app('db')->connection()->select("DESCRIBE drivers");
        echo "4. ✓ Table structure accessible" . PHP_EOL;
        echo "   Columns count: " . count($columns) . PHP_EOL;
        
        // Check for critical columns
        $columnNames = array_map(function($col) { return $col->Field; }, $columns);
        $requiredColumns = ['id', 'driver_id', 'first_name', 'surname', 'email', 'phone'];
        $missingColumns = array_diff($requiredColumns, $columnNames);
        
        if (empty($missingColumns)) {
            echo "   ✓ All required columns present" . PHP_EOL;
        } else {
            echo "   ✗ Missing required columns: " . implode(', ', $missingColumns) . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "4. ✗ Table structure check failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test Driver model
    try {
        $driver = new App\Models\Driver();
        echo "5. ✓ Driver model loaded successfully" . PHP_EOL;
        echo "   Table name: " . $driver->getTable() . PHP_EOL;
        echo "   Fillable fields count: " . count($driver->getFillable()) . PHP_EOL;
        
        // Check critical fillable fields
        $fillable = $driver->getFillable();
        $criticalFields = ['first_name', 'surname', 'email', 'phone'];
        $missingFillable = array_diff($criticalFields, $fillable);
        
        if (empty($missingFillable)) {
            echo "   ✓ Critical fields are fillable" . PHP_EOL;
        } else {
            echo "   ✗ Missing fillable fields: " . implode(', ', $missingFillable) . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "5. ✗ Driver model error: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test simple driver creation
    echo PHP_EOL . "6. Testing basic driver creation..." . PHP_EOL;
    try {
        $testData = [
            'driver_id' => 'TEST' . time(),
            'first_name' => 'Test',
            'surname' => 'Driver',
            'email' => 'test' . time() . '@example.com',
            'phone' => '08012345678',
            'status' => 'active',
            'verification_status' => 'pending',
            'is_active' => true,
            'registered_at' => now(),
        ];
        
        echo "   Creating test driver with data:" . PHP_EOL;
        foreach ($testData as $key => $value) {
            echo "   - $key: $value" . PHP_EOL;
        }
        
        $driver = App\Models\Driver::create($testData);
        
        if ($driver && $driver->id) {
            echo "   ✓ Driver created successfully with ID: " . $driver->id . PHP_EOL;
            
            // Verify the driver was saved to database
            $savedDriver = App\Models\Driver::find($driver->id);
            if ($savedDriver) {
                echo "   ✓ Driver verified in database" . PHP_EOL;
                echo "   - Name: " . $savedDriver->first_name . ' ' . $savedDriver->surname . PHP_EOL;
                echo "   - Email: " . $savedDriver->email . PHP_EOL;
                
                // Clean up test data
                $savedDriver->delete();
                echo "   ✓ Test driver cleaned up" . PHP_EOL;
            } else {
                echo "   ✗ Driver not found in database after creation" . PHP_EOL;
            }
        } else {
            echo "   ✗ Driver creation returned null or failed" . PHP_EOL;
        }
        
    } catch (Exception $e) {
        echo "   ✗ Driver creation failed: " . $e->getMessage() . PHP_EOL;
        echo "   Error details: " . PHP_EOL;
        echo "   - File: " . $e->getFile() . PHP_EOL;
        echo "   - Line: " . $e->getLine() . PHP_EOL;
        
        // Check for validation errors
        if (method_exists($e, 'errors')) {
            echo "   - Validation errors: " . json_encode($e->errors()) . PHP_EOL;
        }
    }
    
    // Test current driver count
    try {
        $count = App\Models\Driver::count();
        echo PHP_EOL . "7. Current drivers in database: $count" . PHP_EOL;
    } catch (Exception $e) {
        echo PHP_EOL . "7. ✗ Could not count drivers: " . $e->getMessage() . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
}

echo PHP_EOL . "=== DEBUG SUMMARY ===" . PHP_EOL;
echo "This script tests the core driver saving functionality." . PHP_EOL;
echo "Check the results above to identify specific issues." . PHP_EOL;
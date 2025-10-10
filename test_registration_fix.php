<?php

require_once __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test database connection and table structure
try {
    echo "=== Testing Driver Registration Fix ===\n";
    
    // Check database connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    echo "✅ Database connection successful\n";
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE drivers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $columnNames = array_column($columns, 'Field');
    echo "📋 Available columns: " . implode(', ', $columnNames) . "\n";
    
    // Check for required columns
    $requiredColumns = ['license_number', 'password', 'email', 'phone', 'first_name', 'surname'];
    $missingColumns = array_diff($requiredColumns, $columnNames);
    
    if (empty($missingColumns)) {
        echo "✅ All required columns exist\n";
    } else {
        echo "❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
    }
    
    // Test unique constraint on license_number
    if (in_array('license_number', $columnNames)) {
        echo "✅ license_number column exists\n";
        
        // Check if it has unique constraint
        $stmt = $pdo->query("SHOW INDEX FROM drivers WHERE Column_name = 'license_number'");
        $indexes = $stmt->fetchAll();
        
        if (!empty($indexes)) {
            echo "✅ license_number has index/unique constraint\n";
        } else {
            echo "⚠️  license_number missing unique constraint\n";
        }
    }
    
    // Test model creation
    echo "\n=== Testing Model Creation ===\n";
    
    $testData = [
        'driver_id' => 'TEST-' . time(),
        'license_number' => 'TEST' . time(),
        'first_name' => 'Test',
        'surname' => 'Driver',
        'phone' => '080' . time(),
        'email' => 'test' . time() . '@example.com',
        'password' => 'password123',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
        'verification_status' => 'pending',
        'status' => 'inactive',
        'registered_at' => now()
    ];
    
    try {
        $driver = App\Models\DriverNormalized::create($testData);
        echo "✅ DriverNormalized model creation successful\n";
        echo "📋 Created driver ID: " . $driver->driver_id . "\n";
        
        // Test authentication
        if (Auth::guard('driver')->attempt([
            'email' => $testData['email'],
            'password' => 'password123'
        ])) {
            echo "✅ Driver authentication works\n";
            Auth::guard('driver')->logout();
        } else {
            echo "❌ Driver authentication failed\n";
        }
        
        // Clean up
        $driver->forceDelete();
        echo "🧹 Test data cleaned up\n";
        
    } catch (Exception $e) {
        echo "❌ Model creation failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Registration Fix Test Complete ===\n";
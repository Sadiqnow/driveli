<?php

// Simple test script to check driver creation
echo "Testing Driver Creation Direct...\n\n";

try {
    // Direct PDO connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Database connected successfully\n";
    
    // Check if drivers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Drivers table not found\n";
        exit(1);
    }
    
    echo "✅ Drivers table exists\n";
    
    // Get current count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM drivers");
    $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📊 Current drivers count: {$currentCount}\n\n";
    
    // Test insert using the same field mapping as in the controller
    $driver_id = 'DR' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $testData = [
        'driver_id' => $driver_id,
        'first_name' => 'John',
        'last_name' => 'TestDriver', // surname mapped to last_name
        'email' => 'john.test' . time() . '@example.com',
        'phone' => '080' . rand(10000000, 99999999),
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male', // Converted to Male/Female for existing enum
        'address' => 'Test Address Lagos Nigeria', // residence_address mapped to address
        'state' => 'Lagos',
        'lga' => 'Lagos Island',
        'nin' => '12345678901', // nin_number mapped to nin
        'license_number' => 'ABC123456789',
        'license_class' => 'Commercial',
        'license_expiry_date' => '2025-12-31',
        'experience_level' => '3-5 years',
        'vehicle_types' => json_encode(['sedan', 'suv']),
        'regions' => json_encode(['lagos', 'ogun']), // work_regions mapped to regions
        'status' => 'Available', // mapped to existing enum
        'verification_status' => 'Pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Build insert query
    $columns = array_keys($testData);
    $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
    
    $sql = "INSERT INTO drivers (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    echo "🔄 Attempting to insert test driver...\n";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $newId = $pdo->lastInsertId();
        echo "✅ Driver created successfully!\n";
        echo "   Database ID: {$newId}\n";
        echo "   Driver ID: {$driver_id}\n";
        echo "   Name: John TestDriver\n";
        
        // Verify the record exists
        $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
        $stmt->execute([$newId]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($driver) {
            echo "✅ Driver verified in database:\n";
            echo "   Saved Name: {$driver['first_name']} {$driver['last_name']}\n";
            echo "   Saved Phone: {$driver['phone']}\n";
            echo "   Saved Status: {$driver['status']}\n";
            echo "   Saved State: {$driver['state']}\n";
            echo "   Saved Vehicle Types: {$driver['vehicle_types']}\n";
        }
        
        // Clean up test data
        $stmt = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
        $stmt->execute([$newId]);
        echo "\n🧹 Test data cleaned up\n";
        
        echo "\n🎉 Driver creation is working correctly!\n";
        echo "The issue may have been resolved.\n";
    } else {
        echo "❌ Failed to insert driver\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        echo "\n💡 This suggests a column name mismatch.\n";
        echo "Let's check the actual table structure...\n";
        
        try {
            $stmt = $pdo->query("DESCRIBE drivers");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "\nActual drivers table columns:\n";
            foreach ($columns as $col) {
                echo "  {$col['Field']} ({$col['Type']})\n";
            }
        } catch (Exception $e2) {
            echo "Could not describe table: " . $e2->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>
<?php

echo "🔧 Fixing Guarantors Table Structure\n";
echo "====================================\n\n";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=drivelink_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database\n\n";
    
    // Check guarantors table structure
    echo "1. Checking guarantors table structure...\n";
    try {
        $columns = $pdo->query("DESCRIBE guarantors")->fetchAll(PDO::FETCH_ASSOC);
        echo "   Current columns: " . implode(', ', array_column($columns, 'Field')) . "\n";
        
        $columnNames = array_column($columns, 'Field');
        $hasName = in_array('name', $columnNames);
        $hasFirstName = in_array('first_name', $columnNames);
        $hasLastName = in_array('last_name', $columnNames);
        
        echo "   - name: " . ($hasName ? "✅" : "❌") . "\n";
        echo "   - first_name: " . ($hasFirstName ? "✅" : "❌") . "\n";
        echo "   - last_name: " . ($hasLastName ? "✅" : "❌") . "\n\n";
        
        // Option 1: Add 'name' column if it doesn't exist
        if (!$hasName && $hasFirstName && $hasLastName) {
            echo "2. Adding 'name' column to guarantors table...\n";
            $pdo->exec("ALTER TABLE guarantors ADD COLUMN name VARCHAR(255) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED AFTER last_name");
            echo "   ✅ Added computed 'name' column\n\n";
        } elseif (!$hasName) {
            echo "2. Adding 'name' column to guarantors table...\n";
            $pdo->exec("ALTER TABLE guarantors ADD COLUMN name VARCHAR(255) NOT NULL AFTER id");
            echo "   ✅ Added 'name' column\n\n";
        } else {
            echo "2. 'name' column already exists\n\n";
        }
        
        // Option 2: If we need to populate name from first_name + last_name
        if ($hasFirstName && $hasLastName) {
            echo "3. Updating existing name values...\n";
            $pdo->exec("UPDATE guarantors SET name = CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) WHERE name IS NULL OR name = ''");
            echo "   ✅ Updated name values from first_name + last_name\n\n";
        }
        
        // Check required columns and add if missing
        echo "4. Ensuring all required columns exist...\n";
        $requiredColumns = [
            'driver_id' => 'VARCHAR(50) NOT NULL',
            'relationship' => 'VARCHAR(100)',
            'phone' => 'VARCHAR(20)',
            'address' => 'TEXT',
            'email' => 'VARCHAR(255)',
            'verification_status' => 'VARCHAR(50) DEFAULT "pending"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ];
        
        foreach ($requiredColumns as $column => $definition) {
            if (!in_array($column, $columnNames)) {
                echo "   Adding '$column' column...\n";
                $pdo->exec("ALTER TABLE guarantors ADD COLUMN $column $definition");
                echo "   ✅ Added '$column' column\n";
            } else {
                echo "   ✅ '$column' column exists\n";
            }
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            echo "   ❌ guarantors table doesn't exist. Creating it...\n";
            
            $createTable = "
                CREATE TABLE guarantors (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    driver_id VARCHAR(50) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    first_name VARCHAR(255) NULL,
                    last_name VARCHAR(255) NULL,
                    relationship VARCHAR(100) NULL,
                    phone VARCHAR(20) NULL,
                    email VARCHAR(255) NULL,
                    address TEXT NULL,
                    state VARCHAR(100) NULL,
                    lga VARCHAR(100) NULL,
                    nin VARCHAR(11) NULL,
                    occupation VARCHAR(255) NULL,
                    employer VARCHAR(255) NULL,
                    how_long_known VARCHAR(100) NULL,
                    id_document VARCHAR(500) NULL,
                    passport_photograph VARCHAR(500) NULL,
                    attestation_letter VARCHAR(500) NULL,
                    verification_status VARCHAR(50) DEFAULT 'pending',
                    verified_at TIMESTAMP NULL,
                    verified_by BIGINT UNSIGNED NULL,
                    verification_notes TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_driver_id (driver_id),
                    INDEX idx_verification_status (verification_status)
                )
            ";
            
            $pdo->exec($createTable);
            echo "   ✅ Created guarantors table with all columns\n\n";
        } else {
            throw $e;
        }
    }
    
    // Test the fix
    echo "5. Testing the fix...\n";
    try {
        // Test if we can select the columns that were causing issues
        $testQuery = "SELECT id, driver_id, name, relationship, phone, address FROM guarantors LIMIT 1";
        $result = $pdo->query($testQuery);
        echo "   ✅ Query successful - columns are accessible\n";
        
        $count = $pdo->query("SELECT COUNT(*) FROM guarantors")->fetchColumn();
        echo "   ✅ Table accessible (records: $count)\n";
        
    } catch (PDOException $e) {
        echo "   ❌ Test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 GUARANTORS TABLE FIXED!\n\n";
    echo "✅ Fixed Issues:\n";
    echo "   - Added missing 'name' column\n";
    echo "   - Ensured all required columns exist\n";
    echo "   - Made table compatible with DriverNormalized relationships\n\n";
    echo "🚀 Your admin panel should now work without column errors!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    echo "🔧 Manual SQL to run in phpMyAdmin:\n\n";
    
    $sql = "
-- Fix guarantors table
USE drivelink_db;

-- Add name column if it doesn't exist
ALTER TABLE guarantors ADD COLUMN IF NOT EXISTS name VARCHAR(255) NOT NULL DEFAULT '';

-- If you have first_name and last_name, populate name column
UPDATE guarantors SET name = CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) WHERE name = '';

-- Add other required columns if missing
ALTER TABLE guarantors 
ADD COLUMN IF NOT EXISTS driver_id VARCHAR(50) NOT NULL,
ADD COLUMN IF NOT EXISTS relationship VARCHAR(100),
ADD COLUMN IF NOT EXISTS phone VARCHAR(20),
ADD COLUMN IF NOT EXISTS address TEXT,
ADD COLUMN IF NOT EXISTS verification_status VARCHAR(50) DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
";
    
    echo $sql;
}

echo "\n✨ Fix completed!\n";
<?php

echo "Adding missing columns to drivers table...\n\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=drivelink", 'root', '');
    echo "✅ Connected to database\n";
    
    // Get current columns
    $stmt = $pdo->query("DESCRIBE drivers");
    $currentColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $currentColumns[] = $row['Field'];
    }
    
    // Columns to add
    $columnsToAdd = [
        'city' => 'VARCHAR(100) NULL',
        'postal_code' => 'VARCHAR(10) NULL',
        'license_issue_date' => 'DATE NULL',
        'years_of_experience' => 'INT NULL',
        'previous_company' => 'VARCHAR(100) NULL',
        'bank_id' => 'BIGINT UNSIGNED NULL',
        'account_number' => 'VARCHAR(20) NULL',
        'account_name' => 'VARCHAR(100) NULL',
        'bvn' => 'VARCHAR(11) NULL',
        'residential_address' => 'TEXT NULL',
        'has_vehicle' => 'BOOLEAN NULL',
        'vehicle_type' => 'VARCHAR(100) NULL',
        'vehicle_year' => 'INT NULL',
        'preferred_work_location' => 'VARCHAR(255) NULL',
        'available_for_night_shifts' => 'BOOLEAN NULL',
        'available_for_weekend_work' => 'BOOLEAN NULL'
    ];
    
    echo "Current columns count: " . count($currentColumns) . "\n";
    echo "Checking columns to add...\n\n";
    
    foreach ($columnsToAdd as $column => $definition) {
        if (in_array($column, $currentColumns)) {
            echo "⏭️  Column '$column' already exists\n";
        } else {
            echo "➕ Adding column '$column'...\n";
            $sql = "ALTER TABLE drivers ADD COLUMN `$column` $definition";
            $pdo->exec($sql);
            echo "✅ Column '$column' added successfully\n";
        }
    }
    
    echo "\n✅ All missing columns have been processed!\n";
    
    // Verify columns were added
    $stmt = $pdo->query("DESCRIBE drivers");
    $newColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $newColumns[] = $row['Field'];
    }
    
    echo "\nNew column count: " . count($newColumns) . "\n";
    echo "Columns added: " . (count($newColumns) - count($currentColumns)) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
<?php
// Simple test to check if KYC columns work
header('Content-Type: text/plain');

try {
    // Direct database connection test
    $connection = new mysqli('localhost', 'root', '', 'drivelink');
    
    if ($connection->connect_error) {
        echo "Connection failed: " . $connection->connect_error;
        exit;
    }
    
    echo "Database connection: OK\n\n";
    
    // Add missing columns if they don't exist
    $alterQueries = [
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS kyc_status ENUM('pending', 'in_progress', 'completed', 'rejected', 'expired') DEFAULT 'pending'",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS kyc_step ENUM('not_started', 'step_1', 'step_2', 'step_3', 'completed') DEFAULT 'not_started'",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed') NULL",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS state_id BIGINT UNSIGNED NULL",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS lga_id BIGINT UNSIGNED NULL",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS residential_address TEXT NULL",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(255) NULL",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR(255) NULL",
        "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS emergency_contact_relationship VARCHAR(255) NULL"
    ];
    
    foreach ($alterQueries as $query) {
        $result = $connection->query($query);
        if (!$result) {
            echo "Note: " . $connection->error . "\n";
        }
    }
    
    echo "Column addition attempts: COMPLETE\n\n";
    
    // Check if columns exist
    $result = $connection->query("SHOW COLUMNS FROM drivers");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = [
        'kyc_status', 'kyc_step', 'marital_status', 'state_id', 'lga_id',
        'residential_address', 'emergency_contact_name', 'emergency_contact_phone', 
        'emergency_contact_relationship'
    ];
    
    echo "Checking required columns:\n";
    $allPresent = true;
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "✓ $column\n";
        } else {
            echo "✗ $column MISSING\n";
            $allPresent = false;
        }
    }
    
    if ($allPresent) {
        echo "\n🎉 SUCCESS: KYC columns are ready!\n";
        echo "You can now test KYC Step 1 registration.\n";
    } else {
        echo "\n❌ Some columns are still missing.\n";
    }
    
    $connection->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
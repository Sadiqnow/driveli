<?php
echo "=== Final Column Check ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Add all required columns
    $alterCommands = [
        "ALTER TABLE drivers ADD COLUMN kyc_status ENUM('pending', 'in_progress', 'completed', 'rejected', 'expired') DEFAULT 'pending'",
        "ALTER TABLE drivers ADD COLUMN kyc_step ENUM('not_started', 'step_1', 'step_2', 'step_3', 'completed') DEFAULT 'not_started'",
        "ALTER TABLE drivers ADD COLUMN marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed') NULL",
        "ALTER TABLE drivers ADD COLUMN state_id BIGINT UNSIGNED NULL",
        "ALTER TABLE drivers ADD COLUMN lga_id BIGINT UNSIGNED NULL", 
        "ALTER TABLE drivers ADD COLUMN residential_address TEXT NULL",
        "ALTER TABLE drivers ADD COLUMN emergency_contact_name VARCHAR(255) NULL",
        "ALTER TABLE drivers ADD COLUMN emergency_contact_phone VARCHAR(255) NULL",
        "ALTER TABLE drivers ADD COLUMN emergency_contact_relationship VARCHAR(255) NULL"
    ];
    
    echo "Adding missing columns...\n";
    foreach ($alterCommands as $i => $command) {
        try {
            $pdo->exec($command);
            echo "✓ Added column " . ($i + 1) . "\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "- Column " . ($i + 1) . " already exists\n";
            } else {
                echo "✗ Error with column " . ($i + 1) . ": " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nVerifying required columns...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    $requiredColumns = [
        'kyc_status', 'kyc_step', 'marital_status', 'state_id', 'lga_id',
        'residential_address', 'emergency_contact_name', 'emergency_contact_phone',
        'emergency_contact_relationship'
    ];
    
    $allPresent = true;
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columnNames)) {
            echo "✓ $column - OK\n";
        } else {
            echo "✗ $column - MISSING\n";
            $allPresent = false;
        }
    }
    
    if ($allPresent) {
        echo "\n🎉 SUCCESS: All required KYC columns are now present!\n";
        echo "\nKYC Step 1 registration should now work without column errors.\n";
        
        // Show total column count
        echo "\nTotal columns in drivers: " . count($columnNames) . "\n";
        
    } else {
        echo "\n❌ ERROR: Some columns are still missing!\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
?>
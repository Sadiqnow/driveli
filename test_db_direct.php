<?php
// Direct database test
try {
    // Try to connect directly to MySQL
    $host = 'localhost';
    $dbname = 'drivelink';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Database connection successful\n\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    $table = $stmt->fetch();
    
    if ($table) {
        echo "✓ drivers table exists\n\n";
        
        // Check columns
        $stmt = $pdo->query("SHOW COLUMNS FROM drivers");
        $columns = $stmt->fetchAll();
        
        echo "Current columns in drivers:\n";
        $columnNames = [];
        foreach ($columns as $column) {
            $columnNames[] = $column['Field'];
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
        
        // Check for required KYC columns
        echo "\n=== Checking KYC columns ===\n";
        $requiredColumns = [
            'kyc_status', 'kyc_step', 'marital_status', 'state_id', 'lga_id',
            'residential_address', 'emergency_contact_name', 'emergency_contact_phone',
            'emergency_contact_relationship'
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $col) {
            if (in_array($col, $columnNames)) {
                echo "✓ $col\n";
            } else {
                echo "✗ $col MISSING\n";
                $missingColumns[] = $col;
            }
        }
        
        // Add missing columns
        if (!empty($missingColumns)) {
            echo "\n=== Adding missing columns ===\n";
            
            $alterStatements = [
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
            
            foreach ($alterStatements as $statement) {
                try {
                    $pdo->exec($statement);
                    echo "✓ Added column\n";
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                        echo "- Column already exists\n";
                    } else {
                        echo "✗ Error: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "\n=== KYC columns check complete ===\n";
        
    } else {
        echo "✗ drivers table does not exist!\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}
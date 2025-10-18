<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("DESCRIBE drivers");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $nationalityExists = false;
    foreach($columns as $col) {
        if ($col['Field'] == 'nationality_id') {
            $nationalityExists = true;
            echo "nationality_id column exists: Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
        }
    }
    
    if (!$nationalityExists) {
        echo "nationality_id column does NOT exist in drivers table.\n";
        echo "Adding the column...\n";
        
        $pdo->exec("ALTER TABLE drivers ADD COLUMN nationality_id BIGINT UNSIGNED NULL DEFAULT 1");
        echo "Added nationality_id column.\n";
        
        // Add index
        $pdo->exec("ALTER TABLE drivers ADD INDEX idx_nationality (nationality_id)");
        echo "Added index on nationality_id.\n";
        
        // Check if nationalities table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'nationalities'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE drivers ADD CONSTRAINT fk_drivers_nationality FOREIGN KEY (nationality_id) REFERENCES nationalities(id) ON DELETE SET NULL");
            echo "Added foreign key to nationalities table.\n";
        } else {
            echo "nationalities table does not exist, skipping foreign key.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

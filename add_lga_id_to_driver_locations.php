<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if lga_id column exists in driver_locations
    $stmt = $pdo->prepare("DESCRIBE driver_locations");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lgaIdExists = false;
    foreach($columns as $col) {
        if ($col['Field'] == 'lga_id') {
            $lgaIdExists = true;
            echo "lga_id column exists in driver_locations: Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
        }
    }

    if (!$lgaIdExists) {
        echo "lga_id column does NOT exist in driver_locations table.\n";
        echo "Adding the column...\n";

        $pdo->exec("ALTER TABLE driver_locations ADD COLUMN lga_id BIGINT UNSIGNED NULL");
        echo "Added lga_id column to driver_locations.\n";

        // Add foreign key if lgas table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'lgas'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE driver_locations ADD CONSTRAINT fk_driver_locations_lga_id FOREIGN KEY (lga_id) REFERENCES lgas(id) ON DELETE SET NULL");
            echo "Added foreign key to lgas table.\n";
        } else {
            echo "lgas table does not exist, skipping foreign key.\n";
        }
    } else {
        echo "lga_id column already exists in driver_locations.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

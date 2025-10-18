<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if state_id column exists in driver_locations
    $stmt = $pdo->prepare("DESCRIBE driver_locations");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stateIdExists = false;
    foreach($columns as $col) {
        if ($col['Field'] == 'state_id') {
            $stateIdExists = true;
            echo "state_id column exists in driver_locations: Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
        }
    }

    if (!$stateIdExists) {
        echo "state_id column does NOT exist in driver_locations table.\n";
        echo "Adding the column...\n";

        $pdo->exec("ALTER TABLE driver_locations ADD COLUMN state_id BIGINT UNSIGNED NULL");
        echo "Added state_id column to driver_locations.\n";

        // Add foreign key if states table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'states'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE driver_locations ADD CONSTRAINT fk_driver_locations_state_id FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL");
            echo "Added foreign key to states table.\n";
        } else {
            echo "states table does not exist, skipping foreign key.\n";
        }
    } else {
        echo "state_id column already exists in driver_locations.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

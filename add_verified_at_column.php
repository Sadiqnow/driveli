<?php

require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding verified_at column to drivers table...\n";

    // Check if column already exists
    $stmt = $pdo->query("DESCRIBE drivers");
    $columns = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }

    if (!in_array('verified_at', $columns)) {
        echo "Column 'verified_at' does not exist. Adding it...\n";

        $sql = "ALTER TABLE drivers ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL AFTER verification_status";
        $pdo->exec($sql);

        echo "Column 'verified_at' added successfully!\n";

        // Verify the column was added
        $stmt = $pdo->query("DESCRIBE drivers");
        $found = false;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['Field'] === 'verified_at') {
                $found = true;
                echo "Verified: Column 'verified_at' exists with type: " . $row['Type'] . "\n";
                break;
            }
        }

        if (!$found) {
            echo "ERROR: Column was not added successfully!\n";
        }
    } else {
        echo "Column 'verified_at' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

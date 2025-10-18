<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding nin_number column to drivers table...\n";
    echo "============================================\n";

    // Check if nin_number column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'nin_number'");
    $exists = $stmt->rowCount() > 0;

    if ($exists) {
        echo "nin_number column already exists in drivers table.\n";
    } else {
        echo "Adding nin_number column...\n";

        // Add nin_number column after phone_2
        $pdo->exec("ALTER TABLE drivers ADD COLUMN nin_number VARCHAR(11) NULL UNIQUE AFTER phone_2");

        echo "✓ Successfully added nin_number column to drivers table.\n";

        // Verify the column was added
        $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'nin_number'");
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✓ Verified: " . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
        }
    }

    echo "\nOperation completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

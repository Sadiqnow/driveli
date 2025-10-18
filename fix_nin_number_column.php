<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Fixing nin_number column issue...\n";
    echo "==================================\n";

    // Check if nin_number column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'nin_number'");
    $ninExists = $stmt->rowCount() > 0;

    // Check if national_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'national_id'");
    $nationalIdExists = $stmt->rowCount() > 0;

    if (!$ninExists && $nationalIdExists) {
        echo "Found national_id column but no nin_number column.\n";
        echo "Renaming national_id to nin_number...\n";

        // Rename national_id to nin_number
        $pdo->exec("ALTER TABLE drivers CHANGE national_id nin_number VARCHAR(11) NULL UNIQUE");

        echo "Successfully renamed national_id to nin_number.\n";

        // Check if driver_next_of_kin table has nin_number
        $stmt = $pdo->query("SHOW COLUMNS FROM driver_next_of_kin LIKE 'nin_number'");
        $kinNinExists = $stmt->rowCount() > 0;

        if ($kinNinExists) {
            echo "driver_next_of_kin table already has nin_number column.\n";
        } else {
            echo "Adding nin_number column to driver_next_of_kin table...\n";
            $pdo->exec("ALTER TABLE driver_next_of_kin ADD COLUMN nin_number VARCHAR(11) NULL AFTER nationality_id");
            echo "Added nin_number column to driver_next_of_kin table.\n";
        }

        // Migrate data from drivers.nin_number to driver_next_of_kin.nin_number if needed
        echo "Checking if data migration is needed...\n";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM drivers WHERE nin_number IS NOT NULL AND nin_number != ''");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $driversWithNin = $row['count'];

        if ($driversWithNin > 0) {
            echo "Found $driversWithNin drivers with nin_number data.\n";
            echo "Migrating data to driver_next_of_kin table...\n";

            $pdo->exec("
                UPDATE driver_next_of_kin nok
                INNER JOIN drivers d ON nok.driver_id = d.id
                SET nok.nin_number = d.nin_number
                WHERE d.nin_number IS NOT NULL AND d.nin_number != ''
            ");

            echo "Data migration completed.\n";
        } else {
            echo "No nin_number data found in drivers table.\n";
        }

    } elseif ($ninExists) {
        echo "nin_number column already exists in drivers table.\n";
    } else {
        echo "Neither nin_number nor national_id columns found. Adding nin_number column...\n";
        $pdo->exec("ALTER TABLE drivers ADD COLUMN nin_number VARCHAR(11) NULL UNIQUE AFTER phone_2");
        echo "Added nin_number column to drivers table.\n";
    }

    // Verify the fix
    echo "\nVerifying the fix:\n";
    echo "==================\n";

    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'nin_number'");
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ nin_number column exists in drivers table: " . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
    } else {
        echo "✗ nin_number column still missing in drivers table\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM driver_next_of_kin LIKE 'nin_number'");
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ nin_number column exists in driver_next_of_kin table: " . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
    } else {
        echo "✗ nin_number column missing in driver_next_of_kin table\n";
    }

    echo "\nFix completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Linking driver nin_number with driver_documents NIN related fields...\n";
    echo "====================================================================\n";

    // Check if driver_documents table has the NIN fields
    $stmt = $pdo->query("SHOW COLUMNS FROM driver_documents LIKE 'nin_verification_data'");
    $hasNinFields = $stmt->rowCount() > 0;

    if (!$hasNinFields) {
        echo "Adding NIN verification fields to driver_documents table...\n";

        $pdo->exec("
            ALTER TABLE driver_documents
            ADD COLUMN nin_verification_data JSON NULL,
            ADD COLUMN nin_verified_at TIMESTAMP NULL,
            ADD COLUMN nin_ocr_match_score DECIMAL(5,2) NULL
        ");

        echo "✓ Added NIN verification fields to driver_documents table.\n";
    } else {
        echo "NIN verification fields already exist in driver_documents table.\n";
    }

    // Check if drivers table has nin_number
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'nin_number'");
    $hasNinNumber = $stmt->rowCount() > 0;

    if (!$hasNinNumber) {
        echo "Adding nin_number column to drivers table...\n";
        $pdo->exec("ALTER TABLE drivers ADD COLUMN nin_number VARCHAR(11) NULL UNIQUE AFTER phone_2");
        echo "✓ Added nin_number column to drivers table.\n";
    } else {
        echo "nin_number column already exists in drivers table.\n";
    }

    // Create a view or ensure data consistency
    echo "Ensuring data consistency between drivers and driver_documents...\n";

    // Check if there's existing data to migrate
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM drivers d
        LEFT JOIN driver_documents dd ON d.id = dd.driver_id
        WHERE d.nin_number IS NOT NULL
        AND (dd.nin_verification_data IS NULL OR dd.nin_verified_at IS NULL)
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $needsMigration = $row['count'] > 0;

    if ($needsMigration) {
        echo "Found $needsMigration records that need NIN data migration.\n";

        // Migrate NIN data from drivers to driver_documents where possible
        $pdo->exec("
            UPDATE driver_documents dd
            INNER JOIN drivers d ON dd.driver_id = d.id
            SET
                dd.nin_verification_data = JSON_OBJECT('nin_number', d.nin_number),
                dd.nin_verified_at = NOW()
            WHERE d.nin_number IS NOT NULL
            AND dd.nin_verification_data IS NULL
        ");

        echo "✓ Migrated NIN data to driver_documents table.\n";
    } else {
        echo "No data migration needed.\n";
    }

    // Create a trigger to keep data in sync (optional)
    echo "Creating database trigger for data synchronization...\n";

    // Drop existing trigger if it exists
    $pdo->exec("DROP TRIGGER IF EXISTS sync_driver_nin_data");

    // Create trigger to sync nin_number changes
    $pdo->exec("
        CREATE TRIGGER sync_driver_nin_data
        AFTER UPDATE ON drivers
        FOR EACH ROW
        BEGIN
            IF OLD.nin_number != NEW.nin_number THEN
                UPDATE driver_documents
                SET
                    nin_verification_data = JSON_SET(
                        COALESCE(nin_verification_data, '{}'),
                        '$.nin_number', NEW.nin_number
                    ),
                    updated_at = NOW()
                WHERE driver_id = NEW.id;
            END IF;
        END
    ");

    echo "✓ Created trigger to synchronize NIN data between tables.\n";

    // Verify the linking
    echo "\nVerifying the linking:\n";
    echo "======================\n";

    // Check columns exist
    $columns = ['drivers.nin_number', 'driver_documents.nin_verification_data', 'driver_documents.nin_verified_at', 'driver_documents.nin_ocr_match_score'];
    foreach ($columns as $column) {
        list($table, $col) = explode('.', $column);
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE '$col'");
        if ($stmt->rowCount() > 0) {
            echo "✓ $column exists\n";
        } else {
            echo "✗ $column missing\n";
        }
    }

    // Check trigger exists
    $stmt = $pdo->query("SHOW TRIGGERS LIKE 'sync_driver_nin_data'");
    if ($stmt->rowCount() > 0) {
        echo "✓ sync_driver_nin_data trigger exists\n";
    } else {
        echo "✗ sync_driver_nin_data trigger missing\n";
    }

    echo "\nLinking completed successfully!\n";
    echo "The driver's nin_number field is now linked with NIN verification fields in driver_documents.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

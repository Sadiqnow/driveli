<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating NIN synchronization trigger...\n";
    echo "======================================\n";

    // Drop existing trigger if it exists
    $pdo->exec("DROP TRIGGER IF EXISTS sync_driver_nin_data");

    // Create trigger to sync nin_number changes from drivers to driver_documents
    $pdo->exec("
        CREATE TRIGGER sync_driver_nin_data
        AFTER UPDATE ON drivers
        FOR EACH ROW
        BEGIN
            IF (OLD.nin_number != NEW.nin_number OR (OLD.nin_number IS NULL AND NEW.nin_number IS NOT NULL)) THEN
                -- Update existing driver_documents record
                UPDATE driver_documents
                SET
                    nin_verification_data = JSON_SET(
                        COALESCE(nin_verification_data, '{\"nin_number\": null}'),
                        '$.nin_number', NEW.nin_number
                    ),
                    nin_verified_at = CASE
                        WHEN NEW.nin_number IS NOT NULL AND OLD.nin_number IS NULL THEN NOW()
                        ELSE nin_verified_at
                    END,
                    updated_at = NOW()
                WHERE driver_id = NEW.id;

                -- If no driver_documents record exists, create one
                IF ROW_COUNT() = 0 THEN
                    INSERT INTO driver_documents (
                        driver_id, document_type, nin_verification_data, nin_verified_at, created_at, updated_at
                    ) VALUES (
                        NEW.id, 'profile',
                        JSON_OBJECT('nin_number', NEW.nin_number),
                        NOW(), NOW(), NOW()
                    );
                END IF;
            END IF;
        END
    ");

    echo "✓ Created sync_driver_nin_data trigger successfully.\n";

    // Also create trigger for INSERT
    $pdo->exec("DROP TRIGGER IF EXISTS sync_driver_nin_data_insert");

    $pdo->exec("
        CREATE TRIGGER sync_driver_nin_data_insert
        AFTER INSERT ON drivers
        FOR EACH ROW
        BEGIN
            IF NEW.nin_number IS NOT NULL THEN
                INSERT INTO driver_documents (
                    driver_id, document_type, nin_verification_data, nin_verified_at, created_at, updated_at
                ) VALUES (
                    NEW.id, 'profile',
                    JSON_OBJECT('nin_number', NEW.nin_number),
                    NOW(), NOW(), NOW()
                );
            END IF;
        END
    ");

    echo "✓ Created sync_driver_nin_data_insert trigger successfully.\n";

    // Verify triggers exist
    echo "\nVerifying triggers:\n";
    $stmt = $pdo->query("SHOW TRIGGERS WHERE `Trigger` LIKE 'sync_driver_nin_data%'");
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($triggers) >= 2) {
        echo "✓ Both triggers created successfully:\n";
        foreach ($triggers as $trigger) {
            echo "  - {$trigger['Trigger']} on {$trigger['Table']}\n";
        }
    } else {
        echo "✗ Some triggers missing\n";
    }

    echo "\nTrigger creation completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

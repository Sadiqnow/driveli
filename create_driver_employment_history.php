<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if driver_employment_history table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'driver_employment_history'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "driver_employment_history table already exists.\n";
    } else {
        echo "driver_employment_history table does NOT exist. Creating it...\n";

        $createTableSQL = "
            CREATE TABLE driver_employment_history (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                driver_id BIGINT UNSIGNED NOT NULL,
                company_name VARCHAR(255) NOT NULL,
                rc_number VARCHAR(255) DEFAULT NULL,
                start_date DATE NOT NULL,
                end_date DATE DEFAULT NULL,
                vehicle_plate_number VARCHAR(255) DEFAULT NULL,
                vehicle_cab_number VARCHAR(255) DEFAULT NULL,
                reason_for_leaving TEXT DEFAULT NULL,
                employment_letter_path VARCHAR(255) DEFAULT NULL,
                service_certificate_path VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,

                INDEX idx_driver_id (driver_id),
                INDEX idx_start_date (start_date),

                FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE

            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $pdo->exec($createTableSQL);
        echo "Created driver_employment_history table successfully.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

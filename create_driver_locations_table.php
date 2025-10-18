<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if driver_locations table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'driver_locations'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "driver_locations table already exists.\n";
    } else {
        echo "driver_locations table does NOT exist. Creating it...\n";

        $createTableSQL = "
            CREATE TABLE driver_locations (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                driver_id VARCHAR(255) NOT NULL,
                latitude DECIMAL(10, 8) NULL,
                longitude DECIMAL(11, 8) NULL,
                location VARCHAR(255) NULL,
                address TEXT NULL,
                city VARCHAR(255) NULL,
                state VARCHAR(255) NULL,
                country VARCHAR(255) NULL,
                accuracy DECIMAL(10, 2) NULL,
                speed DECIMAL(10, 2) NULL,
                heading DECIMAL(10, 2) NULL,
                altitude DECIMAL(10, 2) NULL,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                
                INDEX idx_driver_id (driver_id),
                INDEX idx_location (latitude, longitude),
                INDEX idx_created_at (created_at),
                
                FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE CASCADE
                
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $pdo->exec($createTableSQL);
        echo "Created driver_locations table successfully.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

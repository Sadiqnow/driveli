<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if driver_preferences table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'driver_preferences'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "driver_preferences table already exists.\n";
    } else {
        echo "driver_preferences table does NOT exist. Creating it...\n";

        $createTableSQL = "
            CREATE TABLE driver_preferences (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                driver_id BIGINT UNSIGNED NOT NULL,
                vehicle_types LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vehicle_types`)),
                experience_level VARCHAR(255) DEFAULT NULL,
                years_of_experience INT(11) DEFAULT NULL,
                preferred_routes LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_routes`)),
                working_hours LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_hours`)),
                special_skills LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`special_skills`)),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,

                UNIQUE KEY driver_preferences_driver_id_unique (driver_id),

                FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE

            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $pdo->exec($createTableSQL);
        echo "Created driver_preferences table successfully.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

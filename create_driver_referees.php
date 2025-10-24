<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if driver_referees table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'driver_referees'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "driver_referees table already exists.\n";
    } else {
        echo "driver_referees table does NOT exist. Creating it...\n";

        $createTableSQL = "
            CREATE TABLE driver_referees (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                driver_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(255) NOT NULL,
                nin VARCHAR(11) NOT NULL,
                address TEXT NOT NULL,
                state_id BIGINT UNSIGNED NOT NULL,
                lga_id BIGINT UNSIGNED NOT NULL,
                city VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NOT NULL,
                verification_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
                verified_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,

                INDEX idx_driver_id (driver_id),
                INDEX idx_nin (nin),

                FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
                FOREIGN KEY (state_id) REFERENCES states(id),
                FOREIGN KEY (lga_id) REFERENCES local_governments(id)

            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $pdo->exec($createTableSQL);
        echo "Created driver_referees table successfully.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

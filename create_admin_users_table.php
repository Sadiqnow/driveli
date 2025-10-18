<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if admin_users table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'admin_users'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "admin_users table already exists.\n";
    } else {
        echo "admin_users table does NOT exist. Creating it...\n";

        $createTableSQL = "
            CREATE TABLE admin_users (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NULL,
                role ENUM('superadmin', 'admin', 'moderator') DEFAULT 'admin',
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                last_login_at TIMESTAMP NULL,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL,
                
                INDEX idx_email (email),
                INDEX idx_role (role),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
                
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $pdo->exec($createTableSQL);
        echo "Created admin_users table successfully.\n";

        // Insert default superadmin user
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT INTO admin_users (name, email, password, role, status, created_at, updated_at) 
            VALUES ('Super Admin', 'admin@drivelink.com', '{$passwordHash}', 'superadmin', 'active', NOW(), NOW())
        ");
        echo "Inserted default superadmin user.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
echo "Setting up database for Drivelink application...\n\n";

// Try multiple connection methods
$hosts = ['127.0.0.1', 'localhost'];
$ports = [3306, 3307, 33060];
$connected = false;
$pdo = null;

foreach ($hosts as $host) {
    foreach ($ports as $port) {
        try {
            echo "Trying to connect to $host:$port...\n";
            $pdo = new PDO("mysql:host=$host;port=$port", 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            echo "✓ Connected successfully to $host:$port\n";
            $connected = true;
            break 2;
        } catch (PDOException $e) {
            echo "✗ Failed to connect to $host:$port: " . $e->getMessage() . "\n";
        }
    }
}

if (!$connected) {
    echo "\nERROR: Could not connect to MySQL. Please ensure:\n";
    echo "1. XAMPP is installed\n";
    echo "2. MySQL service is running\n";
    echo "3. MySQL is configured properly\n";
    echo "\nTo start XAMPP MySQL:\n";
    echo "- Open XAMPP Control Panel\n";
    echo "- Click 'Start' next to MySQL\n";
    echo "- Or run: net start mysql\n";
    exit(1);
}

try {
    // Create database
    echo "\nCreating database 'drivelink_db'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS drivelink_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created successfully\n";
    
    // Use the database
    $pdo->exec("USE drivelink_db");
    echo "✓ Using drivelink_db database\n";
    
    // Create admin_users table first (referenced by drivers)
    echo "\nCreating admin_users table...\n";
    $adminUsersSQL = "
        CREATE TABLE IF NOT EXISTS admin_users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL
        )
    ";
    $pdo->exec($adminUsersSQL);
    echo "✓ Admin users table created\n";
    
    // Create drivers table with all required columns
    echo "\nCreating drivers table...\n";
    $driversSQL = "
        CREATE TABLE IF NOT EXISTS drivers (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            driver_id VARCHAR(255) UNIQUE NOT NULL,
            
            -- Personal Information
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NULL,
            phone VARCHAR(255) UNIQUE NOT NULL,
            date_of_birth DATE NULL,
            gender ENUM('Male', 'Female', 'male', 'female') NULL,
            address TEXT NULL,
            state VARCHAR(255) NULL,
            lga VARCHAR(255) NULL,
            
            -- Verification Documents
            nin VARCHAR(255) UNIQUE NULL,
            nin_verification_status ENUM('Pending', 'Verified', 'Rejected', 'pending', 'verified', 'rejected') DEFAULT 'Pending',
            nin_verification_data JSON NULL,
            nin_verified_at TIMESTAMP NULL,
            nin_document VARCHAR(255) NULL,
            nin_ocr_match_score INT NULL,
            
            -- Driver's License
            license_number VARCHAR(255) NULL,
            license_class VARCHAR(255) NULL,
            license_expiry_date DATE NULL,
            license_front_image VARCHAR(255) NULL,
            license_back_image VARCHAR(255) NULL,
            frsc_verification_status ENUM('Pending', 'Verified', 'Rejected', 'pending', 'verified', 'rejected') DEFAULT 'Pending',
            frsc_verification_data JSON NULL,
            frsc_verified_at TIMESTAMP NULL,
            frsc_document VARCHAR(255) NULL,
            frsc_ocr_match_score INT NULL,
            
            -- Professional Information
            experience_level VARCHAR(255) NULL,
            vehicle_types JSON NULL,
            regions JSON NULL,
            special_skills TEXT NULL,
            
            -- Profile & Documents
            profile_photo VARCHAR(255) NULL,
            passport_photograph VARCHAR(255) NULL,
            additional_documents JSON NULL,
            
            -- Account Status
            status ENUM('Available', 'Booked', 'Not Available', 'Suspended', 'active', 'inactive', 'suspended', 'blocked') DEFAULT 'Available',
            verification_status ENUM('Pending', 'Verified', 'Rejected', 'pending', 'verified', 'rejected', 'reviewing') DEFAULT 'Pending',
            verified_at TIMESTAMP NULL,
            verified_by BIGINT UNSIGNED NULL,
            verification_notes TEXT NULL,
            rejected_at TIMESTAMP NULL,
            rejection_reason VARCHAR(255) NULL,
            
            -- OCR Verification
            ocr_verification_status VARCHAR(255) NULL,
            ocr_verification_notes TEXT NULL,
            
            -- Authentication
            email_verified_at TIMESTAMP NULL,
            password VARCHAR(255) NOT NULL,
            otp_code VARCHAR(255) NULL,
            otp_expires_at TIMESTAMP NULL,
            last_login_at TIMESTAMP NULL,
            last_login_ip VARCHAR(255) NULL,
            
            -- Performance Metrics
            rating DECIMAL(3,2) DEFAULT 0.00,
            total_jobs INT DEFAULT 0,
            completed_jobs INT DEFAULT 0,
            cancelled_jobs INT DEFAULT 0,
            total_earnings DECIMAL(12,2) DEFAULT 0.00,
            
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            
            -- Foreign Keys
            FOREIGN KEY (verified_by) REFERENCES admin_users(id) ON DELETE SET NULL,
            
            -- Indexes
            INDEX idx_status_verification (status, verification_status),
            INDEX idx_driver_id (driver_id),
            INDEX idx_phone_email (phone, email),
            INDEX idx_nin (nin)
        )
    ";
    
    $pdo->exec($driversSQL);
    echo "✓ Drivers table created successfully\n";
    
    // Create guarantors table
    echo "\nCreating guarantors table...\n";
    $guarantorsSQL = "
        CREATE TABLE IF NOT EXISTS guarantors (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            driver_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(255) NOT NULL,
            email VARCHAR(255) NULL,
            address TEXT NULL,
            relationship VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
        )
    ";
    $pdo->exec($guarantorsSQL);
    echo "✓ Guarantors table created\n";
    
    // Create a test admin user
    echo "\nCreating test admin user...\n";
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO admin_users (name, email, password) VALUES ('Admin User', 'admin@drivelink.com', '$hashedPassword')");
    echo "✓ Test admin user created (email: admin@drivelink.com, password: admin123)\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "DATABASE SETUP COMPLETE!\n";
    echo str_repeat("=", 50) . "\n";
    echo "Database: drivelink_db\n";
    echo "Tables created:\n";
    echo "- admin_users\n";
    echo "- drivers\n";
    echo "- guarantors\n";
    echo "\nYou can now run your Laravel application!\n";
    
} catch (PDOException $e) {
    echo "\n✗ Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
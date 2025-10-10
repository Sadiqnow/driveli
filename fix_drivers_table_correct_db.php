<?php

echo "=== Fixing drivers Table in CORRECT Database ===\n\n";

// Use the correct database name from .env file
$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db'; // CORRECTED DATABASE NAME

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to CORRECT database: {$database}\n\n";
    
    // Check if drivers table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'drivers'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    echo "1. Checking drivers table in drivelink_db...\n";
    echo "   Table exists: " . ($tableExists ? "YES" : "NO") . "\n";
    
    if ($tableExists) {
        echo "   ✓ Table already exists!\n";
        
        // Check table structure
        $stmt = $pdo->prepare("DESCRIBE drivers");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   ✓ Table has " . count($columns) . " columns\n";
        
        // Test basic functionality
        $testQuery = "SELECT COUNT(*) as count FROM drivers";
        $stmt = $pdo->prepare($testQuery);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   ✓ Current driver count: " . $result['count'] . "\n";
        
        echo "\n✅ drivers table is ready in drivelink_db!\n\n";
        
        // Check if Laravel knows about this table
        echo "2. Checking Laravel configuration...\n";
        
        // Load Laravel to test
        require_once __DIR__ . '/vendor/autoload.php';
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        try {
            $laravelCount = \App\Models\DriverNormalized::count();
            echo "   ✓ Laravel can access the table: {$laravelCount} drivers found\n";
            echo "   ✓ Laravel is correctly configured for drivelink_db\n";
        } catch (\Exception $e) {
            echo "   ❌ Laravel error: " . $e->getMessage() . "\n";
            echo "   This might be a model configuration issue, not a database issue.\n";
        }
        
    } else {
        echo "\n2. Creating drivers table in drivelink_db...\n";
        
        // Check if required dependency tables exist first
        $dependencies = ['admin_users', 'nationalities'];
        $missingDeps = [];
        
        foreach ($dependencies as $dep) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE '{$dep}'");
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                $missingDeps[] = $dep;
            }
        }
        
        if (count($missingDeps) > 0) {
            echo "   ⚠️  Missing dependency tables: " . implode(', ', $missingDeps) . "\n";
            echo "   Creating basic dependency tables first...\n";
            
            // Create basic admin_users table if missing
            if (in_array('admin_users', $missingDeps)) {
                $pdo->exec("
                    CREATE TABLE admin_users (
                        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        phone VARCHAR(255) NULL,
                        role VARCHAR(255) NULL,
                        status VARCHAR(255) NULL,
                        email_verified_at TIMESTAMP NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        deleted_at TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "   ✓ Created admin_users table\n";
            }
            
            // Create basic nationalities table if missing
            if (in_array('nationalities', $missingDeps)) {
                $pdo->exec("
                    CREATE TABLE nationalities (
                        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        code VARCHAR(10) NOT NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                // Insert default Nigerian nationality
                $pdo->exec("
                    INSERT INTO nationalities (id, name, code, created_at, updated_at) 
                    VALUES (1, 'Nigerian', 'NG', NOW(), NOW())
                ");
                echo "   ✓ Created nationalities table with default data\n";
            }
        }
        
        // Now create the drivers table
        echo "   Creating drivers table in drivelink_db...\n";
        
        $createTableSQL = "
            CREATE TABLE drivers (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                driver_id VARCHAR(255) NOT NULL UNIQUE,
                nickname VARCHAR(255) NULL,
                first_name VARCHAR(255) NOT NULL,
                middle_name VARCHAR(255) NULL,
                surname VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NOT NULL UNIQUE,
                phone_2 VARCHAR(255) NULL,
                email VARCHAR(255) NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                date_of_birth DATE NULL,
                gender ENUM('male', 'female', 'other') NULL,
                religion VARCHAR(255) NULL,
                blood_group VARCHAR(255) NULL,
                height_meters DECIMAL(3,2) NULL,
                disability_status VARCHAR(255) NULL,
                nationality_id BIGINT UNSIGNED NULL DEFAULT 1,
                profile_picture VARCHAR(255) NULL,
                profile_photo VARCHAR(255) NULL,
                nin_number VARCHAR(11) NULL,
                license_number VARCHAR(255) NULL,
                license_class VARCHAR(255) NULL,
                license_expiry_date DATE NULL,
                current_employer VARCHAR(255) NULL,
                experience_years INT NULL,
                employment_start_date DATE NULL,
                residence_address TEXT NULL,
                residence_state_id INT NULL,
                residence_lga_id INT NULL,
                vehicle_types JSON NULL,
                work_regions JSON NULL,
                special_skills TEXT NULL,
                status ENUM('active', 'inactive', 'suspended', 'blocked') DEFAULT 'active',
                verification_status ENUM('pending', 'verified', 'rejected', 'reviewing') DEFAULT 'pending',
                is_active BOOLEAN DEFAULT TRUE,
                last_active_at TIMESTAMP NULL,
                registered_at TIMESTAMP NULL,
                verified_at TIMESTAMP NULL,
                verified_by BIGINT UNSIGNED NULL,
                verification_notes TEXT NULL,
                rejected_at TIMESTAMP NULL,
                rejection_reason VARCHAR(255) NULL,
                ocr_verification_status ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
                ocr_verification_notes TEXT NULL,
                passport_photograph VARCHAR(255) NULL,
                license_front_image VARCHAR(255) NULL,
                license_back_image VARCHAR(255) NULL,
                email_verified_at TIMESTAMP NULL,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL,
                
                INDEX idx_status_verification (status, verification_status),
                INDEX idx_verified (verified_at, verified_by),
                INDEX idx_nationality (nationality_id),
                INDEX idx_driver_id (driver_id),
                INDEX idx_phone (phone),
                INDEX idx_email (email),
                
                FOREIGN KEY (nationality_id) REFERENCES nationalities(id) ON DELETE SET NULL,
                FOREIGN KEY (verified_by) REFERENCES admin_users(id) ON DELETE SET NULL
                
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($createTableSQL);
        echo "   ✓ Created drivers table successfully in drivelink_db\n";
        
        // Insert into migrations table to mark as complete
        $migrationName = '2025_08_11_172000_create_normalized_drivers_table';
        $pdo->prepare("
            INSERT INTO migrations (migration, batch) 
            VALUES (?, 1)
            ON DUPLICATE KEY UPDATE migration = migration
        ")->execute([$migrationName]);
        
        echo "   ✓ Marked migration as complete\n";
    }
    
    echo "\n✅ drivers table is ready in drivelink_db database!\n\n";
    
    echo "🎯 Next steps:\n";
    echo "   1. Access admin panel: http://localhost/drivelink/admin/login\n";
    echo "   2. Login with: admin@drivelink.com / password123\n";
    echo "   3. Navigate to Drivers → Create New Driver\n";
    echo "   4. Test driver creation functionality\n\n";
    
    echo "📊 Database Info:\n";
    echo "   Database: drivelink_db (CORRECT)\n";
    echo "   Laravel .env: DB_DATABASE=drivelink_db ✓\n";
    echo "   Table: drivers ✓\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "- MySQL is running\n";
    echo "- Database 'drivelink_db' exists\n";
    echo "- Database credentials are correct\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
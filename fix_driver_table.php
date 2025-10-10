<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FIXING DRIVER TABLE ISSUE ===\n";

try {
    // Check if drivers exists
    $normalizedExists = DB::select("SHOW TABLES LIKE 'drivers'");
    $driversExists = DB::select("SHOW TABLES LIKE 'drivers'");
    
    echo "drivers exists: " . (count($normalizedExists) > 0 ? "YES" : "NO") . "\n";
    echo "drivers exists: " . (count($driversExists) > 0 ? "YES" : "NO") . "\n";
    
    if (count($normalizedExists) == 0 && count($driversExists) > 0) {
        echo "\nSOLUTION 1: Creating drivers table based on existing drivers table...\n";
        
        // Get structure of existing drivers table
        $driversStructure = DB::select("DESCRIBE drivers");
        echo "Original drivers table has " . count($driversStructure) . " columns\n";
        
        // Create drivers table with enhanced structure
        DB::statement("
            CREATE TABLE IF NOT EXISTS drivers (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                driver_id VARCHAR(255) UNIQUE,
                nickname VARCHAR(100) NULL,
                first_name VARCHAR(255) NOT NULL,
                middle_name VARCHAR(255) NULL,
                surname VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NOT NULL UNIQUE,
                phone_2 VARCHAR(255) NULL,
                email VARCHAR(255) NULL UNIQUE,
                date_of_birth DATE NULL,
                gender ENUM('male', 'female', 'other') NULL,
                religion VARCHAR(100) NULL,
                blood_group VARCHAR(10) NULL,
                height_meters DECIMAL(3,2) NULL,
                disability_status VARCHAR(255) NULL,
                nationality_id BIGINT UNSIGNED NULL,
                profile_picture VARCHAR(255) NULL,
                nin_number VARCHAR(11) NULL UNIQUE,
                license_number VARCHAR(255) NULL UNIQUE,
                license_class VARCHAR(100) NULL,
                status ENUM('active', 'inactive', 'suspended', 'blocked') DEFAULT 'active',
                verification_status ENUM('pending', 'verified', 'rejected', 'reviewing') DEFAULT 'pending',
                is_active BOOLEAN DEFAULT TRUE,
                last_active_at TIMESTAMP NULL,
                registered_at TIMESTAMP NULL,
                verified_at TIMESTAMP NULL,
                verified_by BIGINT UNSIGNED NULL,
                verification_notes TEXT NULL,
                rejected_at TIMESTAMP NULL,
                rejection_reason TEXT NULL,
                ocr_verification_status VARCHAR(50) NULL,
                ocr_verification_notes TEXT NULL,
                password VARCHAR(255) NOT NULL,
                remember_token VARCHAR(100) NULL,
                email_verified_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                
                INDEX idx_driver_id (driver_id),
                INDEX idx_phone (phone),
                INDEX idx_email (email),
                INDEX idx_nin (nin_number),
                INDEX idx_license (license_number),
                INDEX idx_status (status),
                INDEX idx_verification (verification_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✅ drivers table created\n";
        
        // Migrate existing data
        $existingDrivers = DB::table('drivers')->get();
        echo "Found {$existingDrivers->count()} existing drivers to migrate\n";
        
        foreach ($existingDrivers as $driver) {
            // Map old fields to new structure
            $newData = [
                'driver_id' => $driver->driver_id ?? 'DR' . str_pad($driver->id, 6, '0', STR_PAD_LEFT),
                'first_name' => $driver->first_name,
                'surname' => $driver->last_name ?? $driver->surname ?? 'Unknown',
                'middle_name' => $driver->middle_name ?? null,
                'nickname' => $driver->nickname ?? null,
                'phone' => $driver->phone,
                'phone_2' => $driver->phone_2 ?? null,
                'email' => $driver->email,
                'date_of_birth' => $driver->date_of_birth,
                'gender' => $driver->gender ? strtolower($driver->gender) : null,
                'religion' => $driver->religion ?? null,
                'blood_group' => $driver->blood_group ?? null,
                'height_meters' => $driver->height_meters ?? null,
                'disability_status' => $driver->disability_status ?? null,
                'nationality_id' => $driver->nationality_id ?? 1, // Default to Nigerian
                'profile_picture' => $driver->profile_photo ?? $driver->profile_picture ?? null,
                'nin_number' => $driver->nin ?? $driver->nin_number ?? null,
                'license_number' => $driver->license_number,
                'license_class' => $driver->license_class,
                'status' => $this->mapStatus($driver->status ?? 'active'),
                'verification_status' => $this->mapVerificationStatus($driver->verification_status ?? 'pending'),
                'is_active' => $driver->is_active ?? true,
                'last_active_at' => $driver->last_login_at ?? $driver->last_active_at ?? null,
                'registered_at' => $driver->joined_at ?? $driver->created_at ?? now(),
                'verified_at' => $driver->verified_at,
                'verified_by' => $driver->verified_by,
                'verification_notes' => $driver->verification_notes,
                'password' => $driver->password,
                'remember_token' => $driver->remember_token ?? null,
                'email_verified_at' => $driver->email_verified_at,
                'created_at' => $driver->created_at ?? now(),
                'updated_at' => $driver->updated_at ?? now(),
                'deleted_at' => $driver->deleted_at ?? null,
            ];
            
            try {
                DB::table('drivers')->insert($newData);
            } catch (Exception $e) {
                echo "Warning: Could not migrate driver {$driver->id}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Data migration completed\n";
        
    } else if (count($normalizedExists) > 0) {
        echo "✅ drivers table already exists\n";
    } else {
        echo "❌ No drivers table found to work with\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Helper methods
function mapStatus($status) {
    $statusMap = [
        'Available' => 'active',
        'Not Available' => 'inactive', 
        'Suspended' => 'suspended',
        'active' => 'active',
        'inactive' => 'inactive',
        'suspended' => 'suspended'
    ];
    return $statusMap[strtolower($status)] ?? 'active';
}

function mapVerificationStatus($status) {
    $statusMap = [
        'Pending' => 'pending',
        'Verified' => 'verified',
        'Rejected' => 'rejected',
        'pending' => 'pending',
        'verified' => 'verified', 
        'rejected' => 'rejected'
    ];
    return $statusMap[strtolower($status)] ?? 'pending';
}

echo "\n=== DRIVER TABLE FIX COMPLETED ===\n";
?>
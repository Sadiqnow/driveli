-- =====================================================
-- DriveLink: Reset drivers Table Completely
-- =====================================================
-- Run this in phpMyAdmin SQL tab

USE drivelink_db;

-- First, disable foreign key checks to avoid constraint issues
SET FOREIGN_KEY_CHECKS = 0;

-- Drop the problematic table if it exists
DROP TABLE IF EXISTS drivers;

-- Create clean drivers table with ALL required fields
CREATE TABLE drivers (
    id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
    driver_id varchar(255) NOT NULL UNIQUE,
    
    -- Personal Information
    nickname varchar(255) NULL,
    first_name varchar(255) NOT NULL,
    middle_name varchar(255) NULL,
    surname varchar(255) NOT NULL,
    last_name varchar(255) NULL,
    phone varchar(255) NOT NULL UNIQUE,
    phone_2 varchar(255) NULL,
    email varchar(255) NULL UNIQUE,
    password varchar(255) NOT NULL,
    email_verified_at timestamp NULL DEFAULT NULL,
    remember_token varchar(100) NULL,
    
    date_of_birth date NULL,
    gender enum('male','female','other') NULL,
    religion varchar(255) NULL,
    blood_group varchar(255) NULL,
    height_meters decimal(3,2) NULL,
    disability_status varchar(255) NULL,
    nationality_id bigint unsigned DEFAULT 1,
    
    -- Profile and Documents
    profile_picture varchar(255) NULL,
    nin_number varchar(11) NULL UNIQUE,
    nin_document varchar(255) NULL,
    license_number varchar(255) NULL UNIQUE,
    license_class varchar(255) NULL,
    license_expiry_date date NULL,
    license_front_image varchar(255) NULL,
    license_back_image varchar(255) NULL,
    frsc_document varchar(255) NULL,
    passport_photograph varchar(255) NULL,
    additional_documents json NULL,
    
    -- Employment
    current_employer varchar(255) NULL,
    experience_years int NULL,
    employment_start_date date NULL,
    
    -- Location
    residence_address text NULL,
    residence_state_id bigint unsigned NULL,
    residence_lga_id bigint unsigned NULL,
    
    -- Preferences
    vehicle_types json NULL,
    work_regions json NULL,
    special_skills text NULL,
    
    -- System Status
    status enum('active','inactive','suspended','blocked') DEFAULT 'active',
    verification_status enum('pending','verified','rejected','reviewing') DEFAULT 'pending',
    is_active boolean DEFAULT true,
    last_active_at timestamp NULL DEFAULT NULL,
    registered_at timestamp NULL DEFAULT NULL,
    
    -- Verification tracking
    verified_at timestamp NULL DEFAULT NULL,
    verified_by bigint unsigned NULL,
    verification_notes text NULL,
    rejected_at timestamp NULL DEFAULT NULL,
    rejection_reason varchar(255) NULL,
    
    -- OCR Status
    ocr_verification_status enum('pending','passed','failed') DEFAULT 'pending',
    ocr_verification_notes text NULL,
    nin_verification_data json NULL,
    nin_verified_at timestamp NULL DEFAULT NULL,
    nin_ocr_match_score decimal(5,2) NULL,
    frsc_verification_data json NULL,
    frsc_verified_at timestamp NULL DEFAULT NULL,
    frsc_ocr_match_score decimal(5,2) NULL,
    
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    deleted_at timestamp NULL DEFAULT NULL
);

-- Add indexes with clean, unique names (no conflicts)
ALTER TABLE drivers ADD INDEX clean_status_verification_idx (status, verification_status);
ALTER TABLE drivers ADD INDEX clean_verified_idx (verified_at, verified_by);
ALTER TABLE drivers ADD INDEX clean_nationality_idx (nationality_id);
ALTER TABLE drivers ADD INDEX clean_phone_idx (phone);
ALTER TABLE drivers ADD INDEX clean_email_idx (email);
ALTER TABLE drivers ADD INDEX clean_nin_idx (nin_number);
ALTER TABLE drivers ADD INDEX clean_license_idx (license_number);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Create a test driver to verify everything works
INSERT INTO drivers (
    driver_id, first_name, surname, phone, password, 
    status, verification_status, is_active, registered_at, 
    created_at, updated_at
) VALUES (
    'DR000001', 'Test', 'Driver', '08123456789', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'active', 'pending', true, NOW(), NOW(), NOW()
);

-- Verify the table was created successfully
SELECT 'Table created successfully!' as status;
SELECT COUNT(*) as total_drivers FROM drivers;
DESCRIBE drivers;

-- Clean up migrations table to avoid conflicts
DELETE FROM migrations WHERE migration LIKE '%drivers%';
DELETE FROM migrations WHERE migration LIKE '%add_database_indexes%';

-- Add clean migration records
INSERT INTO migrations (migration, batch) VALUES 
('2025_08_11_172000_create_normalized_drivers_table', 1),
('2025_08_15_000002_add_missing_fields_to_drivers_table', 1);

-- Final verification
SELECT 'Setup complete! Ready to test driver creation.' as message;
-- DriveLink Database Setup Script
-- Run this in MySQL/phpMyAdmin

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS drivelink_db;
USE drivelink_db;

-- Create migrations table
CREATE TABLE IF NOT EXISTS migrations (
    id int unsigned AUTO_INCREMENT PRIMARY KEY,
    migration varchar(255) NOT NULL,
    batch int NOT NULL
);

-- Create nationalities table
CREATE TABLE IF NOT EXISTS nationalities (
    id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) NOT NULL,
    code varchar(3) NOT NULL UNIQUE,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL
);

-- Insert default nationality
INSERT IGNORE INTO nationalities (id, name, code, created_at, updated_at) VALUES 
(1, 'Nigerian', 'NG', NOW(), NOW());

-- Create states table
CREATE TABLE IF NOT EXISTS states (
    id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) NOT NULL,
    code varchar(2) NOT NULL UNIQUE,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL
);

-- Insert Nigerian states
INSERT IGNORE INTO states (name, code, created_at, updated_at) VALUES 
('Lagos', 'LA', NOW(), NOW()),
('Abuja', 'FC', NOW(), NOW()),
('Kano', 'KN', NOW(), NOW()),
('Rivers', 'RI', NOW(), NOW()),
('Ogun', 'OG', NOW(), NOW());

-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
    id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) NOT NULL,
    email varchar(255) NOT NULL UNIQUE,
    email_verified_at timestamp NULL DEFAULT NULL,
    password varchar(255) NOT NULL,
    role enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
    status enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
    last_login_at timestamp NULL DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    deleted_at timestamp NULL DEFAULT NULL
);

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO admin_users (name, email, password, role, status, created_at, updated_at) VALUES 
('Admin', 'admin@drivelink.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW(), NOW());

-- Create drivers table with ALL required fields
CREATE TABLE IF NOT EXISTS drivers (
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
    deleted_at timestamp NULL DEFAULT NULL,
    
    -- Indexes
    KEY idx_status_verification (status, verification_status),
    KEY idx_verified (verified_at, verified_by),
    KEY idx_nationality (nationality_id),
    
    -- Foreign Keys
    FOREIGN KEY (nationality_id) REFERENCES nationalities(id),
    FOREIGN KEY (verified_by) REFERENCES admin_users(id)
);

-- Create guarantors table
CREATE TABLE IF NOT EXISTS guarantors (
    id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
    driver_id varchar(255) NOT NULL,
    first_name varchar(255) NOT NULL,
    last_name varchar(255) NOT NULL,
    relationship varchar(255) NULL,
    phone varchar(255) NULL,
    email varchar(255) NULL,
    address text NULL,
    state varchar(255) NULL,
    lga varchar(255) NULL,
    nin varchar(11) NULL,
    occupation varchar(255) NULL,
    employer varchar(255) NULL,
    how_long_known varchar(255) NULL,
    id_document varchar(255) NULL,
    passport_photograph varchar(255) NULL,
    attestation_letter varchar(255) NULL,
    verification_status enum('pending','verified','rejected') DEFAULT 'pending',
    verified_at timestamp NULL DEFAULT NULL,
    verified_by bigint unsigned NULL,
    verification_notes text NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    
    KEY idx_driver_id (driver_id),
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES admin_users(id)
);

-- Record migrations as run
INSERT IGNORE INTO migrations (migration, batch) VALUES 
('2025_08_11_170000_create_lookup_tables', 1),
('2025_08_08_115236_create_admin_users_table', 1),
('2025_08_11_172000_create_normalized_drivers_table', 1),
('2025_08_15_000002_add_missing_fields_to_drivers_table', 1),
('2025_08_08_124814_create_guarantors_table', 1);

-- Create sample test driver (for testing)
INSERT IGNORE INTO drivers (
    driver_id, first_name, surname, phone, password, status, verification_status, 
    is_active, registered_at, created_at, updated_at
) VALUES (
    'DR000001', 'Test', 'Driver', '08123456789', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'active', 'pending', true, NOW(), NOW(), NOW()
);

COMMIT;
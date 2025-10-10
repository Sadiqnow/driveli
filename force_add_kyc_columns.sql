USE drivelink;

-- Force add all KYC columns, ignore errors if they already exist
SET sql_notes = 0;

-- KYC status and step tracking
ALTER TABLE drivers ADD COLUMN kyc_status ENUM('pending', 'in_progress', 'completed', 'rejected', 'expired') DEFAULT 'pending';
ALTER TABLE drivers ADD COLUMN kyc_step ENUM('not_started', 'step_1', 'step_2', 'step_3', 'completed') DEFAULT 'not_started';

-- Personal information for KYC Step 1
ALTER TABLE drivers ADD COLUMN marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed') NULL;
ALTER TABLE drivers ADD COLUMN state_id BIGINT UNSIGNED NULL;
ALTER TABLE drivers ADD COLUMN lga_id BIGINT UNSIGNED NULL;
ALTER TABLE drivers ADD COLUMN residential_address TEXT NULL;
ALTER TABLE drivers ADD COLUMN emergency_contact_name VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN emergency_contact_phone VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN emergency_contact_relationship VARCHAR(255) NULL;

-- Additional KYC fields that might be needed
ALTER TABLE drivers ADD COLUMN years_of_experience INT NULL;
ALTER TABLE drivers ADD COLUMN previous_company VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN has_vehicle BOOLEAN NULL;
ALTER TABLE drivers ADD COLUMN vehicle_type VARCHAR(100) NULL;
ALTER TABLE drivers ADD COLUMN vehicle_year INT NULL;
ALTER TABLE drivers ADD COLUMN bank_id BIGINT UNSIGNED NULL;
ALTER TABLE drivers ADD COLUMN account_number VARCHAR(20) NULL;
ALTER TABLE drivers ADD COLUMN account_name VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN bvn VARCHAR(11) NULL;
ALTER TABLE drivers ADD COLUMN preferred_work_location VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN available_for_night_shifts BOOLEAN NULL;
ALTER TABLE drivers ADD COLUMN available_for_weekend_work BOOLEAN NULL;

-- KYC step completion timestamps
ALTER TABLE drivers ADD COLUMN kyc_step_1_completed_at TIMESTAMP NULL;
ALTER TABLE drivers ADD COLUMN kyc_step_2_completed_at TIMESTAMP NULL;
ALTER TABLE drivers ADD COLUMN kyc_step_3_completed_at TIMESTAMP NULL;
ALTER TABLE drivers ADD COLUMN kyc_completed_at TIMESTAMP NULL;

-- Document paths
ALTER TABLE drivers ADD COLUMN driver_license_scan VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN national_id VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN passport_photo VARCHAR(255) NULL;

-- KYC metadata
ALTER TABLE drivers ADD COLUMN kyc_step_data JSON NULL;
ALTER TABLE drivers ADD COLUMN document_verification_data JSON NULL;
ALTER TABLE drivers ADD COLUMN kyc_rejection_reason TEXT NULL;
ALTER TABLE drivers ADD COLUMN kyc_submitted_at TIMESTAMP NULL;
ALTER TABLE drivers ADD COLUMN kyc_reviewed_at TIMESTAMP NULL;
ALTER TABLE drivers ADD COLUMN kyc_reviewed_by BIGINT UNSIGNED NULL;
ALTER TABLE drivers ADD COLUMN kyc_retry_count INT DEFAULT 0;
ALTER TABLE drivers ADD COLUMN kyc_submission_ip VARCHAR(45) NULL;
ALTER TABLE drivers ADD COLUMN kyc_user_agent VARCHAR(500) NULL;
ALTER TABLE drivers ADD COLUMN kyc_last_activity_at TIMESTAMP NULL;

SET sql_notes = 1;

-- Show what columns now exist
SELECT 'Columns added successfully. Current column list:' AS message;
SHOW COLUMNS FROM drivers;
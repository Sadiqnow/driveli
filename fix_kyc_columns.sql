-- SQL script to add missing KYC columns to drivers table

USE drivelink;

-- Add KYC status and step columns
ALTER TABLE drivers 
ADD COLUMN IF NOT EXISTS kyc_status ENUM('pending', 'in_progress', 'completed', 'rejected', 'expired') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS kyc_step ENUM('not_started', 'step_1', 'step_2', 'step_3', 'completed') DEFAULT 'not_started';

-- Add personal information columns for KYC step 1
ALTER TABLE drivers 
ADD COLUMN IF NOT EXISTS marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed') NULL,
ADD COLUMN IF NOT EXISTS state_id BIGINT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS lga_id BIGINT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS residential_address TEXT NULL,
ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS emergency_contact_relationship VARCHAR(255) NULL;

-- Show the table structure to verify changes
DESCRIBE drivers;
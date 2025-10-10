-- Quick Fix for Guarantors Table Column Issue
-- Run this in phpMyAdmin SQL tab

USE drivelink_db;

-- Check current table structure
DESCRIBE guarantors;

-- Add the missing 'name' column
ALTER TABLE guarantors ADD COLUMN IF NOT EXISTS name VARCHAR(255) NOT NULL DEFAULT '' AFTER id;

-- If you have first_name and last_name columns, populate the name column
UPDATE guarantors 
SET name = CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))
WHERE name = '' OR name IS NULL;

-- Ensure other required columns exist
ALTER TABLE guarantors 
ADD COLUMN IF NOT EXISTS driver_id VARCHAR(50) NOT NULL,
ADD COLUMN IF NOT EXISTS relationship VARCHAR(100),
ADD COLUMN IF NOT EXISTS phone VARCHAR(20),
ADD COLUMN IF NOT EXISTS address TEXT,
ADD COLUMN IF NOT EXISTS verification_status VARCHAR(50) DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add indexes for better performance
ALTER TABLE guarantors 
ADD INDEX IF NOT EXISTS idx_driver_id (driver_id),
ADD INDEX IF NOT EXISTS idx_verification_status (verification_status);

-- Verify the fix
SELECT 'Guarantors table fixed successfully!' as status;
DESCRIBE guarantors;

-- Test the problematic query
SELECT id, driver_id, name, relationship, phone, address 
FROM guarantors 
LIMIT 5;
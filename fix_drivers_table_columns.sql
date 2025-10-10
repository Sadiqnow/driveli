-- Fix drivers table to include missing columns
-- Run this in your MySQL database: driverlink_db

USE driverlink_db;

-- Check if drivers table exists
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'driverlink_db' 
AND TABLE_NAME = 'drivers';

-- Show current table structure
DESCRIBE drivers;

-- Add missing columns if they don't exist
-- Note: MySQL will ignore if column already exists with IF NOT EXISTS (not available in older versions)

-- Check if residence_state_id column exists and add if missing
SET @col_exists = (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'driverlink_db' 
                   AND TABLE_NAME = 'drivers' 
                   AND COLUMN_NAME = 'residence_state_id');

SET @sql = IF(@col_exists > 0, 
              'SELECT "residence_state_id column already exists" as status', 
              'ALTER TABLE drivers ADD COLUMN residence_state_id BIGINT UNSIGNED NULL AFTER residence_address');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if residence_lga_id column exists and add if missing
SET @col_exists = (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'driverlink_db' 
                   AND TABLE_NAME = 'drivers' 
                   AND COLUMN_NAME = 'residence_lga_id');

SET @sql = IF(@col_exists > 0, 
              'SELECT "residence_lga_id column already exists" as status', 
              'ALTER TABLE drivers ADD COLUMN residence_lga_id BIGINT UNSIGNED NULL AFTER residence_state_id');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show final table structure
SELECT 'Final table structure:' as message;
DESCRIBE drivers;

-- Show columns related to residence/state/location
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'driverlink_db' 
AND TABLE_NAME = 'drivers' 
AND (COLUMN_NAME LIKE '%state%' OR COLUMN_NAME LIKE '%lga%' OR COLUMN_NAME LIKE '%residence%' OR COLUMN_NAME LIKE '%location%')
ORDER BY ORDINAL_POSITION;
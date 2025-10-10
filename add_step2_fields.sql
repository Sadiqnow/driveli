USE drivelink;

-- Add missing KYC Step 2 fields to drivers table
SET sql_notes = 0;

-- Professional/Experience fields
ALTER TABLE drivers ADD COLUMN years_of_experience INT NULL;
ALTER TABLE drivers ADD COLUMN previous_company VARCHAR(255) NULL;

-- Vehicle information
ALTER TABLE drivers ADD COLUMN has_vehicle BOOLEAN NULL;
ALTER TABLE drivers ADD COLUMN vehicle_type VARCHAR(100) NULL;
ALTER TABLE drivers ADD COLUMN vehicle_year INT NULL;

-- Banking information
ALTER TABLE drivers ADD COLUMN bank_id BIGINT UNSIGNED NULL;
ALTER TABLE drivers ADD COLUMN account_number VARCHAR(20) NULL;
ALTER TABLE drivers ADD COLUMN account_name VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN bvn VARCHAR(11) NULL;

-- Work preferences
ALTER TABLE drivers ADD COLUMN preferred_work_location VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN available_for_night_shifts BOOLEAN NULL;
ALTER TABLE drivers ADD COLUMN available_for_weekend_work BOOLEAN NULL;

SET sql_notes = 1;

SELECT 'KYC Step 2 fields added successfully!' AS result;
SHOW COLUMNS FROM drivers WHERE Field LIKE '%experience%' OR Field LIKE '%vehicle%' OR Field LIKE '%bank%' OR Field LIKE '%account%' OR Field LIKE '%bvn%' OR Field LIKE '%work%' OR Field LIKE '%shift%';
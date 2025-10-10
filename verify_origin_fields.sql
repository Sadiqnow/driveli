-- Verify origin fields implementation
USE driverlink_db;

-- 1. Check if origin fields exist in drivers table
SELECT 'Checking table structure...' as status;

DESCRIBE drivers;

-- 2. Check specifically for origin fields
SELECT 'Origin fields status:' as status;

SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'driverlink_db' 
AND TABLE_NAME = 'drivers'
AND COLUMN_NAME IN ('state_of_origin', 'lga_of_origin', 'address_of_origin')
ORDER BY ORDINAL_POSITION;

-- 3. Check if States table exists for relationships
SELECT 'States table status:' as status;
SELECT COUNT(*) as state_count FROM states;

-- 4. Check if local_governments table exists
SELECT 'Local governments table status:' as status;
SELECT COUNT(*) as lga_count FROM local_governments;

-- 5. Sample query to show how the fields work
SELECT 'Sample data with origin fields:' as status;
SELECT 
    id,
    first_name,
    surname,
    state_of_origin,
    lga_of_origin,
    address_of_origin
FROM drivers 
LIMIT 5;
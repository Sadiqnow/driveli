-- Setup Matching System for DriveLink
-- Run this in your MySQL database: driverlink_db

USE driverlink_db;

-- 1. Create driver_matches table (drop if exists first)
DROP TABLE IF EXISTS driver_matches;

CREATE TABLE driver_matches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id VARCHAR(255) UNIQUE NOT NULL,
    driver_id BIGINT UNSIGNED NOT NULL,
    company_request_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'completed', 'cancelled') DEFAULT 'pending',
    commission_rate DECIMAL(5,2) DEFAULT 10.00,
    commission_amount DECIMAL(10,2) NULL,
    matched_at TIMESTAMP NULL,
    accepted_at TIMESTAMP NULL,
    declined_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    matched_by_admin BOOLEAN DEFAULT FALSE,
    auto_matched BOOLEAN DEFAULT FALSE,
    driver_rating DECIMAL(2,1) NULL,
    company_rating DECIMAL(2,1) NULL,
    driver_feedback TEXT NULL,
    company_feedback TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_driver_id (driver_id),
    INDEX idx_company_request_id (company_request_id),
    INDEX idx_status (status),
    INDEX idx_match_id (match_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insert test data (adjust IDs based on your actual data)
INSERT INTO driver_matches (match_id, driver_id, company_request_id, status, commission_rate, matched_at, matched_by_admin, auto_matched, notes) VALUES
('MT000001', 1, 1, 'pending', 10.00, NOW(), 1, 0, 'Test pending match - can be confirmed or cancelled'),
('MT000002', 2, 2, 'accepted', 12.00, DATE_SUB(NOW(), INTERVAL 1 DAY), 0, 1, 'Test accepted match - auto-matched'),
('MT000003', 3, 3, 'completed', 15.00, DATE_SUB(NOW(), INTERVAL 3 DAY), 1, 0, 'Test completed match - manually matched'),
('MT000004', 4, 4, 'cancelled', 8.00, DATE_SUB(NOW(), INTERVAL 2 DAY), 1, 0, 'Test cancelled match'),
('MT000005', 5, 5, 'declined', 11.00, DATE_SUB(NOW(), INTERVAL 1 DAY), 0, 1, 'Test declined match');

-- 3. Verify the setup
SELECT 'driver_matches table created successfully' as status;
SELECT COUNT(*) as total_matches FROM driver_matches;
SELECT status, COUNT(*) as count FROM driver_matches GROUP BY status;

-- 4. Show sample data
SELECT 
    match_id,
    driver_id,
    company_request_id,
    status,
    commission_rate,
    matched_at,
    CASE 
        WHEN auto_matched = 1 THEN 'Auto'
        WHEN matched_by_admin = 1 THEN 'Manual' 
        ELSE 'System'
    END as match_type
FROM driver_matches 
ORDER BY created_at DESC;

-- 5. Check related tables exist
SELECT 
    TABLE_NAME,
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES t2 WHERE t2.TABLE_NAME = t1.TABLE_NAME AND t2.TABLE_SCHEMA = 'driverlink_db') as exists_count
FROM (
    SELECT 'drivers' as TABLE_NAME
    UNION SELECT 'company_requests'
    UNION SELECT 'companies'
    UNION SELECT 'driver_matches'
) t1;
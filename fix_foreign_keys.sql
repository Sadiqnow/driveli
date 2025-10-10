-- Fix Foreign Key Constraints for DriveLink Database
-- Run this SQL script to resolve foreign key constraint issues

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables that might have foreign key constraints to drivers table
DROP TABLE IF EXISTS `guarantors`;
DROP TABLE IF EXISTS `driver_matches`;
DROP TABLE IF EXISTS `driver_performances`;
DROP TABLE IF EXISTS `commissions`;
DROP TABLE IF EXISTS `driver_locations`;
DROP TABLE IF EXISTS `driver_employment_history`;
DROP TABLE IF EXISTS `driver_next_of_kin`;
DROP TABLE IF EXISTS `driver_banking_details`;
DROP TABLE IF EXISTS `driver_referees`;
DROP TABLE IF EXISTS `driver_preferences`;
DROP TABLE IF EXISTS `driver_documents`;

-- Now drop the main drivers table
DROP TABLE IF EXISTS `drivers`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Show remaining tables
SHOW TABLES;
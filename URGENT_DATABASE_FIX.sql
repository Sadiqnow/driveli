-- URGENT: Fix DriveLink admin_users table structure
-- Copy and paste this ENTIRE script into phpMyAdmin SQL tab

-- Step 1: Check current database
USE drivelink_db;

-- Step 2: Show current table structure (for debugging)
DESCRIBE admin_users;

-- Step 3: Drop the problematic table and recreate it properly
DROP TABLE IF EXISTS admin_users;

-- Step 4: Create the correct admin_users table structure
CREATE TABLE `admin_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('Super Admin','Admin','Manager') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admin',
  `status` enum('Active','Inactive','Suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `permissions` json DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_email_unique` (`email`),
  KEY `idx_email_status` (`email`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 5: Insert working admin user
-- Password hash for 'admin123'
INSERT INTO `admin_users` (
    `name`, 
    `email`, 
    `password`, 
    `phone`, 
    `role`, 
    `status`, 
    `created_at`, 
    `updated_at`
) VALUES (
    'System Administrator',
    'admin@drivelink.com',
    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyuDZkjNF8n9vOtWRKl.W9HDnI2kO',
    '+234-800-000-0000',
    'Super Admin',
    'Active',
    NOW(),
    NOW()
);

-- Step 6: Create backup admin user
INSERT INTO `admin_users` (
    `name`, 
    `email`, 
    `password`, 
    `phone`, 
    `role`, 
    `status`, 
    `created_at`, 
    `updated_at`
) VALUES (
    'Backup Admin',
    'backup@drivelink.com',
    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyuDZkjNF8n9vOtWRKl.W9HDnI2kO',
    '+234-800-000-0001',
    'Super Admin',
    'Active',
    NOW(),
    NOW()
);

-- Step 7: Verify the fix
SELECT id, name, email, role, status, created_at FROM admin_users;

-- Step 8: Show final table structure
DESCRIBE admin_users;

-- SUCCESS MESSAGE
SELECT 'ADMIN TABLE FIXED! You can now login with:' as MESSAGE;
SELECT 'Email: admin@drivelink.com | Password: admin123' as LOGIN_OPTION_1;
SELECT 'Email: backup@drivelink.com | Password: admin123' as LOGIN_OPTION_2;
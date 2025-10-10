-- Create user_activities table for DriveLink User Management
CREATE TABLE IF NOT EXISTS `user_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_activities_user_id_foreign` (`user_id`),
  KEY `user_activities_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `user_activities_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `user_activities_action_index` (`action`),
  CONSTRAINT `user_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a sample activity to test
INSERT INTO `user_activities` (`user_id`, `action`, `description`, `model_type`, `model_id`, `ip_address`, `user_agent`, `created_at`) 
SELECT 
    1, 
    'system_setup', 
    'User Management System initialized and ready', 
    'App\\Models\\AdminUser', 
    1, 
    '127.0.0.1', 
    'DriveLink Setup System', 
    NOW() 
FROM `admin_users` 
WHERE `id` = 1 
LIMIT 1;
-- Create roles table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `display_name` varchar(255) NOT NULL,
    `description` text,
    `level` int NOT NULL DEFAULT '1',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `meta` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_name_unique` (`name`),
    KEY `roles_name_is_active_index` (`name`,`is_active`),
    KEY `roles_level_index` (`level`)
);

-- Create permissions table
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `display_name` varchar(255) DEFAULT NULL,
    `description` text,
    `category` varchar(255) DEFAULT NULL,
    `resource` varchar(255) DEFAULT NULL,
    `action` varchar(255) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `meta` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permissions_name_unique` (`name`)
);

-- Create role_user pivot table
CREATE TABLE IF NOT EXISTS `role_user` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `role_id` bigint unsigned NOT NULL,
    `user_id` bigint unsigned NOT NULL,
    `assigned_at` timestamp NULL DEFAULT NULL,
    `assigned_by` bigint unsigned DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `meta` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `role_user_role_id_user_id_unique` (`role_id`,`user_id`),
    KEY `role_user_user_id_is_active_index` (`user_id`,`is_active`),
    KEY `role_user_expires_at_index` (`expires_at`),
    CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `role_user_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `admin_users` (`id`)
);

-- Create permission_role pivot table
CREATE TABLE IF NOT EXISTS `permission_role` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `permission_id` bigint unsigned NOT NULL,
    `role_id` bigint unsigned NOT NULL,
    `assigned_at` timestamp NULL DEFAULT NULL,
    `assigned_by` bigint unsigned DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `meta` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permission_role_permission_id_role_id_unique` (`permission_id`,`role_id`),
    CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `permission_role_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `admin_users` (`id`)
);
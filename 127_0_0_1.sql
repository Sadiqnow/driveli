-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 16, 2025 at 10:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `drivelink`
--
CREATE DATABASE IF NOT EXISTS `drivelink` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `drivelink`;
--
-- Database: `drivelink_db`
--
CREATE DATABASE IF NOT EXISTS `drivelink_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `drivelink_db`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` enum('Super Admin','Admin','Manager') NOT NULL DEFAULT 'Admin',
  `status` enum('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `role`, `status`, `permissions`, `last_login_at`, `last_login_ip`, `avatar`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Sadiq Abdullah', 'adminII@drivelink.com', NULL, '$2y$10$kdfQpMphq7Ak6MFLH/9WGeB515OBvcKkY0.x8gIfydRCSS1dAh..y', NULL, 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-11 09:48:17', '2025-08-11 09:48:17', NULL),
(3, 'sadiqabdullah', 'adminIII@drivelink.com', NULL, '$2y$10$2SseqPjCJsQS3QokQXnw4u94q7AvyToe5tD8aT.i9bCWe8i0VrHPa', NULL, 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-11 10:23:41', '2025-08-11 10:23:41', NULL),
(4, 'hassan', 'hassan@gmail.com', NULL, '$2y$10$VClhakjPw6A6tXKdsx123e9R878fpgqkcrzxwZuGEIPPcTB0tOjzq', NULL, 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-11 16:59:41', '2025-08-11 16:59:41', NULL),
(5, 'kamal', 'kamal@drivelink.com', NULL, '$2y$10$6Av5.rdi4jL4bvyN3hQPO.WD2VjDd8UecXH.fM/kXn3j3wuOhoVcu', '+234901234567', 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-12 10:47:17', '2025-08-12 10:47:17', NULL),
(6, 'farouk', 'farouk@drivelink.com', NULL, '$2y$10$KMeq9Ouux91rkVvZsPhJdOkMJs1zQwdJY4XLW910J0BPPY870Zhuq', '+2348036545173', 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-12 18:06:20', '2025-08-12 18:06:20', NULL),
(7, 'sadiq', 'admins@drivelink.com', NULL, '$2y$10$RoXSpxTV9xcOx1vde6lwRegRJO4n94icXR5sbdXkPDQ9B3zc/qVQ.', NULL, 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-13 10:59:46', '2025-08-13 10:59:46', NULL),
(8, 'Sadiq Abdullah', 'sadiqabdulnow@gmail.com', NULL, '$2y$10$u4IplkpEpIH2mzrogPo9IuLH2xtp8MzcQA4mbkChL5kKLgSLCbbXa', '+2348036545173', 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-13 14:43:44', '2025-08-13 14:43:44', NULL),
(9, 'sam', 'sam@drivelink.com', NULL, '$2y$10$JMscS5Ygm4PqNW96cVfkMOdgpccsEB4VFhh9caxoOxX.VsOckeHg2', '08036545173', 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-14 17:47:42', '2025-08-14 17:47:42', NULL),
(10, 'abdullah', 'sadiqsbdulnow@drivelink.com', NULL, '$2y$10$A2ITrADE3Y2RILAWA/nguu5/kG/379EpilEBaYbHxOAjGIylgqwxO', '+234553568635', 'Admin', 'Active', '[\"manage_drivers\",\"manage_requests\",\"view_reports\"]', NULL, NULL, NULL, NULL, '2025-08-14 22:58:13', '2025-08-14 22:58:13', NULL),
(11, 'System Administrator', 'admin@drivelink.com', NULL, '$2y$10$YQF2lJXl.9hZGy4GhDlP7u4TOR/AovCYNdBtYLA1PW4nzanjbcclm', NULL, 'Super Admin', 'Active', NULL, NULL, NULL, NULL, NULL, '2025-08-15 00:31:59', '2025-08-15 00:31:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Access Bank', '044', 1, NULL, NULL),
(2, 'Citibank Nigeria', '023', 1, NULL, NULL),
(3, 'Ecobank Nigeria', '050', 1, NULL, NULL),
(4, 'Fidelity Bank', '070', 1, NULL, NULL),
(5, 'First Bank of Nigeria', '011', 1, NULL, NULL),
(6, 'First City Monument Bank', '214', 1, NULL, NULL),
(7, 'Guaranty Trust Bank', '058', 1, NULL, NULL),
(8, 'Heritage Bank', '030', 1, NULL, NULL),
(9, 'Keystone Bank', '082', 1, NULL, NULL),
(10, 'Polaris Bank', '076', 1, NULL, NULL),
(11, 'Providus Bank', '101', 1, NULL, NULL),
(12, 'Stanbic IBTC Bank', '221', 1, NULL, NULL),
(13, 'Standard Chartered Bank', '068', 1, NULL, NULL),
(14, 'Sterling Bank', '232', 1, NULL, NULL),
(15, 'Union Bank of Nigeria', '032', 1, NULL, NULL),
(16, 'United Bank For Africa', '033', 1, NULL, NULL),
(17, 'Unity Bank', '215', 1, NULL, NULL),
(18, 'Wema Bank', '035', 1, NULL, NULL),
(19, 'Zenith Bank', '057', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `company_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `company_id` varchar(255) NOT NULL,
  `registration_number` varchar(255) DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `state` varchar(255) NOT NULL,
  `lga` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `industry` enum('Manufacturing','Oil & Gas','Construction','Agriculture','Mining','Food & Beverages','Logistics','Other') DEFAULT NULL,
  `company_size` enum('1-10','11-50','51-200','201-1000','1000+') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_title` varchar(255) DEFAULT NULL,
  `contact_person_phone` varchar(255) NOT NULL,
  `contact_person_email` varchar(255) NOT NULL,
  `default_commission_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `payment_terms` enum('Immediate','7 days','14 days','30 days') NOT NULL DEFAULT '7 days',
  `preferred_regions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_regions`)),
  `vehicle_types_needed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vehicle_types_needed`)),
  `status` enum('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  `verification_status` enum('Pending','Verified','Rejected') NOT NULL DEFAULT 'Pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `registration_certificate` varchar(255) DEFAULT NULL,
  `tax_certificate` varchar(255) DEFAULT NULL,
  `additional_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_documents`)),
  `total_requests` int(11) NOT NULL DEFAULT 0,
  `fulfilled_requests` int(11) NOT NULL DEFAULT 0,
  `total_amount_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `average_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `company_id`, `registration_number`, `tax_id`, `email`, `phone`, `website`, `address`, `state`, `lga`, `postal_code`, `industry`, `company_size`, `description`, `contact_person_name`, `contact_person_title`, `contact_person_phone`, `contact_person_email`, `default_commission_rate`, `payment_terms`, `preferred_regions`, `vehicle_types_needed`, `status`, `verification_status`, `verified_at`, `verified_by`, `logo`, `registration_certificate`, `tax_certificate`, `additional_documents`, `total_requests`, `fulfilled_requests`, `total_amount_paid`, `average_rating`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Bua cement', 'CP199444', NULL, NULL, 'support@buacement.com', '430494303049', NULL, 'kfs', 'kano', 'kurtum', '43904', NULL, NULL, NULL, 'sulaimna', 'HR', '4904348308', 'sule@buacement.com', 15.00, 'Immediate', '[\"Lagos\",\"Kano\",\"Port Harcourt\"]', '[\"Truck\",\"Trailer\"]', 'Active', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0.00, 0.00, '2025-08-11 16:08:13', '2025-08-13 14:45:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_requests`
--

CREATE TABLE `company_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `request_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `job_type` varchar(100) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `salary_range` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_requests`
--

INSERT INTO `company_requests` (`id`, `company_id`, `driver_id`, `request_type`, `description`, `location`, `job_type`, `requirements`, `salary_range`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-08-11 17:07:05', '2025-08-11 17:23:28', '2025-08-11 17:23:28'),
(2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-08-11 17:17:59', '2025-08-11 17:23:23', '2025-08-11 17:23:23'),
(3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', '2025-08-11 17:22:38', '2025-08-11 17:23:12', NULL),
(4, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rejected', '2025-08-11 17:23:50', '2025-08-11 17:24:12', NULL),
(5, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-08-11 17:24:33', '2025-08-15 00:10:49', NULL),
(7, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', '2025-08-13 14:45:57', '2025-08-13 14:46:29', NULL),
(8, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-08-15 16:28:29', '2025-08-15 16:28:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `verification_status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` varchar(255) NOT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `surname` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `phone_2` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `blood_group` varchar(255) DEFAULT NULL,
  `height_meters` decimal(3,2) DEFAULT NULL,
  `disability_status` varchar(255) DEFAULT NULL,
  `nationality_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `profile_picture` varchar(255) DEFAULT NULL,
  `nin_number` varchar(11) DEFAULT NULL,
  `nin_document` varchar(255) DEFAULT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `license_class` varchar(255) DEFAULT NULL,
  `frsc_document` varchar(255) DEFAULT NULL,
  `license_front_image` varchar(255) DEFAULT NULL,
  `license_back_image` varchar(255) DEFAULT NULL,
  `license_expiry_date` date DEFAULT NULL,
  `passport_photograph` varchar(255) DEFAULT NULL,
  `additional_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_documents`)),
  `current_employer` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `employment_start_date` date DEFAULT NULL,
  `residence_address` text DEFAULT NULL,
  `residence_state_id` bigint(20) UNSIGNED DEFAULT NULL,
  `residence_lga_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vehicle_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vehicle_types`)),
  `work_regions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`work_regions`)),
  `special_skills` text DEFAULT NULL,
  `status` enum('active','inactive','suspended','blocked') NOT NULL DEFAULT 'inactive',
  `verification_status` enum('pending','verified','rejected','reviewing') NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `ocr_verification_status` enum('pending','passed','failed') NOT NULL DEFAULT 'pending',
  `ocr_verification_notes` text DEFAULT NULL,
  `nin_verification_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nin_verification_data`)),
  `nin_verified_at` timestamp NULL DEFAULT NULL,
  `nin_ocr_match_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `frsc_verification_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`frsc_verification_data`)),
  `frsc_verified_at` timestamp NULL DEFAULT NULL,
  `frsc_ocr_match_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_banking_details`
--

CREATE TABLE `driver_banking_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `bank_id` bigint(20) UNSIGNED NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_documents`
--

CREATE TABLE `driver_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` enum('nin','license_front','license_back','profile_picture','passport_photo','employment_letter','service_certificate','other') NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `document_number` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_cost` decimal(10,2) DEFAULT NULL,
  `ocr_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ocr_data`)),
  `ocr_match_score` decimal(5,2) DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_employment_history`
--

CREATE TABLE `driver_employment_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `rc_number` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `vehicle_plate_number` varchar(255) DEFAULT NULL,
  `vehicle_cab_number` varchar(255) DEFAULT NULL,
  `reason_for_leaving` text DEFAULT NULL,
  `employment_letter_path` varchar(255) DEFAULT NULL,
  `service_certificate_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `location_type` enum('origin','residence','birth') NOT NULL,
  `address` text NOT NULL,
  `city` varchar(255) NOT NULL,
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `lga_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_matches`
--

CREATE TABLE `driver_matches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `company_request_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `matched_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_next_of_kin`
--

CREATE TABLE `driver_next_of_kin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `relationship` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_performance`
--

CREATE TABLE `driver_performance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `current_location_lat` decimal(10,8) DEFAULT NULL,
  `current_location_lng` decimal(11,8) DEFAULT NULL,
  `current_city` varchar(255) DEFAULT NULL,
  `total_jobs_completed` int(11) NOT NULL DEFAULT 0,
  `average_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `total_ratings` int(11) NOT NULL DEFAULT 0,
  `total_earnings` decimal(12,2) NOT NULL DEFAULT 0.00,
  `completion_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Job completion rate percentage',
  `last_job_completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_preferences`
--

CREATE TABLE `driver_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vehicle_types`)),
  `experience_level` varchar(255) DEFAULT NULL,
  `years_of_experience` int(11) DEFAULT NULL,
  `preferred_routes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_routes`)),
  `working_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_hours`)),
  `special_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`special_skills`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_referees`
--

CREATE TABLE `driver_referees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `nin` varchar(11) NOT NULL,
  `address` text NOT NULL,
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `lga_id` bigint(20) UNSIGNED NOT NULL,
  `city` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guarantors`
--

CREATE TABLE `guarantors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `relationship` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `state` varchar(255) NOT NULL,
  `lga` varchar(255) DEFAULT NULL,
  `nin` varchar(255) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `how_long_known` text NOT NULL,
  `id_document` varchar(255) DEFAULT NULL,
  `passport_photograph` varchar(255) DEFAULT NULL,
  `attestation_letter` varchar(255) DEFAULT NULL,
  `verification_status` enum('Pending','Verified','Rejected') NOT NULL DEFAULT 'Pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `local_governments`
--

CREATE TABLE `local_governments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `local_governments`
--

INSERT INTO `local_governments` (`id`, `state_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 15, 'Abaji', NULL, NULL),
(2, 15, 'Bwari', NULL, NULL),
(3, 15, 'Gwagwalada', NULL, NULL),
(4, 15, 'Kuje', NULL, NULL),
(5, 15, 'Kwali', NULL, NULL),
(6, 15, 'Municipal Area Council', NULL, NULL),
(7, 25, 'Agege', NULL, NULL),
(8, 25, 'Ajeromi-Ifelodun', NULL, NULL),
(9, 25, 'Alimosho', NULL, NULL),
(10, 25, 'Amuwo-Odofin', NULL, NULL),
(11, 25, 'Apapa', NULL, NULL),
(12, 25, 'Badagry', NULL, NULL),
(13, 25, 'Epe', NULL, NULL),
(14, 25, 'Eti Osa', NULL, NULL),
(15, 25, 'Ibeju-Lekki', NULL, NULL),
(16, 25, 'Ifako-Ijaiye', NULL, NULL),
(17, 25, 'Ikeja', NULL, NULL),
(18, 25, 'Ikorodu', NULL, NULL),
(19, 25, 'Kosofe', NULL, NULL),
(20, 25, 'Lagos Island', NULL, NULL),
(21, 25, 'Lagos Mainland', NULL, NULL),
(22, 25, 'Mushin', NULL, NULL),
(23, 25, 'Ojo', NULL, NULL),
(24, 25, 'Oshodi-Isolo', NULL, NULL),
(25, 25, 'Shomolu', NULL, NULL),
(26, 25, 'Surulere', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_08_08_115236_create_admin_users_table', 2),
(6, '2025_08_08_120245_create_drivers_table', 2),
(7, '2025_08_08_124433_create_companies_table', 2),
(8, '2025_08_08_124814_create_guarantors_table', 2),
(13, '2025_08_11_120000_create_company_requests_table', 7),
(14, '2025_08_11_123528_create_driver_matches_table', 8),
(15, '2025_08_11_123952_create_commission_table', 9),
(16, '2025_08_11_140000_add_missing_columns_to_drivers_table', 10),
(17, '2025_08_11_140100_update_gender_enum_in_drivers_table', 10),
(28, '2025_08_10_173424_add_deleted_at_to_admin_users_table', 11),
(29, '2025_08_11_112127_add_deleted_at_to_drivers_table', 11),
(30, '2025_08_11_114848_add_deleted_at_to_drivers_table', 11),
(31, '2025_08_11_142938_create_drivers_table', 11),
(32, '2025_08_11_170000_create_lookup_tables', 11),
(33, '2025_08_11_171000_create_normalized_driver_tables', 11),
(34, '2025_08_11_172000_create_normalized_drivers_table', 11),
(35, '2025_08_11_173000_migrate_existing_driver_data', 11),
(36, '2025_08_13_000000_add_ocr_fields_to_drivers_table', 11),
(37, '2025_08_14_000001_add_database_indexes_for_performance', 12),
(38, '2025_08_15_000000_add_completion_rate_to_driver_performance_table', 12),
(39, '2025_08_15_000001_add_missing_columns_to_company_requests_table', 12),
(40, '2025_08_15_000002_add_missing_fields_to_drivers_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `nationalities`
--

CREATE TABLE `nationalities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(3) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nationalities`
--

INSERT INTO `nationalities` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Nigerian', 'NG', NULL, NULL),
(2, 'Ghanaian', 'GH', NULL, NULL),
(3, 'Beninese', 'BJ', NULL, NULL),
(4, 'Togolese', 'TG', NULL, NULL),
(5, 'Cameroonian', 'CM', NULL, NULL),
(6, 'American', 'US', NULL, NULL),
(7, 'British', 'GB', NULL, NULL),
(8, 'French', 'FR', NULL, NULL),
(9, 'German', 'DE', NULL, NULL),
(10, 'Lebanese', 'LB', NULL, NULL),
(11, 'Indian', 'IN', NULL, NULL),
(12, 'Chinese', 'CN', NULL, NULL),
(13, 'South African', 'ZA', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Abia', 'AB', NULL, NULL),
(2, 'Adamawa', 'AD', NULL, NULL),
(3, 'Akwa Ibom', 'AK', NULL, NULL),
(4, 'Anambra', 'AN', NULL, NULL),
(5, 'Bauchi', 'BA', NULL, NULL),
(6, 'Bayelsa', 'BY', NULL, NULL),
(7, 'Benue', 'BN', NULL, NULL),
(8, 'Borno', 'BO', NULL, NULL),
(9, 'Cross River', 'CR', NULL, NULL),
(10, 'Delta', 'DE', NULL, NULL),
(11, 'Ebonyi', 'EB', NULL, NULL),
(12, 'Edo', 'ED', NULL, NULL),
(13, 'Ekiti', 'EK', NULL, NULL),
(14, 'Enugu', 'EN', NULL, NULL),
(15, 'FCT', 'FC', NULL, NULL),
(16, 'Gombe', 'GO', NULL, NULL),
(17, 'Imo', 'IM', NULL, NULL),
(18, 'Jigawa', 'JI', NULL, NULL),
(19, 'Kaduna', 'KD', NULL, NULL),
(20, 'Kano', 'KN', NULL, NULL),
(21, 'Katsina', 'KT', NULL, NULL),
(22, 'Kebbi', 'KE', NULL, NULL),
(23, 'Kogi', 'KO', NULL, NULL),
(24, 'Kwara', 'KW', NULL, NULL),
(25, 'Lagos', 'LA', NULL, NULL),
(26, 'Nasarawa', 'NA', NULL, NULL),
(27, 'Niger', 'NI', NULL, NULL),
(28, 'Ogun', 'OG', NULL, NULL),
(29, 'Ondo', 'ON', NULL, NULL),
(30, 'Osun', 'OS', NULL, NULL),
(31, 'Oyo', 'OY', NULL, NULL),
(32, 'Plateau', 'PL', NULL, NULL),
(33, 'Rivers', 'RI', NULL, NULL),
(34, 'Sokoto', 'SO', NULL, NULL),
(35, 'Taraba', 'TA', NULL, NULL),
(36, 'Yobe', 'YO', NULL, NULL),
(37, 'Zamfara', 'ZA', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Drivelink Admin', 'admin@drivelink.com', '2025-08-08 10:01:29', '$2y$10$eH20rHR3bP0ku1E9kXPnretMH6UQvNrPBIL5RuGWa8LHnbDf2O9XW', 'LzURMhJedOxIKbqGgWM1F2pPTuVMFODSS99Q33aJSgsfgk16tMlnOyjxs0AA', '2025-08-08 10:01:29', '2025-08-08 10:01:29'),
(2, 'sada', 'freind@gmail.com', NULL, '$2y$10$MqnAuZ5GUI3m/s.zSwd63OsYLvJzpGoqcanPdonQhH2G.aXhsKvuW', NULL, '2025-08-08 10:13:31', '2025-08-08 10:13:31'),
(3, 'farouk', 'far@gmail.com', NULL, '$2y$10$MOsL/lMXWAm1DnfRreBDOOPdqmphsO.WptTenY1VGSOPe3GhYSGzK', NULL, '2025-08-08 13:02:23', '2025-08-08 13:02:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_users_email_unique` (`email`),
  ADD KEY `admin_users_email_status_index` (`email`,`status`),
  ADD KEY `idx_admin_status` (`status`),
  ADD KEY `idx_admin_role` (`role`),
  ADD KEY `idx_admin_email` (`email`),
  ADD KEY `idx_admin_last_login` (`last_login_at`),
  ADD KEY `idx_admin_status_role` (`status`,`role`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `banks_code_unique` (`code`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commissions_driver_id_foreign` (`driver_id`),
  ADD KEY `commissions_company_request_id_foreign` (`company_request_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `companies_company_id_unique` (`company_id`),
  ADD UNIQUE KEY `companies_email_unique` (`email`),
  ADD KEY `companies_verified_by_foreign` (`verified_by`),
  ADD KEY `companies_status_verification_status_index` (`status`,`verification_status`),
  ADD KEY `companies_company_id_index` (`company_id`),
  ADD KEY `idx_companies_status` (`status`),
  ADD KEY `idx_companies_verification` (`verification_status`),
  ADD KEY `idx_companies_industry` (`industry`),
  ADD KEY `idx_companies_state` (`state`),
  ADD KEY `idx_companies_size` (`company_size`),
  ADD KEY `idx_companies_reg_number` (`registration_number`),
  ADD KEY `idx_companies_email` (`email`),
  ADD KEY `idx_companies_phone` (`phone`),
  ADD KEY `idx_companies_company_id` (`company_id`),
  ADD KEY `idx_companies_status_verification` (`status`,`verification_status`),
  ADD KEY `idx_companies_created` (`created_at`);

--
-- Indexes for table `company_requests`
--
ALTER TABLE `company_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requests_company` (`company_id`),
  ADD KEY `idx_requests_driver` (`driver_id`),
  ADD KEY `idx_requests_status` (`status`),
  ADD KEY `idx_requests_created` (`created_at`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `driver_id` (`driver_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `drivers_driver_id_unique` (`driver_id`),
  ADD UNIQUE KEY `drivers_phone_unique` (`phone`),
  ADD UNIQUE KEY `drivers_email_unique` (`email`),
  ADD UNIQUE KEY `drivers_nin_number_unique` (`nin_number`),
  ADD UNIQUE KEY `drivers_license_number_unique` (`license_number`),
  ADD KEY `drivers_status_verification_status_index` (`status`,`verification_status`),
  ADD KEY `drivers_verified_at_verified_by_index` (`verified_at`,`verified_by`),
  ADD KEY `drivers_nationality_id_index` (`nationality_id`),
  ADD KEY `idx_drivers_status_verification` (`status`,`verification_status`),
  ADD KEY `idx_drivers_status_active` (`status`,`is_active`),
  ADD KEY `idx_drivers_verification_date` (`verification_status`,`verified_at`),
  ADD KEY `idx_drivers_email` (`email`),
  ADD KEY `idx_drivers_phone` (`phone`),
  ADD KEY `idx_drivers_nin` (`nin_number`),
  ADD KEY `idx_drivers_license` (`license_number`),
  ADD KEY `idx_drivers_driver_id` (`driver_id`),
  ADD KEY `idx_drivers_nationality` (`nationality_id`),
  ADD KEY `idx_drivers_gender` (`gender`),
  ADD KEY `idx_drivers_dob` (`date_of_birth`),
  ADD KEY `idx_drivers_verified_by` (`verified_by`),
  ADD KEY `idx_drivers_created` (`created_at`),
  ADD KEY `idx_drivers_last_active` (`last_active_at`),
  ADD KEY `idx_drivers_verification_created` (`verification_status`,`created_at`),
  ADD KEY `idx_drivers_full_status` (`status`,`verification_status`,`is_active`);

--
-- Indexes for table `driver_banking_details`
--
ALTER TABLE `driver_banking_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `driver_banking_details_account_number_bank_id_unique` (`account_number`,`bank_id`),
  ADD KEY `driver_banking_details_bank_id_foreign` (`bank_id`),
  ADD KEY `driver_banking_details_driver_id_is_primary_index` (`driver_id`,`is_primary`);

--
-- Indexes for table `driver_documents`
--
ALTER TABLE `driver_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_documents_driver_id_document_type_index` (`driver_id`,`document_type`),
  ADD KEY `driver_documents_verification_status_index` (`verification_status`);

--
-- Indexes for table `driver_employment_history`
--
ALTER TABLE `driver_employment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_employment_history_driver_id_start_date_index` (`driver_id`,`start_date`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_locations_lga_id_foreign` (`lga_id`),
  ADD KEY `driver_locations_driver_id_location_type_index` (`driver_id`,`location_type`),
  ADD KEY `driver_locations_state_id_lga_id_index` (`state_id`,`lga_id`);

--
-- Indexes for table `driver_matches`
--
ALTER TABLE `driver_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_matches_driver_id_foreign` (`driver_id`),
  ADD KEY `driver_matches_company_request_id_foreign` (`company_request_id`);

--
-- Indexes for table `driver_next_of_kin`
--
ALTER TABLE `driver_next_of_kin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_next_of_kin_driver_id_is_primary_index` (`driver_id`,`is_primary`);

--
-- Indexes for table `driver_performance`
--
ALTER TABLE `driver_performance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `driver_performance_driver_id_unique` (`driver_id`),
  ADD KEY `driver_performance_total_jobs_completed_average_rating_index` (`total_jobs_completed`,`average_rating`);

--
-- Indexes for table `driver_preferences`
--
ALTER TABLE `driver_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `driver_preferences_driver_id_unique` (`driver_id`);

--
-- Indexes for table `driver_referees`
--
ALTER TABLE `driver_referees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_referees_state_id_foreign` (`state_id`),
  ADD KEY `driver_referees_lga_id_foreign` (`lga_id`),
  ADD KEY `driver_referees_driver_id_index` (`driver_id`),
  ADD KEY `driver_referees_nin_index` (`nin`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `guarantors`
--
ALTER TABLE `guarantors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guarantors_verified_by_foreign` (`verified_by`),
  ADD KEY `guarantors_driver_id_verification_status_index` (`driver_id`,`verification_status`);

--
-- Indexes for table `local_governments`
--
ALTER TABLE `local_governments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `local_governments_state_id_name_index` (`state_id`,`name`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nationalities`
--
ALTER TABLE `nationalities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nationalities_code_unique` (`code`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `states_code_unique` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_requests`
--
ALTER TABLE `company_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `driver_banking_details`
--
ALTER TABLE `driver_banking_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_documents`
--
ALTER TABLE `driver_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_employment_history`
--
ALTER TABLE `driver_employment_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_matches`
--
ALTER TABLE `driver_matches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_next_of_kin`
--
ALTER TABLE `driver_next_of_kin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_performance`
--
ALTER TABLE `driver_performance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_preferences`
--
ALTER TABLE `driver_preferences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_referees`
--
ALTER TABLE `driver_referees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guarantors`
--
ALTER TABLE `guarantors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `local_governments`
--
ALTER TABLE `local_governments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `nationalities`
--
ALTER TABLE `nationalities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_company_request_id_foreign` FOREIGN KEY (`company_request_id`) REFERENCES `company_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commissions_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `company_requests`
--
ALTER TABLE `company_requests`
  ADD CONSTRAINT `company_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `company_requests_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_nationality_id_foreign` FOREIGN KEY (`nationality_id`) REFERENCES `nationalities` (`id`),
  ADD CONSTRAINT `drivers_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `driver_banking_details`
--
ALTER TABLE `driver_banking_details`
  ADD CONSTRAINT `driver_banking_details_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`),
  ADD CONSTRAINT `driver_banking_details_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_documents`
--
ALTER TABLE `driver_documents`
  ADD CONSTRAINT `driver_documents_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_employment_history`
--
ALTER TABLE `driver_employment_history`
  ADD CONSTRAINT `driver_employment_history_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `driver_locations_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_locations_lga_id_foreign` FOREIGN KEY (`lga_id`) REFERENCES `local_governments` (`id`),
  ADD CONSTRAINT `driver_locations_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`);

--
-- Constraints for table `driver_matches`
--
ALTER TABLE `driver_matches`
  ADD CONSTRAINT `driver_matches_company_request_id_foreign` FOREIGN KEY (`company_request_id`) REFERENCES `company_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_matches_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_next_of_kin`
--
ALTER TABLE `driver_next_of_kin`
  ADD CONSTRAINT `driver_next_of_kin_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_performance`
--
ALTER TABLE `driver_performance`
  ADD CONSTRAINT `driver_performance_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_preferences`
--
ALTER TABLE `driver_preferences`
  ADD CONSTRAINT `driver_preferences_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_referees`
--
ALTER TABLE `driver_referees`
  ADD CONSTRAINT `driver_referees_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_referees_lga_id_foreign` FOREIGN KEY (`lga_id`) REFERENCES `local_governments` (`id`),
  ADD CONSTRAINT `driver_referees_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`);

--
-- Constraints for table `guarantors`
--
ALTER TABLE `guarantors`
  ADD CONSTRAINT `guarantors_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guarantors_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `local_governments`
--
ALTER TABLE `local_governments`
  ADD CONSTRAINT `local_governments_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE;
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"fuelstamp01\",\"table\":\"users\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-03-22 11:42:13', '{\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

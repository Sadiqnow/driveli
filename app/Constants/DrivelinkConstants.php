<?php

namespace App\Constants;

class DrivelinkConstants
{
    // Driver Status Constants
    public const DRIVER_STATUS_PENDING = 'pending';
    public const DRIVER_STATUS_ACTIVE = 'active';
    public const DRIVER_STATUS_INACTIVE = 'inactive';
    public const DRIVER_STATUS_SUSPENDED = 'suspended';
    public const DRIVER_STATUS_BANNED = 'banned';
    public const DRIVER_STATUS_DELETED = 'deleted';

    // Driver Verification Status Constants
    public const VERIFICATION_STATUS_PENDING = 'pending';
    public const VERIFICATION_STATUS_IN_PROGRESS = 'in_progress';
    public const VERIFICATION_STATUS_VERIFIED = 'verified';
    public const VERIFICATION_STATUS_REJECTED = 'rejected';
    public const VERIFICATION_STATUS_REQUIRES_UPDATE = 'requires_update';

    // KYC Status Constants
    public const KYC_STATUS_NOT_STARTED = 'not_started';
    public const KYC_STATUS_IN_PROGRESS = 'in_progress';
    public const KYC_STATUS_COMPLETED = 'completed';
    public const KYC_STATUS_REJECTED = 'rejected';
    public const KYC_STATUS_REQUIRES_INFO = 'requires_info';

    // KYC Steps
    public const KYC_STEP_1 = 1; // Personal Information
    public const KYC_STEP_2 = 2; // Documents Upload
    public const KYC_STEP_3 = 3; // Guarantor Information
    public const KYC_STEP_4 = 4; // Final Review

    // Document Types
    public const DOC_TYPE_NIN = 'nin_document';
    public const DOC_TYPE_LICENSE_FRONT = 'license_front_image';
    public const DOC_TYPE_LICENSE_BACK = 'license_back_image';
    public const DOC_TYPE_PASSPORT_PHOTO = 'passport_photograph';
    public const DOC_TYPE_PROFILE_PICTURE = 'profile_picture';
    public const DOC_TYPE_UTILITY_BILL = 'utility_bill';
    public const DOC_TYPE_BANK_STATEMENT = 'bank_statement';

    // File Upload Limits
    public const MAX_FILE_SIZE_MB = 5;
    public const MAX_FILE_SIZE_BYTES = self::MAX_FILE_SIZE_MB * 1024 * 1024;
    public const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif'];
    public const ALLOWED_DOCUMENT_TYPES = ['jpg', 'jpeg', 'png', 'pdf'];

    // Pagination
    public const DEFAULT_PAGE_SIZE = 20;
    public const ALLOWED_PAGE_SIZES = [10, 20, 50, 100];
    public const MAX_PAGE_SIZE = 100;

    // Search Limits
    public const MAX_SEARCH_LENGTH = 255;
    public const MIN_SEARCH_LENGTH = 2;

    // Rate Limiting
    public const AUTH_RATE_LIMIT_ATTEMPTS = 5;
    public const AUTH_RATE_LIMIT_DECAY_MINUTES = 15;
    public const API_RATE_LIMIT_ATTEMPTS = 60;
    public const API_RATE_LIMIT_DECAY_MINUTES = 1;
    public const UPLOAD_RATE_LIMIT_ATTEMPTS = 10;
    public const UPLOAD_RATE_LIMIT_DECAY_MINUTES = 5;

    // OCR Verification
    public const OCR_STATUS_PENDING = 'pending';
    public const OCR_STATUS_PROCESSING = 'processing';
    public const OCR_STATUS_PASSED = 'passed';
    public const OCR_STATUS_FAILED = 'failed';
    public const OCR_STATUS_REQUIRES_REVIEW = 'requires_review';

    // OCR Match Score Thresholds
    public const OCR_MATCH_SCORE_EXCELLENT = 90;
    public const OCR_MATCH_SCORE_GOOD = 75;
    public const OCR_MATCH_SCORE_POOR = 50;
    public const OCR_MATCH_SCORE_FAIL = 30;

    // Admin User Roles
    public const ADMIN_ROLE_SUPER_ADMIN = 'super_admin';
    public const ADMIN_ROLE_ADMIN = 'admin';
    public const ADMIN_ROLE_MANAGER = 'manager';
    public const ADMIN_ROLE_OPERATOR = 'operator';
    public const ADMIN_ROLE_VIEWER = 'viewer';

    // Admin Status
    public const ADMIN_STATUS_ACTIVE = 'active';
    public const ADMIN_STATUS_INACTIVE = 'inactive';
    public const ADMIN_STATUS_SUSPENDED = 'suspended';

    // Gender Constants
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';
    public const GENDER_OTHER = 'other';
    public const GENDER_PREFER_NOT_SAY = 'prefer_not_to_say';

    // License Classes
    public const LICENSE_CLASS_A = 'Class A';
    public const LICENSE_CLASS_B = 'Class B';
    public const LICENSE_CLASS_C = 'Class C';
    public const LICENSE_CLASS_D = 'Class D';
    public const LICENSE_CLASS_CDL = 'CDL';

    // Vehicle Types
    public const VEHICLE_TYPE_SEDAN = 'sedan';
    public const VEHICLE_TYPE_SUV = 'suv';
    public const VEHICLE_TYPE_TRUCK = 'truck';
    public const VEHICLE_TYPE_VAN = 'van';
    public const VEHICLE_TYPE_MOTORCYCLE = 'motorcycle';
    public const VEHICLE_TYPE_BUS = 'bus';

    // Employment Preferences
    public const EMPLOYMENT_PART_TIME = 'part_time';
    public const EMPLOYMENT_FULL_TIME = 'full_time';
    public const EMPLOYMENT_CONTRACT = 'contract';
    public const EMPLOYMENT_ASSIGNMENT = 'assignment';

    // Driver Categories
    public const DRIVER_CATEGORY_COMMERCIAL_TRUCK = 'commercial_truck';
    public const DRIVER_CATEGORY_PROFESSIONAL = 'professional';
    public const DRIVER_CATEGORY_PUBLIC = 'public';
    public const DRIVER_CATEGORY_EXECUTIVE = 'executive';

    // Security Clearance Levels
    public const SECURITY_CLEARANCE_NONE = 'none';
    public const SECURITY_CLEARANCE_BASIC = 'basic';
    public const SECURITY_CLEARANCE_INTERMEDIATE = 'intermediate';
    public const SECURITY_CLEARANCE_HIGH = 'high';
    public const SECURITY_CLEARANCE_TOP_SECRET = 'top_secret';

    // Background Check Status
    public const BACKGROUND_CHECK_PENDING = 'pending';
    public const BACKGROUND_CHECK_PASSED = 'passed';
    public const BACKGROUND_CHECK_FAILED = 'failed';
    public const BACKGROUND_CHECK_EXPIRED = 'expired';

    // Currency
    public const CURRENCY_NGN = 'NGN';
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_GBP = 'GBP';

    // Notification Types
    public const NOTIFICATION_TYPE_KYC_APPROVED = 'kyc_approved';
    public const NOTIFICATION_TYPE_KYC_REJECTED = 'kyc_rejected';
    public const NOTIFICATION_TYPE_KYC_INFO_REQUEST = 'kyc_info_request';
    public const NOTIFICATION_TYPE_DOCUMENT_REQUIRED = 'document_required';
    public const NOTIFICATION_TYPE_PROFILE_UPDATE = 'profile_update';

    // Validation Rules
    public const NIN_LENGTH = 11;
    public const BVN_LENGTH = 11;
    public const PHONE_MIN_LENGTH = 10;
    public const PHONE_MAX_LENGTH = 15;
    public const PASSWORD_MIN_LENGTH = 8;

    // Cache Keys
    public const CACHE_KEY_DRIVER_STATS = 'driver_stats';
    public const CACHE_KEY_OCR_STATS = 'ocr_stats';
    public const CACHE_KEY_ADMIN_STATS = 'admin_stats';
    public const CACHE_TTL_STATS = 300; // 5 minutes

    // Log Channels
    public const LOG_CHANNEL_SECURITY = 'security';
    public const LOG_CHANNEL_AUDIT = 'audit';
    public const LOG_CHANNEL_KYC = 'kyc';
    public const LOG_CHANNEL_OCR = 'ocr';

    // Profile Completion Thresholds
    public const PROFILE_COMPLETION_MINIMUM = 60;
    public const PROFILE_COMPLETION_GOOD = 80;
    public const PROFILE_COMPLETION_EXCELLENT = 95;

    // Verification Readiness Scores
    public const VERIFICATION_SCORE_NOT_READY = 60;
    public const VERIFICATION_SCORE_NEEDS_REVIEW = 80;
    public const VERIFICATION_SCORE_READY = 90;

    /**
     * Get all driver statuses
     */
    public static function getDriverStatuses(): array
    {
        return [
            self::DRIVER_STATUS_PENDING,
            self::DRIVER_STATUS_ACTIVE,
            self::DRIVER_STATUS_INACTIVE,
            self::DRIVER_STATUS_SUSPENDED,
            self::DRIVER_STATUS_BANNED,
        ];
    }

    /**
     * Get all verification statuses
     */
    public static function getVerificationStatuses(): array
    {
        return [
            self::VERIFICATION_STATUS_PENDING,
            self::VERIFICATION_STATUS_IN_PROGRESS,
            self::VERIFICATION_STATUS_VERIFIED,
            self::VERIFICATION_STATUS_REJECTED,
            self::VERIFICATION_STATUS_REQUIRES_UPDATE,
        ];
    }

    /**
     * Get all KYC statuses
     */
    public static function getKycStatuses(): array
    {
        return [
            self::KYC_STATUS_NOT_STARTED,
            self::KYC_STATUS_IN_PROGRESS,
            self::KYC_STATUS_COMPLETED,
            self::KYC_STATUS_REJECTED,
            self::KYC_STATUS_REQUIRES_INFO,
        ];
    }

    /**
     * Get all admin roles
     */
    public static function getAdminRoles(): array
    {
        return [
            self::ADMIN_ROLE_SUPER_ADMIN,
            self::ADMIN_ROLE_ADMIN,
            self::ADMIN_ROLE_MANAGER,
            self::ADMIN_ROLE_OPERATOR,
            self::ADMIN_ROLE_VIEWER,
        ];
    }

    /**
     * Get OCR match score description
     */
    public static function getOcrScoreDescription(int $score): string
    {
        return match (true) {
            $score >= self::OCR_MATCH_SCORE_EXCELLENT => 'Excellent Match',
            $score >= self::OCR_MATCH_SCORE_GOOD => 'Good Match',
            $score >= self::OCR_MATCH_SCORE_POOR => 'Poor Match',
            $score >= self::OCR_MATCH_SCORE_FAIL => 'Failed Match',
            default => 'No Match',
        };
    }

    /**
     * Check if OCR score passes verification
     */
    public static function isOcrScorePassing(int $score): bool
    {
        return $score >= self::OCR_MATCH_SCORE_POOR;
    }
}
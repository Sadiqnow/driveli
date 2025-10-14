<?php

namespace App\Services;

use App\Models\Drivers;
use Carbon\Carbon;

class DrivelinkHelperService
{
    /**
     * Format Nigerian phone number to standard format
     */
    public static function formatNigerianPhone(string $phone): string
    {
        // Remove all non-numeric characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle different formats
        if (str_starts_with($cleanPhone, '234')) {
            // Already has country code
            return '+' . $cleanPhone;
        } elseif (str_starts_with($cleanPhone, '0')) {
            // Remove leading zero and add country code
            return '+234' . substr($cleanPhone, 1);
        } else {
            // Add country code directly
            return '+234' . $cleanPhone;
        }
    }

    /**
     * Generate unique driver ID
     */
    public static function generateDriverId(): string
    {
        $prefix = config('drivelink.driver_id_prefix', 'DR');
        $timestamp = now()->format('ymd');
        
        // Get the last driver ID for today
        $lastDriver = Drivers::where('driver_id', 'like', $prefix . $timestamp . '%')
            ->orderBy('driver_id', 'desc')
            ->first();
        
        if ($lastDriver) {
            // Extract sequence number and increment
            $lastSequence = (int) substr($lastDriver->driver_id, -4);
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return $prefix . $timestamp . $sequence;
    }

    /**
     * Generate unique request ID
     */
    public static function generateRequestId(): string
    {
        $prefix = config('drivelink.request_id_prefix', 'REQ');
        $timestamp = now()->format('ymdHis');
        $random = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Get status badge configuration
     */
    public static function getStatusBadge(string $status): array
    {
        $badges = [
            'active' => ['class' => 'badge-success', 'text' => 'Active'],
            'inactive' => ['class' => 'badge-secondary', 'text' => 'Inactive'],
            'suspended' => ['class' => 'badge-warning', 'text' => 'Suspended'],
            'blocked' => ['class' => 'badge-danger', 'text' => 'Blocked'],
            'pending' => ['class' => 'badge-info', 'text' => 'Pending'],
            'verified' => ['class' => 'badge-success', 'text' => 'Verified'],
            'rejected' => ['class' => 'badge-danger', 'text' => 'Rejected'],
            'under_review' => ['class' => 'badge-warning', 'text' => 'Under Review'],
        ];

        return $badges[$status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($status)];
    }

    /**
     * Get verification status badge
     */
    public static function getVerificationBadge(string $status): array
    {
        $badges = [
            'pending' => ['class' => 'badge-warning', 'text' => 'Pending Verification'],
            'verified' => ['class' => 'badge-success', 'text' => 'Verified'],
            'rejected' => ['class' => 'badge-danger', 'text' => 'Verification Rejected'],
            'under_review' => ['class' => 'badge-info', 'text' => 'Under Review'],
            'incomplete' => ['class' => 'badge-secondary', 'text' => 'Incomplete'],
        ];

        return $badges[$status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($status)];
    }

    /**
     * Calculate driver profile completion percentage
     */
    public static function calculateDriverCompletionPercentage(Drivers $driver): int
    {
        $requiredFields = [
            'first_name', 'surname', 'email', 'phone', 'date_of_birth',
            'gender', 'nationality_id', 'nin_number', 'license_number',
            'license_class', 'license_expiry_date', 'residence_address',
            'residence_state_id', 'residence_lga_id'
        ];

        $completedFields = 0;
        
        foreach ($requiredFields as $field) {
            if (!empty($driver->$field)) {
                $completedFields++;
            }
        }

        // Check for required documents
        $requiredDocuments = ['nin', 'license_front', 'license_back', 'profile_picture'];
        $uploadedDocuments = $driver->documents()
            ->whereIn('document_type', $requiredDocuments)
            ->where('verification_status', '!=', 'rejected')
            ->count();

        // Documents count as 25% of completion
        $documentPercentage = ($uploadedDocuments / count($requiredDocuments)) * 25;
        $fieldsPercentage = ($completedFields / count($requiredFields)) * 75;

        return min(100, round($fieldsPercentage + $documentPercentage));
    }

    /**
     * Format currency for Nigerian Naira
     */
    public static function formatNaira(float $amount): string
    {
        return '₦' . number_format($amount, 2);
    }

    /**
     * Parse salary range and return average
     */
    public static function parseSalaryRange(string $salaryRange): ?float
    {
        // Remove currency symbols and commas
        $cleaned = preg_replace('/[₦,\s]/', '', $salaryRange);
        
        // Look for range patterns like "50000-100000" or "50k-100k"
        if (preg_match('/(\d+\.?\d*)k?\s*-\s*(\d+\.?\d*)k?/i', $cleaned, $matches)) {
            $min = (float) $matches[1];
            $max = (float) $matches[2];
            
            // Convert 'k' notation to thousands
            if (str_contains($salaryRange, 'k') || str_contains($salaryRange, 'K')) {
                $min *= 1000;
                $max *= 1000;
            }
            
            return ($min + $max) / 2;
        }
        
        // Single value
        if (preg_match('/(\d+\.?\d*)k?/i', $cleaned, $matches)) {
            $value = (float) $matches[1];
            
            if (str_contains($salaryRange, 'k') || str_contains($salaryRange, 'K')) {
                $value *= 1000;
            }
            
            return $value;
        }
        
        return null;
    }

    /**
     * Get time ago string
     */
    public static function timeAgo(?Carbon $date): string
    {
        if (!$date) {
            return 'Never';
        }

        return $date->diffForHumans();
    }

    /**
     * Mask sensitive data
     */
    public static function maskSensitiveData(string $data, int $visibleChars = 4, string $maskChar = '*'): string
    {
        $length = strlen($data);
        
        if ($length <= $visibleChars) {
            return str_repeat($maskChar, $length);
        }
        
        $visibleStart = substr($data, 0, $visibleChars / 2);
        $visibleEnd = substr($data, -($visibleChars / 2));
        $maskedMiddle = str_repeat($maskChar, $length - $visibleChars);
        
        return $visibleStart . $maskedMiddle . $visibleEnd;
    }

    /**
     * Generate secure random string
     */
    public static function generateSecureRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Validate and clean Nigerian state name
     */
    public static function cleanStateName(string $state): string
    {
        $nigerianStates = [
            'abia', 'adamawa', 'akwa ibom', 'anambra', 'bauchi', 'bayelsa',
            'benue', 'borno', 'cross river', 'delta', 'ebonyi', 'edo',
            'ekiti', 'enugu', 'gombe', 'imo', 'jigawa', 'kaduna',
            'kano', 'katsina', 'kebbi', 'kogi', 'kwara', 'lagos',
            'nasarawa', 'niger', 'ogun', 'ondo', 'osun', 'oyo',
            'plateau', 'rivers', 'sokoto', 'taraba', 'yobe', 'zamfara',
            'fct' // Federal Capital Territory
        ];

        $cleanState = strtolower(trim($state));
        
        // Handle common abbreviations
        $abbreviations = [
            'fct' => 'federal capital territory',
            'akwa-ibom' => 'akwa ibom',
            'cross-river' => 'cross river',
        ];

        if (isset($abbreviations[$cleanState])) {
            $cleanState = $abbreviations[$cleanState];
        }

        return in_array($cleanState, $nigerianStates) ? ucwords($cleanState) : $state;
    }

    /**
     * Log business event
     */
    public static function logBusinessEvent(string $event, array $data = [], string $level = 'info'): void
    {
        $logData = array_merge($data, [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ]);

        \Illuminate\Support\Facades\Log::channel('business')->{$level}($event, $logData);
    }

    /**
     * Sanitize filename for secure storage
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
        
        // Remove multiple consecutive dots or underscores
        $filename = preg_replace('/[._-]+/', '_', $filename);
        
        // Ensure it doesn't start with a dot
        $filename = ltrim($filename, '.');
        
        // Limit length
        if (strlen($filename) > 100) {
            $pathinfo = pathinfo($filename);
            $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
            $filename = substr($pathinfo['filename'], 0, 100 - strlen($extension)) . $extension;
        }
        
        return $filename ?: 'file';
    }

    /**
     * Get dashboard statistics
     */
    public static function getDashboardStats(): array
    {
        return [
            'total_drivers' => Drivers::count(),
            'verified_drivers' => Drivers::verified()->count(),
            'pending_verification' => Drivers::where('verification_status', 'pending')->count(),
            'active_requests' => \App\Models\CompanyRequest::active()->count(),
            'completed_matches' => \App\Models\DriverMatch::completed()->count(),
            'pending_matches' => \App\Models\DriverMatch::pending()->count(),
        ];
    }
}
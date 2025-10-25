<?php

namespace App\Helpers;

class DrivelinkHelper
{
    /**
     * Generate unique driver ID
     */
    public static function generateDriverId()
    {
        do {
            $id = 'DR' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\Driver::where('driver_id', $id)->exists());
        
        return $id;
    }

    /**
     * Format experience level from years
     */
    public static function formatExperienceLevel($years)
    {
        if (!$years || $years < 1) return 'Less than 1 year';
        if ($years < 2) return '1-2 years';
        if ($years <= 5) return '3-5 years';
        if ($years <= 10) return '6-10 years';
        return '10+ years';
    }

    /**
     * Parse experience level to years range
     */
    public static function parseExperienceLevel($experienceLevel)
    {
        switch ($experienceLevel) {
            case '1-2 years':
                return ['min' => 1, 'max' => 2];
            case '3-5 years':
                return ['min' => 3, 'max' => 5];
            case '6-10 years':
                return ['min' => 6, 'max' => 10];
            case '10+ years':
                return ['min' => 10, 'max' => 100];
            default:
                return null;
        }
    }

    /**
     * Format Nigerian phone number
     */
    public static function formatNigerianPhone($phone)
    {
        if (!$phone) return null;
        
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Handle Nigerian phone format
        if (strlen($phone) == 11 && substr($phone, 0, 1) == '0') {
            return '+234' . substr($phone, 1);
        }
        
        if (strlen($phone) == 10) {
            return '+234' . $phone;
        }
        
        if (strlen($phone) == 13 && substr($phone, 0, 3) == '234') {
            return '+' . $phone;
        }
        
        return $phone;
    }

    /**
     * Calculate driver completion percentage
     */
    public static function calculateDriverCompletionPercentage($driver)
    {
        $checks = [
            $driver->profile_picture !== null,
            $driver->nin_number !== null,
            $driver->license_number !== null,
            $driver->phone !== null,
            $driver->email !== null,
            $driver->date_of_birth !== null,
            $driver->locations()->count() > 0,
            $driver->nextOfKin()->count() > 0,
            $driver->bankingDetails()->count() > 0,
        ];
        
        $completedChecks = count(array_filter($checks));
        return round(($completedChecks / count($checks)) * 100);
    }

    /**
     * Get verification badge info
     */
    public static function getVerificationBadge($status)
    {
        return match($status) {
            'verified' => ['text' => 'Verified', 'class' => 'success'],
            'rejected' => ['text' => 'Rejected', 'class' => 'danger'],
            'reviewing' => ['text' => 'Under Review', 'class' => 'info'],
            default => ['text' => 'Pending', 'class' => 'warning']
        };
    }

    /**
     * Get status badge info
     */
    public static function getStatusBadge($status)
    {
        return match($status) {
            'active' => ['text' => 'Active', 'class' => 'success'],
            'inactive' => ['text' => 'Inactive', 'class' => 'warning'],
            'suspended' => ['text' => 'Suspended', 'class' => 'danger'],
            'blocked' => ['text' => 'Blocked', 'class' => 'dark'],
            default => ['text' => 'Unknown', 'class' => 'secondary']
        };
    }

    /**
     * Get Nigerian states
     */
    public static function getNigerianStates()
    {
        return [
            1 => 'Abia', 2 => 'Adamawa', 3 => 'Akwa Ibom', 4 => 'Anambra', 5 => 'Bauchi',
            6 => 'Bayelsa', 7 => 'Benue', 8 => 'Borno', 9 => 'Cross River', 10 => 'Delta',
            11 => 'Ebonyi', 12 => 'Edo', 13 => 'Ekiti', 14 => 'Enugu', 15 => 'FCT',
            16 => 'Gombe', 17 => 'Imo', 18 => 'Jigawa', 19 => 'Kaduna', 20 => 'Kano',
            21 => 'Katsina', 22 => 'Kebbi', 23 => 'Kogi', 24 => 'Kwara', 25 => 'Lagos',
            26 => 'Nasarawa', 27 => 'Niger', 28 => 'Ogun', 29 => 'Ondo', 30 => 'Osun',
            31 => 'Oyo', 32 => 'Plateau', 33 => 'Rivers', 34 => 'Sokoto', 35 => 'Taraba',
            36 => 'Yobe', 37 => 'Zamfara'
        ];
    }

    /**
     * Standardized JSON response helper
     */
    public static function respondJson($status, $message, $data = null, $code = 200)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }
}

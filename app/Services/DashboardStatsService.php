<?php

namespace App\Services;

use App\Models\Drivers as Driver;
use App\Models\CompanyRequest;
use App\Models\DriverMatch;
use App\Models\Commission;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Log;

class DashboardStatsService
{
    /**
     * Get comprehensive dashboard statistics
     */
    public function getDetailedStats(): array
    {
        try {
            $driverStats = $this->getDriverStats();
            $userStats = $this->getUserStats();
            $businessStats = $this->getBusinessStats();

            return array_merge($driverStats, $userStats, $businessStats);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('Dashboard stats query failed, returning defaults', [
                'error' => $e->getMessage()
            ]);
            return $this->getDefaultStats();
        }
    }

    /**
     * Get driver-related statistics
     */
    private function getDriverStats(): array
    {
        $driverStats = Driver::selectRaw('
            COUNT(*) as total_drivers,
            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_drivers,
            SUM(CASE WHEN verification_status = "verified" THEN 1 ELSE 0 END) as verified_drivers,
            SUM(CASE WHEN verification_status = "pending" THEN 1 ELSE 0 END) as pending_verifications,
            SUM(CASE WHEN verification_status = "rejected" THEN 1 ELSE 0 END) as rejected_drivers,
            SUM(CASE WHEN status = "suspended" THEN 1 ELSE 0 END) as suspended_drivers,
            SUM(CASE WHEN profile_picture IS NOT NULL THEN 1 ELSE 0 END) as drivers_with_documents,
            SUM(CASE WHEN ocr_verification_status != "pending" THEN 1 ELSE 0 END) as ocr_processed,
            SUM(CASE WHEN ocr_verification_status = "passed" THEN 1 ELSE 0 END) as ocr_passed,
            SUM(CASE WHEN ocr_verification_status = "failed" THEN 1 ELSE 0 END) as ocr_failed,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as drivers_today,
            SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(NOW()) THEN 1 ELSE 0 END) as drivers_this_week,
            SUM(CASE WHEN YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW()) THEN 1 ELSE 0 END) as drivers_this_month,
            SUM(CASE WHEN DATE(verified_at) = CURDATE() THEN 1 ELSE 0 END) as verifications_today
        ')->first();

        return [
            'total_drivers' => $driverStats->total_drivers ?? 0,
            'active_drivers' => $driverStats->active_drivers ?? 0,
            'verified_drivers' => $driverStats->verified_drivers ?? 0,
            'pending_verifications' => $driverStats->pending_verifications ?? 0,
            'rejected_drivers' => $driverStats->rejected_drivers ?? 0,
            'suspended_drivers' => $driverStats->suspended_drivers ?? 0,
            'drivers_with_documents' => $driverStats->drivers_with_documents ?? 0,
            'ocr_processed' => $driverStats->ocr_processed ?? 0,
            'ocr_passed' => $driverStats->ocr_passed ?? 0,
            'ocr_failed' => $driverStats->ocr_failed ?? 0,
            'drivers_today' => $driverStats->drivers_today ?? 0,
            'drivers_this_week' => $driverStats->drivers_this_week ?? 0,
            'drivers_this_month' => $driverStats->drivers_this_month ?? 0,
            'verifications_today' => $driverStats->verifications_today ?? 0,
        ];
    }

    /**
     * Get user management statistics
     */
    private function getUserStats(): array
    {
        $userStats = AdminUser::selectRaw('
            COUNT(*) as total_users,
            SUM(CASE WHEN status = "Active" THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN role = "Super Admin" THEN 1 ELSE 0 END) as super_admins,
            SUM(CASE WHEN role = "Admin" THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as users_today,
            SUM(CASE WHEN last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as active_last_week
        ')->first();

        return [
            'total_users' => $userStats->total_users ?? 0,
            'active_users' => $userStats->active_users ?? 0,
            'super_admins' => $userStats->super_admins ?? 0,
            'admins' => $userStats->admins ?? 0,
            'users_today' => $userStats->users_today ?? 0,
            'active_last_week' => $userStats->active_last_week ?? 0,
        ];
    }

    /**
     * Get business-related statistics
     */
    private function getBusinessStats(): array
    {
        return [
            'active_requests' => CompanyRequest::where('status', 'active')->count(),
            'completed_matches' => DriverMatch::where('status', 'completed')->count(),
            'pending_matches' => DriverMatch::where('status', 'pending')->count(),
            'total_commission' => Commission::where('status', 'paid')->sum('amount') ?? 0,
            'pending_commission' => Commission::where('status', 'unpaid')->sum('amount') ?? 0,
        ];
    }

    /**
     * Get default statistics when database queries fail
     */
    private function getDefaultStats(): array
    {
        return [
            'total_users' => 0,
            'active_users' => 0,
            'super_admins' => 0,
            'admins' => 0,
            'users_today' => 0,
            'active_last_week' => 0,
            'total_drivers' => 0,
            'active_drivers' => 0,
            'verified_drivers' => 0,
            'pending_verifications' => 0,
            'rejected_drivers' => 0,
            'suspended_drivers' => 0,
            'drivers_with_documents' => 0,
            'ocr_processed' => 0,
            'ocr_passed' => 0,
            'ocr_failed' => 0,
            'active_requests' => 0,
            'completed_matches' => 0,
            'pending_matches' => 0,
            'total_commission' => 0,
            'pending_commission' => 0,
            'drivers_today' => 0,
            'drivers_this_week' => 0,
            'drivers_this_month' => 0,
            'verifications_today' => 0,
        ];
    }
}

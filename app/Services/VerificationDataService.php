<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerificationDataService
{
    /**
     * Get dashboard data for verification overview
     */
    public function getDashboardData(array $dateRange): array
    {
        return [
            'pending_reviews' => $this->getPendingReviews(),
            'recent_activities' => $this->getRecentActivities($dateRange),
            'failed_verifications' => $this->getFailedVerifications(),
        ];
    }

    /**
     * Get verification report data
     */
    public function getReportData(array $dateRange): array
    {
        return [
            'component_stats' => $this->getComponentStats($dateRange),
            'api_stats' => $this->getApiStats($dateRange),
        ];
    }

    /**
     * Get pending manual reviews
     */
    private function getPendingReviews()
    {
        return Driver::where('verification_status', 'requires_manual_review')
            ->with(['driverMatches', 'driverPerformances'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get recent verification activities
     */
    private function getRecentActivities(array $dateRange)
    {
        try {
            return DB::table('driver_verifications')
                ->join('drivers', 'driver_verifications.driver_id', '=', 'drivers.id')
                ->select([
                    'driver_verifications.*',
                    'drivers.first_name',
                    'drivers.last_name',
                    'drivers.email'
                ])
                ->whereBetween('driver_verifications.created_at', [
                    Carbon::parse($dateRange['start']),
                    Carbon::parse($dateRange['end'])
                ])
                ->orderBy('driver_verifications.created_at', 'desc')
                ->take(20)
                ->get();
        } catch (\Exception $e) {
            // Fallback to empty collection if table doesn't exist
            return collect();
        }
    }

    /**
     * Get failed verifications
     */
    private function getFailedVerifications()
    {
        return Driver::where('verification_status', 'failed')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get detailed verification breakdown by component
     */
    private function getComponentStats(array $dateRange)
    {
        return DB::table('driver_verifications')
            ->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ])
            ->select([
                'verification_type',
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(verification_score) as avg_score')
            ])
            ->groupBy('verification_type', 'status')
            ->get();
    }

    /**
     * Get API performance metrics
     */
    private function getApiStats(array $dateRange)
    {
        return DB::table('api_verification_logs')
            ->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ])
            ->select([
                'api_provider',
                'verification_type',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(is_successful) as successful_requests'),
                DB::raw('AVG(response_time_ms) as avg_response_time')
            ])
            ->groupBy('api_provider', 'verification_type')
            ->get();
    }
}

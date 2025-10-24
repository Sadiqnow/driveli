<?php

namespace App\Services;

use App\Models\Drivers as Driver;
use App\Models\CompanyRequest;
use App\Models\DriverMatch;
use App\Models\Commission;
use App\Models\AdminUser;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get comprehensive analytics dashboard data
     */
    public function getDashboardAnalytics(): array
    {
        return Cache::remember('analytics_dashboard', 1800, function () {
            return [
                'overview' => $this->getOverviewMetrics(),
                'trends' => $this->getTrendAnalytics(),
                'performance' => $this->getPerformanceMetrics(),
                'geographic' => $this->getGeographicAnalytics(),
                'category' => $this->getCategoryAnalytics(),
                'financial' => $this->getFinancialAnalytics(),
                'predictions' => $this->getPredictiveAnalytics(),
            ];
        });
    }

    /**
     * Get overview metrics for dashboard
     */
    private function getOverviewMetrics(): array
    {
        $totalDrivers = Driver::count();
        $activeDrivers = Driver::where('status', 'active')->count();
        $verifiedDrivers = Driver::where('verification_status', 'verified')->count();
        $totalCompanies = Company::count();
        $activeRequests = CompanyRequest::where('status', 'active')->count();
        $completedMatches = DriverMatch::where('status', 'completed')->count();

        return [
            'total_drivers' => $totalDrivers,
            'active_drivers' => $activeDrivers,
            'verified_drivers' => $verifiedDrivers,
            'driver_activation_rate' => $totalDrivers > 0 ? round(($activeDrivers / $totalDrivers) * 100, 2) : 0,
            'driver_verification_rate' => $totalDrivers > 0 ? round(($verifiedDrivers / $totalDrivers) * 100, 2) : 0,
            'total_companies' => $totalCompanies,
            'active_requests' => $activeRequests,
            'completed_matches' => $completedMatches,
            'match_success_rate' => $activeRequests > 0 ? round(($completedMatches / $activeRequests) * 100, 2) : 0,
            'total_revenue' => Commission::where('status', 'paid')->sum('amount'),
            'pending_revenue' => Commission::where('status', 'unpaid')->sum('amount'),
        ];
    }

    /**
     * Get trend analytics over time
     */
    private function getTrendAnalytics(): array
    {
        $days = 30;

        // Driver registration trends
        $driverTrends = Driver::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Verification trends
        $verificationTrends = Driver::selectRaw('DATE(verified_at) as date, COUNT(*) as count')
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Match completion trends
        $matchTrends = DriverMatch::selectRaw('DATE(updated_at) as date, COUNT(*) as count')
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Revenue trends
        $revenueTrends = Commission::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return [
            'driver_registrations' => $this->fillMissingDates($driverTrends, $days),
            'verifications' => $this->fillMissingDates($verificationTrends, $days),
            'matches_completed' => $this->fillMissingDates($matchTrends, $days),
            'revenue' => $this->fillMissingDates($revenueTrends, $days),
            'period_days' => $days,
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        // Average verification time
        $avgVerificationTime = Driver::whereNotNull('verified_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, verified_at)) as avg_hours')
            ->value('avg_hours');

        // Average match completion time
        $avgMatchTime = DriverMatch::where('status', 'completed')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours');

        // OCR success rate
        $ocrStats = Driver::selectRaw('
            COUNT(*) as total_processed,
            SUM(CASE WHEN ocr_verification_status = "passed" THEN 1 ELSE 0 END) as passed,
            SUM(CASE WHEN ocr_verification_status = "failed" THEN 1 ELSE 0 END) as failed
        ')->first();

        // Profile completion rates
        $profileCompletion = $this->calculateProfileCompletionRates();

        return [
            'avg_verification_time_hours' => round($avgVerificationTime ?? 0, 1),
            'avg_match_completion_time_hours' => round($avgMatchTime ?? 0, 1),
            'ocr_success_rate' => $ocrStats->total_processed > 0
                ? round(($ocrStats->passed / $ocrStats->total_processed) * 100, 2)
                : 0,
            'profile_completion' => $profileCompletion,
            'system_performance' => $this->getSystemPerformanceMetrics(),
        ];
    }

    /**
     * Get geographic analytics
     */
    private function getGeographicAnalytics(): array
    {
        // Driver distribution by state
        $driversByState = Driver::join('driver_locations', 'drivers.id', '=', 'driver_locations.driver_id')
            ->selectRaw('driver_locations.state, COUNT(*) as count')
            ->groupBy('driver_locations.state')
            ->orderBy('count', 'desc')
            ->pluck('count', 'state')
            ->toArray();

        // Company distribution by state
        $companiesByState = Company::selectRaw('state, COUNT(*) as count')
            ->whereNotNull('state')
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->pluck('count', 'state')
            ->toArray();

        // Request distribution by state
        $requestsByState = CompanyRequest::join('companies', 'company_requests.company_id', '=', 'companies.id')
            ->selectRaw('companies.state, COUNT(*) as count')
            ->groupBy('companies.state')
            ->orderBy('count', 'desc')
            ->pluck('count', 'state')
            ->toArray();

        return [
            'drivers_by_state' => $driversByState,
            'companies_by_state' => $companiesByState,
            'requests_by_state' => $requestsByState,
            'top_states' => array_slice(array_keys($driversByState), 0, 5),
        ];
    }

    /**
     * Get category-based analytics
     */
    private function getCategoryAnalytics(): array
    {
        // Driver categories (if implemented)
        $driversByCategory = Driver::selectRaw('COALESCE(driver_category, "Unspecified") as category, COUNT(*) as count')
            ->groupBy('driver_category')
            ->pluck('count', 'category')
            ->toArray();

        // Request types distribution
        $requestsByType = CompanyRequest::selectRaw('request_type, COUNT(*) as count')
            ->whereNotNull('request_type')
            ->groupBy('request_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'request_type')
            ->toArray();

        // Match success by category
        $matchSuccessByCategory = DriverMatch::join('drivers', 'driver_matches.driver_id', '=', 'drivers.id')
            ->selectRaw('COALESCE(drivers.driver_category, "Unspecified") as category, COUNT(*) as total_matches')
            ->where('driver_matches.status', 'completed')
            ->groupBy('drivers.driver_category')
            ->pluck('total_matches', 'category')
            ->toArray();

        return [
            'drivers_by_category' => $driversByCategory,
            'requests_by_type' => $requestsByType,
            'match_success_by_category' => $matchSuccessByCategory,
            'category_performance' => $this->calculateCategoryPerformance(),
        ];
    }

    /**
     * Get financial analytics
     */
    private function getFinancialAnalytics(): array
    {
        $monthlyRevenue = Commission::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            SUM(amount) as revenue,
            COUNT(*) as transactions
        ')
        ->where('status', 'paid')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();

        $revenueByDriver = Commission::join('drivers', 'commissions.driver_id', '=', 'drivers.id')
            ->selectRaw('drivers.first_name, drivers.surname, SUM(commissions.amount) as total_earned')
            ->where('commissions.status', 'paid')
            ->groupBy('drivers.id', 'drivers.first_name', 'drivers.surname')
            ->orderBy('total_earned', 'desc')
            ->limit(10)
            ->get();

        $commissionRates = Commission::selectRaw('
            commission_rate,
            COUNT(*) as count,
            AVG(amount) as avg_amount
        ')
        ->groupBy('commission_rate')
        ->orderBy('commission_rate')
        ->limit(20)
        ->get();

        return [
            'monthly_revenue' => $monthlyRevenue,
            'top_earning_drivers' => $revenueByDriver,
            'commission_distribution' => $commissionRates,
            'financial_summary' => $this->getFinancialSummary(),
        ];
    }

    /**
     * Get predictive analytics
     */
    private function getPredictiveAnalytics(): array
    {
        // Simple trend analysis for next month prediction
        $last3Months = Driver::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            COUNT(*) as registrations
        ')
        ->where('created_at', '>=', now()->subMonths(3))
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->pluck('registrations')
        ->toArray();

        $predictedRegistrations = $this->predictNextMonth($last3Months);

        // Growth rate calculation
        $currentMonth = Driver::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonth = Driver::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month - 1)
            ->count();

        $growthRate = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return [
            'predicted_registrations_next_month' => round($predictedRegistrations),
            'current_growth_rate' => round($growthRate, 2),
            'trend_direction' => $growthRate > 0 ? 'increasing' : ($growthRate < 0 ? 'decreasing' : 'stable'),
            'seasonal_patterns' => $this->analyzeSeasonalPatterns(),
        ];
    }

    /**
     * Helper method to fill missing dates with zero values
     */
    private function fillMissingDates(array $data, int $days): array
    {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[] = [
                'date' => $date,
                'value' => $data[$date] ?? 0,
            ];
        }
        return $result;
    }

    /**
     * Calculate profile completion rates
     */
    private function calculateProfileCompletionRates(): array
    {
        $total = Driver::count();
        if ($total === 0) return ['basic' => 0, 'complete' => 0, 'full' => 0];

        $basic = Driver::whereNotNull('email')
            ->whereNotNull('phone')
            ->count();

        $complete = Driver::whereNotNull('profile_picture')
            ->whereNotNull('nin_number')
            ->whereNotNull('license_number')
            ->count();

        $full = Driver::whereNotNull('profile_picture')
            ->whereNotNull('nin_number')
            ->whereNotNull('license_number')
            ->whereHas('locations')
            ->whereHas('nextOfKin')
            ->whereHas('bankingDetails')
            ->count();

        return [
            'basic' => round(($basic / $total) * 100, 2),
            'complete' => round(($complete / $total) * 100, 2),
            'full' => round(($full / $total) * 100, 2),
        ];
    }

    /**
     * Get system performance metrics
     */
    private function getSystemPerformanceMetrics(): array
    {
        return [
            'avg_response_time' => Cache::get('avg_response_time', 0),
            'slow_queries_count' => Cache::get('slow_queries_count', 0),
            'cache_hit_rate' => Cache::get('cache_hit_rate', 0),
            'error_rate' => Cache::get('error_rate', 0),
        ];
    }

    /**
     * Calculate category performance
     */
    private function calculateCategoryPerformance(): array
    {
        $categories = Driver::selectRaw('COALESCE(driver_category, "Unspecified") as category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $performance = [];
        foreach ($categories as $category) {
            $drivers = Driver::where('driver_category', $category)->orWhere(function($q) use ($category) {
                if ($category === 'Unspecified') {
                    $q->whereNull('driver_category');
                }
            });

            $totalDrivers = $drivers->count();
            $verifiedDrivers = (clone $drivers)->where('verification_status', 'verified')->count();
            $activeDrivers = (clone $drivers)->where('status', 'active')->count();

            $performance[$category] = [
                'total_drivers' => $totalDrivers,
                'verification_rate' => $totalDrivers > 0 ? round(($verifiedDrivers / $totalDrivers) * 100, 2) : 0,
                'activation_rate' => $totalDrivers > 0 ? round(($activeDrivers / $totalDrivers) * 100, 2) : 0,
            ];
        }

        return $performance;
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary(): array
    {
        $totalRevenue = Commission::where('status', 'paid')->sum('amount');
        $pendingRevenue = Commission::where('status', 'unpaid')->sum('amount');
        $monthlyRevenue = Commission::where('status', 'paid')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return [
            'total_revenue' => $totalRevenue,
            'pending_revenue' => $pendingRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'avg_transaction_value' => $totalRevenue > 0 ? round($totalRevenue / Commission::where('status', 'paid')->count(), 2) : 0,
        ];
    }

    /**
     * Simple linear regression for prediction
     */
    private function predictNextMonth(array $data): float
    {
        if (count($data) < 2) return 0;

        $n = count($data);
        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($data);
        $sumXY = 0;
        $sumXX = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += ($i + 1) * $data[$i];
            $sumXX += ($i + 1) * ($i + 1);
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        return $intercept + $slope * ($n + 1);
    }

    /**
     * Analyze seasonal patterns
     */
    private function analyzeSeasonalPatterns(): array
    {
        $monthlyData = Driver::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $peakMonth = array_keys($monthlyData, max($monthlyData))[0] ?? null;
        $lowMonth = array_keys($monthlyData, min($monthlyData))[0] ?? null;

        return [
            'peak_month' => $peakMonth,
            'low_month' => $lowMonth,
            'seasonal_variation' => count($monthlyData) > 0 ? round((max($monthlyData) - min($monthlyData)) / array_sum($monthlyData) * 100, 2) : 0,
        ];
    }

    /**
     * Export analytics data
     */
    public function exportAnalytics(string $type = 'full'): array
    {
        $data = $this->getDashboardAnalytics();

        return [
            'export_type' => $type,
            'generated_at' => now()->toISOString(),
            'data' => $type === 'summary' ? $data['overview'] : $data,
        ];
    }

    /**
     * Clear analytics cache
     */
    public function clearAnalyticsCache(): void
    {
        Cache::forget('analytics_dashboard');
        Cache::forget('analytics_dashboard_summary');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuperAdminAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
    }

    /**
     * Display super admin dashboard
     */
    public function index()
    {
        // Get system overview stats
        $stats = $this->getSystemStats();

        return view('admin.superadmin.index', compact('stats'));
    }

    /**
     * Display driver analytics
     */
    public function driversAnalytics(Request $request)
    {
        // Get analytics data
        $analytics = $this->getDriverAnalytics();

        return view('admin.superadmin.drivers.analytics', compact('analytics'));
    }

    /**
     * Display driver audit trail
     */
    public function driversAudit(Request $request)
    {
        $query = \App\Models\SuperadminActivityLog::with(['superadmin'])
            ->where('resource_type', 'driver')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('driver_id')) {
            $query->where('resource_id', $request->driver_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(50);

        return view('admin.superadmin.drivers.audit', compact('activities'));
    }

    /**
     * Get system statistics
     */
    private function getSystemStats()
    {
        return [
            'total_users' => \App\Models\AdminUser::count(),
            'active_users' => \App\Models\AdminUser::where('status', 'Active')->count(),
            'total_drivers' => \App\Models\Driver::count(),
            'verified_drivers' => \App\Models\Driver::where('verification_status', 'verified')->count(),
            'total_activities' => \Illuminate\Support\Facades\Schema::hasTable('user_activities') ? \App\Models\UserActivity::count() : 0,
            'recent_activities' => \Illuminate\Support\Facades\Schema::hasTable('user_activities') ? \App\Models\UserActivity::where('created_at', '>=', now()->subDay())->count() : 0,
            'system_uptime' => $this->getSystemUptime(),
            'database_size' => $this->getDatabaseSize()
        ];
    }

    /**
     * Get driver analytics data
     */
    private function getDriverAnalytics()
    {
        $totalDrivers = \App\Models\Driver::count();
        $verifiedDrivers = \App\Models\Driver::where('verification_status', 'verified')->count();
        $activeDrivers = \App\Models\Driver::where('status', 'active')->count();

        // Performance metrics
        $performanceData = \App\Models\DriverPerformance::selectRaw('
            AVG(rating) as avg_rating,
            COUNT(*) as total_ratings,
            SUM(total_jobs) as total_jobs,
            SUM(total_earnings) as total_earnings
        ')->first();

        // Monthly registration trends (last 12 months)
        $monthlyRegistrations = \App\Models\Driver::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as count
        ')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();

        // Verification status distribution
        $verificationStats = \App\Models\Driver::selectRaw('
            verification_status,
            COUNT(*) as count
        ')
        ->groupBy('verification_status')
        ->pluck('count', 'verification_status')
        ->toArray();

        // KYC completion rates
        $kycStats = \App\Models\Driver::selectRaw('
            kyc_status,
            COUNT(*) as count
        ')
        ->groupBy('kyc_status')
        ->pluck('count', 'kyc_status')
        ->toArray();

        // Document verification stats
        $documentStats = \App\Models\DriverDocument::selectRaw('
            verification_status,
            COUNT(*) as count
        ')
        ->groupBy('verification_status')
        ->pluck('count', 'verification_status')
        ->toArray();

        // Top performing drivers
        $topDrivers = \App\Models\Driver::with('performance')
            ->join('driver_performances', 'drivers.id', '=', 'driver_performances.driver_id')
            ->orderBy('driver_performances.rating', 'desc')
            ->orderBy('driver_performances.total_jobs', 'desc')
            ->limit(10)
            ->get(['drivers.*', 'driver_performances.rating', 'driver_performances.total_jobs']);

        return [
            'overview' => [
                'total_drivers' => $totalDrivers,
                'verified_drivers' => $verifiedDrivers,
                'active_drivers' => $activeDrivers,
                'verification_rate' => $totalDrivers > 0 ? round(($verifiedDrivers / $totalDrivers) * 100, 2) : 0,
                'active_rate' => $totalDrivers > 0 ? round(($activeDrivers / $totalDrivers) * 100, 2) : 0,
            ],
            'performance' => [
                'average_rating' => round($performanceData->avg_rating ?? 0, 1),
                'total_ratings' => $performanceData->total_ratings ?? 0,
                'total_jobs' => $performanceData->total_jobs ?? 0,
                'total_earnings' => $performanceData->total_earnings ?? 0,
            ],
            'trends' => [
                'monthly_registrations' => $monthlyRegistrations,
            ],
            'verification_distribution' => $verificationStats,
            'kyc_distribution' => $kycStats,
            'document_distribution' => $documentStats,
            'top_performers' => $topDrivers,
        ];
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows uptime (simplified)
            return 'Windows Server - Uptime data not available';
        } else {
            // Linux/Unix uptime
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime) {
                $uptime = explode(' ', $uptime)[0];
                $days = floor($uptime / 86400);
                $hours = floor(($uptime % 86400) / 3600);
                $minutes = floor(($uptime % 3600) / 60);
                return "{$days}d {$hours}h {$minutes}m";
            }
        }

        return 'Uptime data not available';
    }

    /**
     * Get database size
     */
    private function getDatabaseSize()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = \Illuminate\Support\Facades\DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
            return $result[0]->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'Size unknown';
        }
    }
}

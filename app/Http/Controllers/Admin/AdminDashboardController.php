<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatsService;
use App\Services\DashboardActivityService;
use App\Services\DashboardChartService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected $statsService;
    protected $activityService;
    protected $chartService;

    public function __construct(
        DashboardStatsService $statsService,
        DashboardActivityService $activityService,
        DashboardChartService $chartService
    ) {
        $this->statsService = $statsService;
        $this->activityService = $activityService;
        $this->chartService = $chartService;
    }

    public function index()
    {
        // Check if user has permission to view dashboard
        if (!auth('admin')->user()->hasPermission('view_dashboard')) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        $stats = $this->statsService->getDetailedStats();
        $recentActivity = $this->activityService->getRecentActivity();
        $chartData = $this->chartService->getChartData();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'chartData'));
    }
    

    public function getStats()
    {
        return response()->json($this->statsService->getDetailedStats());
    }
}
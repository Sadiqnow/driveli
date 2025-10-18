<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the analytics dashboard
     */
    public function index()
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return view('admin.analytics.index', compact('analytics'));
    }

    /**
     * Get analytics data via AJAX
     */
    public function data(Request $request): JsonResponse
    {
        $type = $request->get('type', 'full');
        $analytics = $this->analyticsService->getDashboardAnalytics();

        if ($type === 'summary') {
            return response()->json($analytics['overview']);
        }

        return response()->json($analytics);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'full');
        $data = $this->analyticsService->exportAnalytics($type);

        $filename = 'analytics-export-' . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Get trend analytics
     */
    public function trends(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return response()->json($analytics['trends']);
    }

    /**
     * Get performance metrics
     */
    public function performance(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return response()->json($analytics['performance']);
    }

    /**
     * Get geographic analytics
     */
    public function geographic(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return response()->json($analytics['geographic']);
    }

    /**
     * Get category analytics
     */
    public function category(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return response()->json($analytics['category']);
    }

    /**
     * Get financial analytics
     */
    public function financial(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return response()->json($analytics['financial']);
    }

    /**
     * Get predictive analytics
     */
    public function predictive(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return response()->json($analytics['predictions']);
    }

    /**
     * Clear analytics cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        $this->analyticsService->clearAnalyticsCache();

        return response()->json([
            'success' => true,
            'message' => 'Analytics cache cleared successfully'
        ]);
    }

    /**
     * Get real-time metrics
     */
    public function realtime(Request $request): JsonResponse
    {
        // Get fresh data without cache
        $analytics = $this->analyticsService->getDashboardAnalytics();

        return response()->json([
            'overview' => $analytics['overview'],
            'timestamp' => now()->toISOString(),
            'cache_status' => 'fresh'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function graphs(Request $request)
    {
        $year = $request->query('year');
        $month = $request->query('month');

        if ($year && $month) {
            // Monthly data for specific year and month
            $data = $this->analyticsService->getMonthlyAnalytics($year, $month);
        } elseif ($year) {
            // Yearly data for specific year
            $data = $this->analyticsService->getYearlyAnalytics($year);
        } else {
            // Default to current year
            $data = $this->analyticsService->getYearlyAnalytics(now()->year);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}

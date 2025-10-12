<?php

namespace App\Services;

use App\Models\Drivers;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getYearlyAnalytics($year)
    {
        // Get yearly analytics data
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        $data = [
            'year' => $year,
            'total_drivers' => Drivers::whereBetween('created_at', [$startDate, $endDate])->count(),
            'verified_drivers' => Drivers::where('kyc_status', 'verified')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_drivers' => Drivers::where('kyc_status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'rejected_drivers' => Drivers::where('kyc_status', 'rejected')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'monthly_breakdown' => $this->getMonthlyBreakdown($year),
        ];

        return $data;
    }

    public function getMonthlyAnalytics($year, $month)
    {
        // Get monthly analytics data
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $data = [
            'year' => $year,
            'month' => $month,
            'total_drivers' => Drivers::whereBetween('created_at', [$startDate, $endDate])->count(),
            'verified_drivers' => Drivers::where('kyc_status', 'verified')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_drivers' => Drivers::where('kyc_status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'rejected_drivers' => Drivers::where('kyc_status', 'rejected')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'daily_breakdown' => $this->getDailyBreakdown($year, $month),
        ];

        return $data;
    }

    private function getMonthlyBreakdown($year)
    {
        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $startDate = "{$year}-{$month}-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            $data[] = [
                'month' => $month,
                'total' => Drivers::whereBetween('created_at', [$startDate, $endDate])->count(),
                'verified' => Drivers::where('kyc_status', 'verified')->whereBetween('created_at', [$startDate, $endDate])->count(),
            ];
        }
        return $data;
    }

    private function getDailyBreakdown($year, $month)
    {
        $data = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = "{$year}-{$month}-{$day}";
            $data[] = [
                'day' => $day,
                'total' => Drivers::whereDate('created_at', $date)->count(),
                'verified' => Drivers::where('kyc_status', 'verified')->whereDate('created_at', $date)->count(),
            ];
        }
        return $data;
    }
}

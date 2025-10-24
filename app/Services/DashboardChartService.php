<?php

namespace App\Services;

use App\Models\Drivers as Driver;
use Carbon\Carbon;

class DashboardChartService
{
    /**
     * Get all chart data for dashboard
     */
    public function getChartData(): array
    {
        return [
            'driver_registrations' => $this->getDriverRegistrationData(),
            'verification_breakdown' => $this->getVerificationBreakdown(),
            'status_breakdown' => $this->getStatusBreakdown(),
            'ocr_stats' => $this->getOcrStats(),
        ];
    }

    /**
     * Get driver registration data for the last 30 days
     */
    private function getDriverRegistrationData(): array
    {
        $driverRegistrations = Driver::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $registrationData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $registrationData[] = [
                'date' => $date->format('M d'),
                'count' => $driverRegistrations->get($dateKey)?->count ?? 0
            ];
        }

        return $registrationData;
    }

    /**
     * Get verification status breakdown
     */
    private function getVerificationBreakdown(): array
    {
        $breakdown = Driver::selectRaw('verification_status, COUNT(*) as count')
            ->whereIn('verification_status', ['verified', 'pending', 'rejected', 'reviewing'])
            ->groupBy('verification_status')
            ->pluck('count', 'verification_status')
            ->toArray();

        return $this->fillMissingStatuses($breakdown, ['verified', 'pending', 'rejected', 'reviewing']);
    }

    /**
     * Get driver status breakdown
     */
    private function getStatusBreakdown(): array
    {
        $breakdown = Driver::selectRaw('status, COUNT(*) as count')
            ->whereIn('status', ['active', 'inactive', 'suspended', 'blocked'])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return $this->fillMissingStatuses($breakdown, ['active', 'inactive', 'suspended', 'blocked']);
    }

    /**
     * Get OCR verification statistics
     */
    private function getOcrStats(): array
    {
        $stats = Driver::selectRaw('ocr_verification_status, COUNT(*) as count')
            ->whereIn('ocr_verification_status', ['passed', 'failed', 'pending'])
            ->groupBy('ocr_verification_status')
            ->pluck('count', 'ocr_verification_status')
            ->toArray();

        return $this->fillMissingStatuses($stats, ['passed', 'failed', 'pending']);
    }

    /**
     * Fill missing statuses with zero values
     */
    private function fillMissingStatuses(array $data, array $expectedStatuses): array
    {
        $defaults = array_fill_keys($expectedStatuses, 0);
        return array_merge($defaults, $data);
    }
}

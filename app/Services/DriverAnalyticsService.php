<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverAnalyticsService
{
    /**
     * Get driver statistics for dashboard
     */
    public function getDriverStats(): array
    {
        try {
            $stats = [
                'total_drivers' => Driver::count(),
                'active_drivers' => Driver::where('status', 'active')->count(),
                'inactive_drivers' => Driver::where('status', 'inactive')->count(),
                'suspended_drivers' => Driver::where('status', 'suspended')->count(),
                'verified_drivers' => Driver::where('verification_status', 'verified')->count(),
                'pending_verification' => Driver::where('verification_status', 'pending')->count(),
                'rejected_drivers' => Driver::where('verification_status', 'rejected')->count(),
                'drivers_registered_today' => Driver::whereDate('created_at', today())->count(),
                'drivers_registered_this_week' => Driver::where('created_at', '>=', now()->startOfWeek())->count(),
                'drivers_registered_this_month' => Driver::where('created_at', '>=', now()->startOfMonth())->count(),
            ];

            // Calculate growth rates
            $lastMonthCount = Driver::where('created_at', '>=', now()->subMonth()->startOfMonth())
                                  ->where('created_at', '<', now()->startOfMonth())
                                  ->count();
            $thisMonthCount = $stats['drivers_registered_this_month'];

            $stats['monthly_growth_rate'] = $lastMonthCount > 0
                ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 2)
                : ($thisMonthCount > 0 ? 100 : 0);

            // Verification rate
            $totalWithVerification = $stats['verified_drivers'] + $stats['rejected_drivers'];
            $stats['verification_rate'] = $totalWithVerification > 0
                ? round(($stats['verified_drivers'] / $totalWithVerification) * 100, 2)
                : 0;

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to get driver stats: ' . $e->getMessage());
            return [
                'total_drivers' => 0,
                'active_drivers' => 0,
                'inactive_drivers' => 0,
                'suspended_drivers' => 0,
                'verified_drivers' => 0,
                'pending_verification' => 0,
                'rejected_drivers' => 0,
                'drivers_registered_today' => 0,
                'drivers_registered_this_week' => 0,
                'drivers_registered_this_month' => 0,
                'monthly_growth_rate' => 0,
                'verification_rate' => 0,
            ];
        }
    }

    /**
     * Get recently registered drivers
     */
    public function getRecentDrivers(int $limit = 10, int $days = 30): array
    {
        try {
            $drivers = Driver::select([
                'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
                'status', 'verification_status', 'created_at'
            ])
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'driver_id' => $driver->driver_id,
                    'full_name' => $driver->full_name,
                    'email' => $driver->email,
                    'phone' => $driver->phone,
                    'status' => $driver->status,
                    'verification_status' => $driver->verification_status,
                    'registered_at' => $driver->created_at->format('Y-m-d H:i:s'),
                    'days_since_registration' => $driver->created_at->diffInDays(now())
                ];
            });

            return [
                'success' => true,
                'drivers' => $drivers
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get recent drivers: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load recent drivers',
                'drivers' => []
            ];
        }
    }

    /**
     * Get verification statistics
     */
    public function getVerificationStats(string $period = 'month'): array
    {
        try {
            $query = Driver::whereNotNull('verified_at');

            switch ($period) {
                case 'day':
                    $query->whereDate('verified_at', today());
                    break;
                case 'week':
                    $query->where('verified_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where('verified_at', '>=', now()->startOfMonth());
                    break;
                case 'year':
                    $query->where('verified_at', '>=', now()->startOfYear());
                    break;
            }

            $verifiedCount = $query->count();

            // Average verification time
            $avgTime = Driver::whereNotNull('verified_at')
                           ->where('verified_at', '>=', now()->subDays(30))
                           ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, verified_at)) as avg_hours')
                           ->first()
                           ->avg_hours ?? 0;

            $stats = [
                'verified_in_period' => $verifiedCount,
                'pending_verification' => Driver::where('verification_status', 'pending')->count(),
                'average_verification_time_hours' => round($avgTime, 2),
                'verification_success_rate' => $this->calculateVerificationSuccessRate(),
                'verifications_by_admin' => $this->getVerificationsByAdmin($period),
                'daily_verification_trend' => $this->getVerificationTrend(7) // Last 7 days
            ];

            return [
                'success' => true,
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get verification stats: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load verification statistics',
                'stats' => []
            ];
        }
    }

    /**
     * Get KYC statistics
     */
    public function getKycStats(): array
    {
        try {
            $stats = [
                'total_kyc_submissions' => Driver::whereNotNull('kyc_submitted_at')->count(),
                'completed_kyc' => Driver::where('kyc_status', 'completed')->count(),
                'pending_kyc_review' => Driver::where('kyc_status', 'completed')
                                            ->where('verification_status', 'pending')->count(),
                'rejected_kyc' => Driver::where('kyc_status', 'rejected')->count(),
                'in_progress_kyc' => Driver::where('kyc_status', 'in_progress')->count(),
                'kyc_completion_rate' => $this->calculateKycCompletionRate(),
                'average_kyc_completion_time' => $this->calculateAverageKycTime(),
                'kyc_steps_completion' => $this->getKycStepsStats(),
                'document_upload_stats' => $this->getDocumentUploadStats()
            ];

            return [
                'success' => true,
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get KYC stats: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load KYC statistics',
                'stats' => []
            ];
        }
    }

    /**
     * Get driver activity data
     */
    public function getDriverActivity(int $days = 30): array
    {
        try {
            // Daily registration activity
            $registrationActivity = Driver::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                        ->where('created_at', '>=', now()->subDays($days))
                                        ->groupBy('date')
                                        ->orderBy('date')
                                        ->get()
                                        ->keyBy('date');

            // Verification activity
            $verificationActivity = Driver::selectRaw('DATE(verified_at) as date, COUNT(*) as count')
                                        ->whereNotNull('verified_at')
                                        ->where('verified_at', '>=', now()->subDays($days))
                                        ->groupBy('date')
                                        ->orderBy('date')
                                        ->get()
                                        ->keyBy('date');

            // Status changes activity
            $statusActivity = DB::table('driver_status_history')
                              ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                              ->where('created_at', '>=', now()->subDays($days))
                              ->groupBy('date')
                              ->orderBy('date')
                              ->get()
                              ->keyBy('date');

            // Format data for charts
            $dates = [];
            $currentDate = now()->subDays($days - 1);

            for ($i = 0; $i < $days; $i++) {
                $date = $currentDate->format('Y-m-d');
                $dates[] = [
                    'date' => $date,
                    'registrations' => isset($registrationActivity[$date]) ? $registrationActivity[$date]->count : 0,
                    'verifications' => isset($verificationActivity[$date]) ? $verificationActivity[$date]->count : 0,
                    'status_changes' => isset($statusActivity[$date]) ? $statusActivity[$date]->count : 0
                ];
                $currentDate = $currentDate->addDay();
            }

            return [
                'success' => true,
                'activity' => $dates
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get driver activity: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load driver activity data',
                'activity' => []
            ];
        }
    }

    /**
     * Get driver performance metrics
     */
    public function getDriverPerformance(): array
    {
        try {
            // This would integrate with driver performance/performance tables
            // For now, return basic metrics based on available data

            $performance = [
                'average_profile_completion' => $this->calculateAverageProfileCompletion(),
                'verification_completion_rate' => $this->calculateVerificationCompletionRate(),
                'document_upload_rate' => $this->calculateDocumentUploadRate(),
                'kyc_completion_rate' => $this->calculateKycCompletionRate(),
                'top_performing_drivers' => $this->getTopPerformingDrivers(10),
                'performance_distribution' => $this->getPerformanceDistribution()
            ];

            return [
                'success' => true,
                'performance' => $performance
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get driver performance: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load driver performance metrics',
                'performance' => []
            ];
        }
    }

    /**
     * Get driver demographic data
     */
    public function getDriverDemographics(): array
    {
        try {
            $demographics = [
                'gender_distribution' => $this->getGenderDistribution(),
                'age_distribution' => $this->getAgeDistribution(),
                'state_distribution' => $this->getStateDistribution(),
                'nationality_distribution' => $this->getNationalityDistribution(),
                'experience_distribution' => $this->getExperienceDistribution(),
                'registration_trends' => $this->getRegistrationTrends()
            ];

            return [
                'success' => true,
                'demographics' => $demographics
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get driver demographics: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load driver demographic data',
                'demographics' => []
            ];
        }
    }

    /**
     * Get driver retention metrics
     */
    public function getDriverRetention(string $period = 'month'): array
    {
        try {
            // Calculate retention based on active drivers over time
            $retention = [
                'current_active_drivers' => Driver::where('status', 'active')->count(),
                'retention_rate_30_days' => $this->calculateRetentionRate(30),
                'retention_rate_90_days' => $this->calculateRetentionRate(90),
                'retention_rate_6_months' => $this->calculateRetentionRate(180),
                'retention_rate_1_year' => $this->calculateRetentionRate(365),
                'churn_rate' => $this->calculateChurnRate($period),
                'retention_trends' => $this->getRetentionTrends(12) // Last 12 months
            ];

            return [
                'success' => true,
                'retention' => $retention
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get driver retention: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load driver retention metrics',
                'retention' => []
            ];
        }
    }

    /**
     * Get driver engagement metrics
     */
    public function getDriverEngagement(): array
    {
        try {
            $engagement = [
                'profile_completion_rate' => $this->calculateAverageProfileCompletion(),
                'document_upload_completion' => $this->calculateDocumentUploadRate(),
                'kyc_completion_rate' => $this->calculateKycCompletionRate(),
                'verification_completion_rate' => $this->calculateVerificationCompletionRate(),
                'average_session_duration' => 0, // Would need session tracking
                'feature_usage_stats' => $this->getFeatureUsageStats(),
                'engagement_score_distribution' => $this->getEngagementScoreDistribution()
            ];

            return [
                'success' => true,
                'engagement' => $engagement
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get driver engagement: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load driver engagement metrics',
                'engagement' => []
            ];
        }
    }

    /**
     * Get driver satisfaction data
     */
    public function getDriverSatisfaction(): array
    {
        try {
            // This would integrate with satisfaction surveys/ratings
            // For now, return basic satisfaction indicators

            $satisfaction = [
                'average_rating' => 0, // Would come from ratings table
                'satisfaction_trends' => [], // Would come from historical data
                'complaint_resolution_rate' => 0, // Would come from support tickets
                'support_ticket_volume' => 0, // Would come from support system
                'app_crash_reports' => 0, // Would come from error tracking
                'feature_request_volume' => 0, // Would come from feedback system
                'satisfaction_indicators' => [
                    'profile_completion_satisfaction' => $this->calculateAverageProfileCompletion(),
                    'verification_process_satisfaction' => $this->calculateVerificationCompletionRate(),
                    'support_response_satisfaction' => 0 // Would need support system integration
                ]
            ];

            return [
                'success' => true,
                'satisfaction' => $satisfaction
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get driver satisfaction: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load driver satisfaction data',
                'satisfaction' => []
            ];
        }
    }

    // ========================================================================================
    // HELPER METHODS FOR ANALYTICS
    // ========================================================================================

    /**
     * Calculate verification success rate
     */
    private function calculateVerificationSuccessRate(): float
    {
        $totalProcessed = Driver::whereIn('verification_status', ['verified', 'rejected'])->count();
        $verified = Driver::where('verification_status', 'verified')->count();

        return $totalProcessed > 0 ? round(($verified / $totalProcessed) * 100, 2) : 0;
    }

    /**
     * Get verifications by admin
     */
    private function getVerificationsByAdmin(string $period): array
    {
        $query = Driver::selectRaw('verified_by, COUNT(*) as count')
                     ->whereNotNull('verified_by')
                     ->where('verification_status', 'verified');

        switch ($period) {
            case 'day':
                $query->whereDate('verified_at', today());
                break;
            case 'week':
                $query->where('verified_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->where('verified_at', '>=', now()->startOfMonth());
                break;
            case 'year':
                $query->where('verified_at', '>=', now()->startOfYear());
                break;
        }

        return $query->groupBy('verified_by')
                    ->with('verifiedBy:id,name,email')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'admin_name' => $item->verifiedBy->name ?? 'Unknown',
                            'count' => $item->count
                        ];
                    })
                    ->toArray();
    }

    /**
     * Get verification trend for last N days
     */
    private function getVerificationTrend(int $days): array
    {
        $trend = Driver::selectRaw('DATE(verified_at) as date, COUNT(*) as count')
                     ->whereNotNull('verified_at')
                     ->where('verified_at', '>=', now()->subDays($days))
                     ->groupBy('date')
                     ->orderBy('date')
                     ->get()
                     ->keyBy('date');

        $result = [];
        $currentDate = now()->subDays($days - 1);

        for ($i = 0; $i < $days; $i++) {
            $date = $currentDate->format('Y-m-d');
            $result[] = [
                'date' => $date,
                'count' => isset($trend[$date]) ? $trend[$date]->count : 0
            ];
            $currentDate = $currentDate->addDay();
        }

        return $result;
    }

    /**
     * Calculate KYC completion rate
     */
    private function calculateKycCompletionRate(): float
    {
        $totalWithKyc = Driver::whereNotNull('kyc_submitted_at')->count();
        $completed = Driver::where('kyc_status', 'completed')->count();

        return $totalWithKyc > 0 ? round(($completed / $totalWithKyc) * 100, 2) : 0;
    }

    /**
     * Calculate average KYC completion time
     */
    private function calculateAverageKycTime(): float
    {
        $avgTime = Driver::whereNotNull('kyc_completed_at')
                       ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, kyc_submitted_at, kyc_completed_at)) as avg_hours')
                       ->first()
                       ->avg_hours ?? 0;

        return round($avgTime, 2);
    }

    /**
     * Get KYC steps completion statistics
     */
    private function getKycStepsStats(): array
    {
        // This would analyze kyc_step_data JSON field
        // For now, return basic stats
        return [
            'step_1_completion' => Driver::where('kyc_step', '>=', 1)->count(),
            'step_2_completion' => Driver::where('kyc_step', '>=', 2)->count(),
            'step_3_completion' => Driver::where('kyc_step', '>=', 3)->count(),
            'step_4_completion' => Driver::where('kyc_step', '>=', 4)->count(),
            'step_5_completion' => Driver::where('kyc_step', '>=', 5)->count()
        ];
    }

    /**
     * Get document upload statistics
     */
    private function getDocumentUploadStats(): array
    {
        return [
            'nin_documents' => Driver::whereNotNull('nin_document')->count(),
            'profile_pictures' => Driver::whereNotNull('profile_picture')->count(),
            'license_front' => Driver::whereNotNull('license_front_image')->count(),
            'license_back' => Driver::whereNotNull('license_back_image')->count(),
            'passport_photos' => Driver::whereNotNull('passport_photograph')->count()
        ];
    }

    /**
     * Calculate average profile completion
     */
    private function calculateAverageProfileCompletion(): float
    {
        $drivers = Driver::all();
        if ($drivers->isEmpty()) return 0;

        $totalCompletion = $drivers->sum(function ($driver) {
            return $driver->profile_completion_percentage ?? 0;
        });

        return round($totalCompletion / $drivers->count(), 2);
    }

    /**
     * Calculate verification completion rate
     */
    private function calculateVerificationCompletionRate(): float
    {
        $totalDrivers = Driver::count();
        $verifiedDrivers = Driver::where('verification_status', 'verified')->count();

        return $totalDrivers > 0 ? round(($verifiedDrivers / $totalDrivers) * 100, 2) : 0;
    }

    /**
     * Calculate document upload rate
     */
    private function calculateDocumentUploadRate(): float
    {
        $totalDrivers = Driver::count();
        if ($totalDrivers === 0) return 0;

        $driversWithDocuments = Driver::where(function ($query) {
            $query->whereNotNull('nin_document')
                  ->orWhereNotNull('profile_picture')
                  ->orWhereNotNull('license_front_image')
                  ->orWhereNotNull('license_back_image')
                  ->orWhereNotNull('passport_photograph');
        })->count();

        return round(($driversWithDocuments / $totalDrivers) * 100, 2);
    }

    /**
     * Get top performing drivers
     */
    private function getTopPerformingDrivers(int $limit): array
    {
        // This would integrate with performance metrics
        // For now, return drivers with highest profile completion
        return Driver::select(['id', 'driver_id', 'first_name', 'surname', 'profile_completion_percentage'])
                    ->orderBy('profile_completion_percentage', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function ($driver) {
                        return [
                            'driver_id' => $driver->driver_id,
                            'name' => $driver->full_name,
                            'score' => $driver->profile_completion_percentage ?? 0
                        ];
                    })
                    ->toArray();
    }

    /**
     * Get performance distribution
     */
    private function getPerformanceDistribution(): array
    {
        return [
            'excellent' => Driver::where('profile_completion_percentage', '>=', 90)->count(),
            'good' => Driver::whereBetween('profile_completion_percentage', [70, 89])->count(),
            'average' => Driver::whereBetween('profile_completion_percentage', [50, 69])->count(),
            'poor' => Driver::where('profile_completion_percentage', '<', 50)->count()
        ];
    }

    /**
     * Get gender distribution
     */
    private function getGenderDistribution(): array
    {
        return [
            'male' => Driver::where('gender', 'male')->count(),
            'female' => Driver::where('gender', 'female')->count(),
            'other' => Driver::where('gender', 'other')->count(),
            'not_specified' => Driver::whereNull('gender')->count()
        ];
    }

    /**
     * Get age distribution
     */
    private function getAgeDistribution(): array
    {
        $distribution = [
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '56+' => 0,
            'not_specified' => 0
        ];

        Driver::all()->each(function ($driver) use (&$distribution) {
            if (!$driver->date_of_birth) {
                $distribution['not_specified']++;
                return;
            }

            $age = $driver->date_of_birth->age;
            if ($age >= 18 && $age <= 25) {
                $distribution['18-25']++;
            } elseif ($age >= 26 && $age <= 35) {
                $distribution['26-35']++;
            } elseif ($age >= 36 && $age <= 45) {
                $distribution['36-45']++;
            } elseif ($age >= 46 && $age <= 55) {
                $distribution['46-55']++;
            } else {
                $distribution['56+']++;
            }
        });

        return $distribution;
    }

    /**
     * Get state distribution
     */
    private function getStateDistribution(): array
    {
        return Driver::selectRaw('state_of_origin, COUNT(*) as count')
                    ->whereNotNull('state_of_origin')
                    ->groupBy('state_of_origin')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->pluck('count', 'state_of_origin')
                    ->toArray();
    }

    /**
     * Get nationality distribution
     */
    private function getNationalityDistribution(): array
    {
        return Driver::selectRaw('nationality_id, COUNT(*) as count')
                    ->with('nationality:id,name')
                    ->whereNotNull('nationality_id')
                    ->groupBy('nationality_id')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->nationality->name ?? 'Unknown' => $item->count];
                    })
                    ->toArray();
    }

    /**
     * Get experience distribution
     */
    private function getExperienceDistribution(): array
    {
        return [
            '0-2_years' => Driver::whereBetween('experience_years', [0, 2])->count(),
            '3-5_years' => Driver::whereBetween('experience_years', [3, 5])->count(),
            '6-10_years' => Driver::whereBetween('experience_years', [6, 10])->count(),
            '10+_years' => Driver::where('experience_years', '>', 10)->count(),
            'not_specified' => Driver::whereNull('experience_years')->count()
        ];
    }

    /**
     * Get registration trends
     */
    private function getRegistrationTrends(): array
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Driver::whereYear('created_at', $date->year)
                          ->whereMonth('created_at', $date->month)
                          ->count();
            $trends[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        return $trends;
    }

    /**
     * Calculate retention rate for given days
     */
    private function calculateRetentionRate(int $days): float
    {
        $startDate = now()->subDays($days);
        $registeredInPeriod = Driver::where('created_at', '>=', $startDate)->count();
        $stillActive = Driver::where('created_at', '>=', $startDate)
                           ->where('status', 'active')
                           ->count();

        return $registeredInPeriod > 0 ? round(($stillActive / $registeredInPeriod) * 100, 2) : 0;
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate(string $period): float
    {
        $query = Driver::where('status', '!=', 'active');

        switch ($period) {
            case 'day':
                $query->whereDate('updated_at', today());
                break;
            case 'week':
                $query->where('updated_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->where('updated_at', '>=', now()->startOfMonth());
                break;
        }

        $churned = $query->count();
        $totalDrivers = Driver::count();

        return $totalDrivers > 0 ? round(($churned / $totalDrivers) * 100, 2) : 0;
    }

    /**
     * Get retention trends for last N months
     */
    private function getRetentionTrends(int $months): array
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $retentionRate = $this->calculateRetentionRate($date->daysInMonth * 30); // Approximate
            $trends[] = [
                'month' => $date->format('M Y'),
                'retention_rate' => $retentionRate
            ];
        }
        return $trends;
    }

    /**
     * Get feature usage statistics
     */
    private function getFeatureUsageStats(): array
    {
        // This would track actual feature usage
        // For now, return basic engagement metrics
        return [
            'profile_views' => 0, // Would need tracking
            'document_uploads' => Driver::whereNotNull('nin_document')->count(),
            'kyc_completions' => Driver::where('kyc_status', 'completed')->count(),
            'verification_requests' => Driver::where('verification_status', 'verified')->count()
        ];
    }

    /**
     * Get engagement score distribution
     */
    private function getEngagementScoreDistribution(): array
    {
        $drivers = Driver::all();
        $scores = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];

        $drivers->each(function ($driver) use (&$scores) {
            $score = ($driver->profile_completion_percentage ?? 0) +
                    ($driver->kyc_status === 'completed' ? 30 : 0) +
                    ($driver->verification_status === 'verified' ? 40 : 0);

            if ($score >= 80) {
                $scores['high']++;
            } elseif ($score >= 50) {
                $scores['medium']++;
            } else {
                $scores['low']++;
            }
        });

        return $scores;
    }
}

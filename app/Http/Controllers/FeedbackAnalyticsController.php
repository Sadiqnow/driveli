<?php

namespace App\Http\Controllers;

use App\Models\DriverCompanyRelation;
use App\Services\EmploymentFeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackAnalyticsController extends Controller
{
    protected $feedbackService;

    public function __construct(EmploymentFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Display the feedback analytics dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stats = $this->feedbackService->getFeedbackStats();
        $flaggedDrivers = $this->feedbackService->getFlaggedDrivers();

        // Get monthly trends for the last 12 months
        $monthlyTrends = $this->getMonthlyTrends();

        // Get performance rating distribution
        $ratingDistribution = $this->getRatingDistribution();

        // Get response time analysis
        $responseTimeAnalysis = $this->getResponseTimeAnalysis();

        return view('admin.feedback-analytics', compact(
            'stats',
            'flaggedDrivers',
            'monthlyTrends',
            'ratingDistribution',
            'responseTimeAnalysis'
        ));
    }

    /**
     * Get monthly feedback trends
     *
     * @return array
     */
    private function getMonthlyTrends(): array
    {
        $trends = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');

            $requested = DriverCompanyRelation::whereYear('feedback_requested_at', $date->year)
                ->whereMonth('feedback_requested_at', $date->month)
                ->count();

            $submitted = DriverCompanyRelation::whereYear('feedback_submitted_at', $date->year)
                ->whereMonth('feedback_submitted_at', $date->month)
                ->count();

            $flagged = DriverCompanyRelation::whereYear('feedback_submitted_at', $date->year)
                ->whereMonth('feedback_submitted_at', $date->month)
                ->where('is_flagged', true)
                ->count();

            $trends[] = [
                'month' => $monthName,
                'requested' => $requested,
                'submitted' => $submitted,
                'flagged' => $flagged,
                'response_rate' => $requested > 0 ? round(($submitted / $requested) * 100, 1) : 0
            ];
        }

        return $trends;
    }

    /**
     * Get performance rating distribution
     *
     * @return array
     */
    private function getRatingDistribution(): array
    {
        $ratings = DriverCompanyRelation::whereNotNull('performance_rating')
            ->select('performance_rating', DB::raw('count(*) as count'))
            ->groupBy('performance_rating')
            ->orderBy('performance_rating')
            ->get()
            ->pluck('count', 'performance_rating')
            ->toArray();

        // Ensure all ratings 1-5 are represented
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $ratings[$i] ?? 0;
        }

        return $distribution;
    }

    /**
     * Get response time analysis
     *
     * @return array
     */
    private function getResponseTimeAnalysis(): array
    {
        $responseTimes = DriverCompanyRelation::whereNotNull('feedback_submitted_at')
            ->selectRaw('DATEDIFF(feedback_submitted_at, feedback_requested_at) as response_days')
            ->get()
            ->pluck('response_days');

        if ($responseTimes->isEmpty()) {
            return [
                'average_days' => 0,
                'median_days' => 0,
                'fastest_days' => 0,
                'slowest_days' => 0,
                'within_7_days' => 0,
                'within_14_days' => 0,
                'within_30_days' => 0
            ];
        }

        $sorted = $responseTimes->sort();
        $count = $responseTimes->count();

        return [
            'average_days' => round($responseTimes->avg(), 1),
            'median_days' => $sorted->values()[$count / 2] ?? 0,
            'fastest_days' => $responseTimes->min(),
            'slowest_days' => $responseTimes->max(),
            'within_7_days' => $responseTimes->filter(fn($days) => $days <= 7)->count(),
            'within_14_days' => $responseTimes->filter(fn($days) => $days <= 14)->count(),
            'within_30_days' => $responseTimes->filter(fn($days) => $days <= 30)->count()
        ];
    }

    /**
     * Get feedback trends data for charts (API endpoint)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trends(Request $request)
    {
        $period = $request->get('period', '12months'); // 12months, 6months, 3months

        switch ($period) {
            case '3months':
                $months = 3;
                break;
            case '6months':
                $months = 6;
                break;
            default:
                $months = 12;
        }

        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');

            $data = [
                'month' => $monthName,
                'requested' => DriverCompanyRelation::whereYear('feedback_requested_at', $date->year)
                    ->whereMonth('feedback_requested_at', $date->month)->count(),
                'submitted' => DriverCompanyRelation::whereYear('feedback_submitted_at', $date->year)
                    ->whereMonth('feedback_submitted_at', $date->month)->count(),
                'flagged' => DriverCompanyRelation::whereYear('feedback_submitted_at', $date->year)
                    ->whereMonth('feedback_submitted_at', $date->month)
                    ->where('is_flagged', true)->count()
            ];

            $data['response_rate'] = $data['requested'] > 0 ?
                round(($data['submitted'] / $data['requested']) * 100, 1) : 0;

            $trends[] = $data;
        }

        return response()->json($trends);
    }

    /**
     * Get flagged drivers data (API endpoint)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function flaggedDrivers()
    {
        $flaggedDrivers = $this->feedbackService->getFlaggedDrivers()
            ->map(function ($relation) {
                return [
                    'id' => $relation->id,
                    'driver_name' => $relation->driver->full_name,
                    'company_name' => $relation->company->name,
                    'performance_rating' => $relation->performance_rating,
                    'reason_for_leaving' => $relation->reason_for_leaving,
                    'submitted_at' => $relation->feedback_submitted_at->format('Y-m-d'),
                    'days_since_submission' => $relation->feedback_submitted_at->diffInDays(now())
                ];
            });

        return response()->json($flaggedDrivers);
    }
}

<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DriverMatch;
use App\Models\DriverPerformance;
use App\Models\Commission;
use App\Models\Drivers;
use Carbon\Carbon;

class DriverDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:driver');
    }

    public function index()
    {
        $authDriver = Auth::guard('driver')->user();

        // Fetch fresh driver data from database to ensure verification status is up to date
        $driver = Drivers::find($authDriver->id);

        // Calculate profile completeness
        $profileCompleteness = $this->calculateProfileCompleteness($driver);

        // Get dashboard statistics
        $stats = $this->getDashboardStats($driver);

        // Get recent matches/activity
        $recentMatches = $this->getRecentMatches($driver);

        return view('driver.dashboard', compact(
            'driver',
            'profileCompleteness',
            'stats',
            'recentMatches'
        ));
    }

    private function calculateProfileCompleteness($driver)
    {
        $completeness = 0;
        $totalFields = 10; // Adjust based on required fields
        
        // Check basic fields
        if ($driver->first_name) $completeness++;
        if ($driver->surname) $completeness++;
        if ($driver->email) $completeness++;
        if ($driver->phone) $completeness++;
        if ($driver->date_of_birth) $completeness++;
        if ($driver->gender) $completeness++;
        if ($driver->residential_address) $completeness++;
        if ($driver->emergency_contact_name) $completeness++;
        if ($driver->emergency_contact_phone) $completeness++;
        if ($driver->verification_status === 'verified') $completeness++;
        
        return round(($completeness / $totalFields) * 100);
    }

    private function getDashboardStats($driver)
    {
        // Get performance data
        $performance = DriverPerformance::where('driver_id', $driver->id)->first();

        // Get match statistics
        $totalJobs = DriverMatch::where('driver_id', $driver->id)->count();
        $completedJobs = DriverMatch::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->count();

        // Calculate earnings from commissions
        $totalEarnings = Commission::where('driver_id', $driver->id)
            ->where('status', 'paid')
            ->sum('amount');

        // Jobs this month
        $jobsThisMonth = DriverMatch::where('driver_id', $driver->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Earnings this month
        $earningsThisMonth = Commission::where('driver_id', $driver->id)
            ->where('status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // Average rating from completed matches
        $averageRating = DriverMatch::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->whereNotNull('driver_rating')
            ->avg('driver_rating') ?? 5.0;

        return [
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'total_earnings' => $totalEarnings,
            'average_rating' => round($averageRating, 1),
            'jobs_this_month' => $jobsThisMonth,
            'earnings_this_month' => $earningsThisMonth,
        ];
    }

    private function getRecentMatches($driver)
    {
        return DriverMatch::with(['companyRequest.company'])
            ->where('driver_id', $driver->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($match) {
                return [
                    'id' => $match->id,
                    'match_id' => $match->match_id,
                    'status' => $match->status,
                    'company_name' => $match->companyRequest->company->name ?? 'Company',
                    'created_at' => $match->created_at,
                    'commission_amount' => $match->commission_amount,
                ];
            });
    }

    public function updateAvailability(Request $request)
    {
        $request->validate([
            'available' => 'required|boolean'
        ]);

        $authDriver = Auth::guard('driver')->user();
        $driver = Drivers::find($authDriver->id);
        $driver->available = $request->available;
        $driver->save();

        return response()->json([
            'success' => true,
            'message' => $request->available ? 'You are now available for jobs' : 'You are now unavailable for jobs'
        ]);
    }

    public function getNotifications()
    {
        // Return empty notifications for now
        return response()->json([
            'success' => true,
            'notifications' => []
        ]);
    }

    public function getStats()
    {
        $driver = Auth::guard('driver')->user();
        $stats = $this->getDashboardStats($driver);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function getActivity()
    {
        $driver = Auth::guard('driver')->user();
        $activities = $this->getRecentMatches($driver);

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }
}
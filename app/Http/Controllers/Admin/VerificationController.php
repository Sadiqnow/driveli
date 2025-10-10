<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverNormalized;
use App\Services\VerificationStatusService;
use App\Services\DriverVerificationWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerificationController extends Controller
{
    protected $verificationStatusService;
    protected $verificationWorkflow;

    public function __construct(
        VerificationStatusService $verificationStatusService,
        DriverVerificationWorkflow $verificationWorkflow
    ) {
        $this->verificationStatusService = $verificationStatusService;
        $this->verificationWorkflow = $verificationWorkflow;
    }

    public function dashboard(Request $request)
    {
        // Get date range from request or default to last 30 days
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        // Get verification statistics
        $statistics = $this->verificationStatusService->getVerificationStatistics($dateRange);

        // Get pending manual reviews
        $pendingReviews = DriverNormalized::where('verification_status', 'requires_manual_review')
            ->with(['driverMatches', 'driverPerformances'])
            ->orderBy('verification_started_at', 'desc')
            ->take(10)
            ->get();

        // Get recent verification activities
        $recentActivities = DB::table('driver_verifications')
            ->join('drivers', 'driver_verifications.driver_id', '=', 'drivers.id')
            ->select([
                'driver_verifications.*',
                'drivers.first_name',
                'drivers.last_name',
                'drivers.email'
            ])
            ->orderBy('driver_verifications.created_at', 'desc')
            ->take(20)
            ->get();

        // Get failed verifications
        $failedVerifications = DriverNormalized::where('verification_status', 'failed')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.verification.dashboard', compact(
            'statistics',
            'pendingReviews',
            'recentActivities',
            'failedVerifications',
            'dateRange'
        ));
    }

    public function driverDetails($driverId)
    {
        $verificationDetails = $this->verificationStatusService->getDriverVerificationDetails($driverId);

        if (!$verificationDetails['success']) {
            return redirect()->back()->with('error', 'Failed to load driver verification details');
        }

        return view('admin.verification.driver-details', $verificationDetails);
    }

    public function approveVerification(Request $request, $driverId)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'override_score' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            DB::beginTransaction();

            $driver = DriverNormalized::findOrFail($driverId);
            
            // Override score if provided
            $finalScore = $request->input('override_score', $driver->overall_verification_score);
            
            // Update verification status
            $verificationData = [
                'manual_review' => [
                    'status' => 'verified',
                    'score' => $finalScore,
                    'verified_at' => now(),
                    'verified_by' => auth()->user()->name ?? 'Admin',
                    'notes' => $request->input('notes')
                ]
            ];

            $result = $this->verificationStatusService->updateDriverVerificationStatus($driverId, $verificationData);

            // Log the manual approval
            DB::table('driver_verifications')->insert([
                'driver_id' => $driverId,
                'verification_type' => 'manual_approval',
                'status' => 'completed',
                'verification_score' => $finalScore,
                'verification_data' => json_encode($verificationData),
                'notes' => $request->input('notes'),
                'verified_by' => auth()->user()->name ?? 'Admin',
                'verified_at' => now(),
                'attempt_count' => 1,
                'last_attempt_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Driver verification approved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve verification: ' . $e->getMessage());
        }
    }

    public function rejectVerification(Request $request, $driverId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $driver = DriverNormalized::findOrFail($driverId);
            
            // Update verification status to failed
            $verificationData = [
                'manual_review' => [
                    'status' => 'failed',
                    'score' => 0,
                    'verified_at' => now(),
                    'verified_by' => auth()->user()->name ?? 'Admin',
                    'rejection_reason' => $request->input('rejection_reason')
                ]
            ];

            $result = $this->verificationStatusService->updateDriverVerificationStatus($driverId, $verificationData);

            // Log the manual rejection
            DB::table('driver_verifications')->insert([
                'driver_id' => $driverId,
                'verification_type' => 'manual_rejection',
                'status' => 'completed',
                'verification_score' => 0,
                'verification_data' => json_encode($verificationData),
                'notes' => $request->input('rejection_reason'),
                'verified_by' => auth()->user()->name ?? 'Admin',
                'verified_at' => now(),
                'attempt_count' => 1,
                'last_attempt_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Driver verification rejected');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject verification: ' . $e->getMessage());
        }
    }

    public function retryVerification($driverId)
    {
        $result = $this->verificationStatusService->retryFailedVerification($driverId);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Verification retry initiated');
        } else {
            return redirect()->back()->with('error', $result['error']);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'exists:drivers,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $successCount = 0;
        $failureCount = 0;

        foreach ($request->input('driver_ids') as $driverId) {
            try {
                $verificationData = [
                    'manual_review' => [
                        'status' => 'verified',
                        'score' => 85, // Default bulk approval score
                        'verified_at' => now(),
                        'verified_by' => auth()->user()->name ?? 'Admin',
                        'notes' => $request->input('notes') ?? 'Bulk approved'
                    ]
                ];

                $result = $this->verificationStatusService->updateDriverVerificationStatus($driverId, $verificationData);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }

            } catch (\Exception $e) {
                $failureCount++;
            }
        }

        $message = "Bulk approval completed: {$successCount} approved, {$failureCount} failed";
        return redirect()->back()->with('success', $message);
    }

    public function verificationReport(Request $request)
    {
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        // Get comprehensive verification statistics
        $statistics = $this->verificationStatusService->getVerificationStatistics($dateRange);

        // Get detailed verification breakdown by component
        $componentStats = DB::table('driver_verifications')
            ->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ])
            ->select([
                'verification_type',
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(verification_score) as avg_score')
            ])
            ->groupBy('verification_type', 'status')
            ->get();

        // Get API performance metrics
        $apiStats = DB::table('api_verification_logs')
            ->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ])
            ->select([
                'api_provider',
                'verification_type',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(is_successful) as successful_requests'),
                DB::raw('AVG(response_time_ms) as avg_response_time')
            ])
            ->groupBy('api_provider', 'verification_type')
            ->get();

        return view('admin.verification.report', compact(
            'statistics',
            'componentStats',
            'apiStats',
            'dateRange'
        ));
    }

    public function downloadReport(Request $request)
    {
        // Implementation for downloading verification reports as CSV/PDF
        // This would generate and download a detailed verification report
        return response()->json(['message' => 'Report download feature to be implemented']);
    }

    public function getVerificationStats(Request $request)
    {
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(7)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        $stats = $this->verificationStatusService->getVerificationStatistics($dateRange);

        return response()->json($stats);
    }
}
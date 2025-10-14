<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeactivationRequest;
use App\Models\OtpVerification;
use App\Models\ActivityLog;
use App\Models\Drivers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LogDashboardController extends Controller
{
    /**
     * Display the log dashboard with deactivation requests, verification statuses, and OTP verifications.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Deactivation Requests Statistics
        $deactivationStats = [
            'total' => DeactivationRequest::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending' => DeactivationRequest::where('status', 'pending')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'approved' => DeactivationRequest::where('status', 'approved')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'rejected' => DeactivationRequest::where('status', 'rejected')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'by_type' => DeactivationRequest::select('user_type', DB::raw('count(*) as count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('user_type')
                ->get()
                ->pluck('count', 'user_type')
                ->toArray()
        ];

        // Verification Status Statistics
        $verificationStats = [
            'total_verified' => Drivers::where('verification_status', 'verified')->count(),
            'total_pending' => Drivers::where('verification_status', 'pending')->count(),
            'total_rejected' => Drivers::where('verification_status', 'rejected')->count(),
            'recent_verifications' => Drivers::where('verification_status', 'verified')
                ->whereBetween('verified_at', [$startDate, $endDate])
                ->count(),
            'verification_trends' => Drivers::select(
                    DB::raw('DATE(verified_at) as date'),
                    DB::raw('count(*) as count')
                )
                ->where('verification_status', 'verified')
                ->whereBetween('verified_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        // OTP Verification Statistics
        $otpStats = [
            'total_sent' => OtpVerification::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_verified' => OtpVerification::where('is_verified', true)
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_failed' => OtpVerification::where('is_verified', false)
                ->where('attempts', '>=', 3)
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'by_type' => OtpVerification::select('type', DB::raw('count(*) as count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
                ->toArray(),
            'success_rate' => $this->calculateOtpSuccessRate($startDate, $endDate)
        ];

        // Recent Activity Logs
        $recentLogs = ActivityLog::with(['user', 'subject'])
            ->whereIn('action', [
                'deactivation_requested',
                'deactivation_approved',
                'deactivation_rejected',
                'otp_requested',
                'otp_verified',
                'otp_failed',
                'verification_completed',
                'verification_failed'
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Recent Deactivation Requests
        $recentDeactivations = DeactivationRequest::with(['requester', 'approver'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Recent OTP Verifications
        $recentOtps = OtpVerification::with('driver')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.logs.dashboard', compact(
            'deactivationStats',
            'verificationStats',
            'otpStats',
            'recentLogs',
            'recentDeactivations',
            'recentOtps',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Calculate OTP success rate for the given date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    private function calculateOtpSuccessRate($startDate, $endDate)
    {
        $totalSent = OtpVerification::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalVerified = OtpVerification::where('is_verified', true)
            ->whereBetween('created_at', [$startDate, $endDate])->count();

        return $totalSent > 0 ? round(($totalVerified / $totalSent) * 100, 2) : 0;
    }

    /**
     * Get detailed statistics for a specific date range.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $stats = [
            'deactivation' => [
                'total' => DeactivationRequest::whereBetween('created_at', [$startDate, $endDate])->count(),
                'pending' => DeactivationRequest::where('status', 'pending')
                    ->whereBetween('created_at', [$startDate, $endDate])->count(),
                'approved' => DeactivationRequest::where('status', 'approved')
                    ->whereBetween('created_at', [$startDate, $endDate])->count(),
                'rejected' => DeactivationRequest::where('status', 'rejected')
                    ->whereBetween('created_at', [$startDate, $endDate])->count(),
            ],
            'verification' => [
                'verified' => Drivers::where('verification_status', 'verified')
                    ->whereBetween('verified_at', [$startDate, $endDate])->count(),
                'pending' => Drivers::where('verification_status', 'pending')->count(),
                'rejected' => Drivers::where('verification_status', 'rejected')->count(),
            ],
            'otp' => [
                'sent' => OtpVerification::whereBetween('created_at', [$startDate, $endDate])->count(),
                'verified' => OtpVerification::where('is_verified', true)
                    ->whereBetween('created_at', [$startDate, $endDate])->count(),
                'failed' => OtpVerification::where('is_verified', false)
                    ->where('attempts', '>=', 3)
                    ->whereBetween('created_at', [$startDate, $endDate])->count(),
                'success_rate' => $this->calculateOtpSuccessRate($startDate, $endDate)
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Export log data to CSV.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $type = $request->get('type', 'all'); // deactivation, verification, otp, all

        $filename = "logs_export_{$type}_{$startDate}_to_{$endDate}.csv";

        return response()->stream(function () use ($startDate, $endDate, $type) {
            $handle = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($handle, ['Date', 'Type', 'Action', 'User', 'Details', 'Status']);

            $query = ActivityLog::with(['user', 'subject'])
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($type !== 'all') {
                $actions = [];
                switch ($type) {
                    case 'deactivation':
                        $actions = ['deactivation_requested', 'deactivation_approved', 'deactivation_rejected'];
                        break;
                    case 'verification':
                        $actions = ['verification_completed', 'verification_failed'];
                        break;
                    case 'otp':
                        $actions = ['otp_requested', 'otp_verified', 'otp_failed'];
                        break;
                }
                $query->whereIn('action', $actions);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    ucfirst($type),
                    $log->action,
                    $log->user ? $log->user->name : 'System',
                    $log->description,
                    $log->metadata['status'] ?? 'N/A'
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

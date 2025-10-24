<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VerificationStatusService;
use App\Services\DriverVerificationWorkflow;
use App\Services\VerificationActionService;
use App\Services\VerificationDataService;
use App\Services\VerificationReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VerificationController extends Controller
{
    protected $verificationStatusService;
    protected $verificationWorkflow;
    protected $actionService;
    protected $dataService;
    protected $reportService;

    public function __construct(
        VerificationStatusService $verificationStatusService,
        DriverVerificationWorkflow $verificationWorkflow,
        VerificationActionService $actionService,
        VerificationDataService $dataService,
        VerificationReportService $reportService
    ) {
        $this->verificationStatusService = $verificationStatusService;
        $this->verificationWorkflow = $verificationWorkflow;
        $this->actionService = $actionService;
        $this->dataService = $dataService;
        $this->reportService = $reportService;
    }

    public function dashboard(Request $request)
    {
        // Check if user has permission to manage verification
        if (!auth('admin')->user()->hasPermission('manage_verification')) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        // Get date range from request or default to last 30 days
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        // Get verification statistics
        $statistics = $this->verificationStatusService->getVerificationStatistics($dateRange);

        // Get dashboard data using service
        $dashboardData = $this->dataService->getDashboardData($dateRange);

        return view('admin.verification.dashboard', array_merge(
            compact('statistics', 'dateRange'),
            $dashboardData
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

        $result = $this->actionService->approveVerification($driverId, $request->only(['notes', 'override_score']));

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function rejectVerification(Request $request, $driverId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $result = $this->actionService->rejectVerification($driverId, $request->only('rejection_reason'));

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function retryVerification($driverId)
    {
        $result = $this->actionService->retryVerification($driverId);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'exists:drivers,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $result = $this->actionService->bulkApproveVerifications(
            $request->input('driver_ids'),
            $request->input('notes')
        );

        return redirect()->back()->with('success', $result['message']);
    }

    public function verificationReport(Request $request)
    {
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        // Get comprehensive verification statistics
        $statistics = $this->verificationStatusService->getVerificationStatistics($dateRange);

        // Get report data using service
        $reportData = $this->dataService->getReportData($dateRange);

        return view('admin.verification.report', array_merge(
            compact('statistics', 'dateRange'),
            $reportData
        ));
    }

    public function downloadReport(Request $request)
    {
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        return $this->reportService->downloadCsvReport($dateRange);
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
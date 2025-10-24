<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CompanyVerificationService;
use App\Services\CompanyVerificationActionService;
use App\Services\CompanyVerificationDataService;
use App\Services\CompanyVerificationReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CompanyVerificationController extends Controller
{
    protected $verificationService;
    protected $actionService;
    protected $dataService;
    protected $reportService;

    public function __construct(
        CompanyVerificationService $verificationService,
        CompanyVerificationActionService $actionService,
        CompanyVerificationDataService $dataService,
        CompanyVerificationReportService $reportService
    ) {
        $this->verificationService = $verificationService;
        $this->actionService = $actionService;
        $this->dataService = $dataService;
        $this->reportService = $reportService;
    }

    /**
     * Display company verification queue
     */
    public function index(Request $request)
    {
        // Check permissions
        if (!auth('admin')->user()->hasPermission('manage_company_verifications')) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        // Get date range from request or default to last 30 days
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        // Get verification statistics
        $statistics = $this->verificationService->getVerificationStatistics($dateRange);

        // Get queue data using service
        $queueData = $this->dataService->getQueueData($dateRange);

        return view('admin.verification.company-queue', array_merge(
            compact('statistics', 'dateRange'),
            $queueData
        ));
    }

    /**
     * Show company verification details
     */
    public function show($id)
    {
        $verificationDetails = $this->dataService->getVerificationDetails($id);

        return view('admin.verification.company-detail', $verificationDetails);
    }

    /**
     * Approve company verification
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        $result = $this->actionService->approveVerification($id, $request->only('notes'));

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Reject company verification
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $result = $this->actionService->rejectVerification($id, $request->only('rejection_reason'));

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Move to under review status
     */
    public function underReview($id)
    {
        $result = $this->actionService->moveToUnderReview($id);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Bulk approve verifications
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'verification_ids' => 'required|array',
            'verification_ids.*' => 'exists:company_verifications,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $result = $this->actionService->bulkApproveVerifications(
            $request->input('verification_ids'),
            $request->input('notes')
        );

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Get verification statistics
     */
    public function stats(Request $request)
    {
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(7)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        $stats = $this->verificationService->getVerificationStatistics($dateRange);

        return response()->json($stats);
    }

    /**
     * Download verification report
     */
    public function downloadReport(Request $request)
    {
        $dateRange = [
            'start' => $request->input('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->input('end_date', Carbon::now()->toDateString())
        ];

        return $this->reportService->downloadCsvReport($dateRange);
    }
}

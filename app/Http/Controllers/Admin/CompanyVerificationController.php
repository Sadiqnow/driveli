<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyVerification;
use App\Services\CompanyVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanyVerificationController extends Controller
{
    protected $verificationService;

    public function __construct(CompanyVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
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

        // Get pending verifications
        $pendingVerifications = CompanyVerification::with('company')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get recent verification activities
        $recentActivities = CompanyVerification::with('company', 'verifiedBy')
            ->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.verification.company-queue', compact(
            'statistics',
            'pendingVerifications',
            'recentActivities',
            'dateRange'
        ));
    }

    /**
     * Show company verification details
     */
    public function show($id)
    {
        $verification = CompanyVerification::with('company', 'verifiedBy')->findOrFail($id);

        // Get verification history
        $history = CompanyVerification::where('company_id', $verification->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get OCR results if available
        $ocrResults = DB::table('company_ocr_results')
            ->where('company_id', $verification->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.verification.company-detail', compact(
            'verification',
            'history',
            'ocrResults'
        ));
    }

    /**
     * Approve company verification
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $verification = CompanyVerification::findOrFail($id);
            $verification->update([
                'status' => 'approved',
                'verified_at' => now(),
                'verified_by' => auth('admin')->id(),
                'notes' => $request->input('notes')
            ]);

            // Update company status
            $verification->company->update(['verification_status' => 'verified']);

            // Log the approval
            $this->verificationService->logVerificationAction($verification, 'approved', $request->input('notes'));

            DB::commit();

            return redirect()->back()->with('success', 'Company verification approved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve verification: ' . $e->getMessage());
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

        try {
            DB::beginTransaction();

            $verification = CompanyVerification::findOrFail($id);
            $verification->update([
                'status' => 'rejected',
                'verified_at' => now(),
                'verified_by' => auth('admin')->id(),
                'rejection_reason' => $request->input('rejection_reason')
            ]);

            // Update company status
            $verification->company->update(['verification_status' => 'rejected']);

            // Log the rejection
            $this->verificationService->logVerificationAction($verification, 'rejected', $request->input('rejection_reason'));

            DB::commit();

            return redirect()->back()->with('success', 'Company verification rejected');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject verification: ' . $e->getMessage());
        }
    }

    /**
     * Move to under review status
     */
    public function underReview($id)
    {
        try {
            $verification = CompanyVerification::findOrFail($id);
            $verification->update([
                'status' => 'under_review',
                'verified_by' => auth('admin')->id()
            ]);

            $this->verificationService->logVerificationAction($verification, 'under_review', 'Moved to under review');

            return redirect()->back()->with('success', 'Verification moved to under review');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update verification status: ' . $e->getMessage());
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

        $successCount = 0;
        $failureCount = 0;

        foreach ($request->input('verification_ids') as $verificationId) {
            try {
                $verification = CompanyVerification::findOrFail($verificationId);
                $verification->update([
                    'status' => 'approved',
                    'verified_at' => now(),
                    'verified_by' => auth('admin')->id(),
                    'notes' => $request->input('notes') ?? 'Bulk approved'
                ]);

                $verification->company->update(['verification_status' => 'verified']);
                $successCount++;

            } catch (\Exception $e) {
                $failureCount++;
            }
        }

        $message = "Bulk approval completed: {$successCount} approved, {$failureCount} failed";
        return redirect()->back()->with('success', $message);
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

        // Generate CSV report
        $verifications = CompanyVerification::with('company', 'verifiedBy')
            ->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ])
            ->get();

        $filename = 'company_verification_report_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];

        $callback = function() use ($verifications) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Company Name',
                'Verification Type',
                'Status',
                'Submitted Date',
                'Verified Date',
                'Verified By',
                'Notes'
            ]);

            // CSV data
            foreach ($verifications as $verification) {
                fputcsv($file, [
                    $verification->company->name ?? 'N/A',
                    $verification->verification_type,
                    $verification->status,
                    $verification->created_at->format('Y-m-d H:i:s'),
                    $verification->verified_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $verification->verifiedBy->name ?? 'N/A',
                    $verification->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

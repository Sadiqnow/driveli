<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeactivationRequest;
use App\Models\OtpNotification;
use App\Services\DeactivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeactivationController extends Controller
{
    protected $deactivationService;

    public function __construct(DeactivationService $deactivationService)
    {
        $this->deactivationService = $deactivationService;
    }

    /**
     * Display deactivation dashboard
     */
    public function index(Request $request)
    {
        // Check permissions
        Gate::authorize('manage-deactivations');

        $stats = $this->deactivationService->getDeactivationStats();
        $pendingRequests = $this->deactivationService->getPendingRequests();

        return view('admin.deactivation.index', compact('stats', 'pendingRequests'));
    }

    /**
     * Show create deactivation request form
     */
    public function create(Request $request)
    {
        Gate::authorize('manage-deactivations');

        $userType = $request->get('user_type', 'driver');

        return view('admin.deactivation.create', compact('userType'));
    }

    /**
     * Store a new deactivation request
     */
    public function store(Request $request)
    {
        Gate::authorize('manage-deactivations');

        $request->validate([
            'user_type' => 'required|in:driver,company',
            'user_id' => 'required|integer',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            if ($request->user_type === 'driver') {
                $deactivationRequest = $this->deactivationService->createDriverDeactivationRequest(
                    $request->user_id,
                    $request->reason,
                    auth()->user()
                );
            } else {
                $deactivationRequest = $this->deactivationService->createCompanyDeactivationRequest(
                    $request->user_id,
                    $request->reason,
                    auth()->user()
                );
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deactivation request created successfully',
                    'request_id' => $deactivationRequest->id,
                ]);
            }

            return redirect()->route('admin.deactivation.show', $deactivationRequest)
                ->with('success', 'Deactivation request created successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create deactivation request: ' . $e->getMessage(),
                ], 400);
            }

            return redirect()->back()
                ->with('error', 'Failed to create deactivation request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show specific deactivation request
     */
    public function show(DeactivationRequest $deactivationRequest)
    {
        Gate::authorize('manage-deactivations');

        $deactivationRequest->load(['requester', 'approver', 'user']);

        return view('admin.deactivation.show', compact('deactivationRequest'));
    }

    /**
     * Admin-II review action
     */
    public function review(Request $request, DeactivationRequest $deactivationRequest)
    {
        Gate::authorize('review-deactivations');

        try {
            $this->deactivationService->adminIIReview($deactivationRequest->id, auth()->user());

            return redirect()->back()->with('success', 'Request sent to Admin-I for approval');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process review: ' . $e->getMessage());
        }
    }

    /**
     * Admin-I approve action
     */
    public function approve(Request $request, DeactivationRequest $deactivationRequest)
    {
        Gate::authorize('approve-deactivations');

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->deactivationService->adminIApprove(
                $deactivationRequest->id,
                auth()->user(),
                $request->notes
            );

            return redirect()->route('admin.deactivation.otp', $result['otp'])
                ->with('success', 'Request approved. OTP sent for final confirmation.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    /**
     * Reject deactivation request
     */
    public function reject(Request $request, DeactivationRequest $deactivationRequest)
    {
        Gate::authorize('approve-deactivations');

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $deactivationRequest->reject(auth()->user(), $request->rejection_reason);

            return redirect()->back()->with('success', 'Deactivation request rejected');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    /**
     * Show OTP verification form
     */
    public function showOTP(OtpNotification $otp)
    {
        Gate::authorize('manage-deactivations');

        if (!$otp->isExpired() && $otp->status === 'sent') {
            return view('admin.deactivation.verify-otp', compact('otp'));
        }

        return redirect()->route('admin.deactivation.index')
            ->with('error', 'OTP has expired or is invalid');
    }

    /**
     * Verify OTP and complete deactivation
     */
    public function verifyOTP(Request $request, OtpNotification $otp)
    {
        Gate::authorize('manage-deactivations');

        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        try {
            $this->deactivationService->verifyOTPAndDeactivate(
                $otp->id,
                $request->otp_code,
                $request->ip(),
                $request->userAgent()
            );

            return redirect()->route('admin.deactivation.index')
                ->with('success', 'Deactivation completed successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to verify OTP: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Resend OTP
     */
    public function resendOTP(OtpNotification $otp)
    {
        Gate::authorize('manage-deactivations');

        try {
            // Generate new OTP
            $newOtp = $this->deactivationService->generateOTP(
                $otp->user_type,
                $otp->user_id,
                $otp->type
            );

            return redirect()->route('admin.deactivation.otp', $newOtp)
                ->with('success', 'New OTP sent');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to resend OTP: ' . $e->getMessage());
        }
    }

    /**
     * Send OTP challenge for monitoring
     */
    public function sendChallenge(Request $request)
    {
        Gate::authorize('monitor-drivers');

        $request->validate([
            'user_type' => 'required|in:driver,company',
            'user_id' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $otp = $this->deactivationService->sendOTPChallenge(
                $request->user_type,
                $request->user_id,
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP challenge sent',
                'otp_id' => $otp->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API endpoint for driver-initiated deactivation
     */
    public function driverRequestDeactivation(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $deactivationRequest = $this->deactivationService->createDriverDeactivationRequest(
                auth()->id(),
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Deactivation request submitted',
                'request_id' => $deactivationRequest->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get deactivation statistics (API)
     */
    public function getStats()
    {
        Gate::authorize('manage-deactivations');

        $stats = $this->deactivationService->getDeactivationStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

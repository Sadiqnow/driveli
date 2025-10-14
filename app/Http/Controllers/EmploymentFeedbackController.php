<?php

namespace App\Http\Controllers;

use App\Services\EmploymentFeedbackService;
use App\Http\Requests\StoreEmploymentFeedbackRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmploymentFeedbackController extends Controller
{
    private EmploymentFeedbackService $feedbackService;

    public function __construct(EmploymentFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Show the feedback form for employers
     */
    public function showForm(Request $request, string $token): View
    {
        $relation = $this->feedbackService->validateToken($token);

        if (!$relation) {
            abort(404, 'Invalid or expired feedback request.');
        }

        return view('employment-feedback.form', [
            'relation' => $relation,
            'token' => $token,
        ]);
    }

    /**
     * Submit employment feedback
     */
    public function submitFeedback(StoreEmploymentFeedbackRequest $request, string $token): RedirectResponse
    {
        $relation = $this->feedbackService->submitFeedback($token, $request->validated());

        if (!$relation) {
            return back()->withErrors(['token' => 'Invalid or expired feedback request.']);
        }

        return redirect()->route('employment-feedback.success')
                        ->with('success', 'Thank you for providing employment feedback. Your response has been recorded.');
    }

    /**
     * Show success page after feedback submission
     */
    public function showSuccess(): View
    {
        return view('employment-feedback.success');
    }

    /**
     * API endpoint to request feedback (Admin only)
     */
    public function requestFeedback(Request $request): JsonResponse
    {
        $request->validate([
            'relation_id' => 'required|exists:driver_company_relations,id',
        ]);

        $relation = \App\Models\DriverCompanyRelation::findOrFail($request->relation_id);
        $admin = auth()->user();

        if (!$admin instanceof \App\Models\AdminUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $success = $this->feedbackService->requestFeedback($relation, $admin);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback request sent successfully',
                'data' => [
                    'relation_id' => $relation->id,
                    'requested_at' => $relation->feedback_requested_at,
                ]
            ]);
        }

        return response()->json(['error' => 'Failed to send feedback request'], 500);
    }

    /**
     * API endpoint for bulk feedback requests (Admin only)
     */
    public function bulkRequestFeedback(Request $request): JsonResponse
    {
        $request->validate([
            'relation_ids' => 'required|array|min:1',
            'relation_ids.*' => 'exists:driver_company_relations,id',
        ]);

        $admin = auth()->user();

        if (!$admin instanceof \App\Models\AdminUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $results = $this->feedbackService->bulkRequestFeedback($request->relation_ids, $admin);

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => "Processed {$results['successful']} successful, {$results['failed']} failed requests"
        ]);
    }

    /**
     * Get feedback statistics (Admin only)
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->feedbackService->getFeedbackStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get flagged drivers list (Admin only)
     */
    public function getFlaggedDrivers(): JsonResponse
    {
        $flaggedDrivers = $this->feedbackService->getFlaggedDrivers();

        return response()->json([
            'success' => true,
            'data' => $flaggedDrivers
        ]);
    }
}

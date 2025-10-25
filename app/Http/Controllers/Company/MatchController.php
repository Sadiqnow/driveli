<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyMatch;
use App\Services\MatchingService;

class MatchController extends Controller
{
    protected $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = Auth::guard('company')->user();

        $query = CompanyMatch::where('company_id', $company->id)
            ->with(['companyRequest', 'driver']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $matches = $query->latest()->paginate(15);

        // Get stats for the matches page
        $stats = [
            'total' => CompanyMatch::where('company_id', $company->id)->count(),
            'pending' => CompanyMatch::where('company_id', $company->id)->where('status', 'pending')->count(),
            'accepted' => CompanyMatch::where('company_id', $company->id)->where('status', 'accepted')->count(),
            'rejected' => CompanyMatch::where('company_id', $company->id)->where('status', 'rejected')->count(),
        ];

        return view('company.matches', compact('matches', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyMatch $match)
    {
        $this->authorize('view', $match);

        $match->load(['companyRequest', 'driver', 'invoices']);

        return view('company.matches.show', compact('match'));
    }

    /**
     * Accept a match
     */
    public function accept(Request $request, CompanyMatch $match)
    {
        $this->authorize('update', $match);

        if ($match->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Match is not in pending status'
            ], 400);
        }

        $request->validate([
            'agreed_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $this->matchingService->acceptMatch($match, [
                'agreed_rate' => $request->agreed_rate,
                'notes' => $request->notes
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Match accepted successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Match accepted successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to accept match'
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Failed to accept match']);
        }
    }

    /**
     * Reject a match
     */
    public function reject(Request $request, CompanyMatch $match)
    {
        $this->authorize('update', $match);

        if ($match->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Match is not in pending status'
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $this->matchingService->rejectMatch($match, [
                'reason' => $request->reason,
                'notes' => $request->notes
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Match rejected successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Match rejected successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject match'
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Failed to reject match']);
        }
    }

    /**
     * Negotiate a match
     */
    public function negotiate(Request $request, CompanyMatch $match)
    {
        $this->authorize('update', $match);

        if ($match->status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => 'Match must be accepted before negotiation'
            ], 400);
        }

        $request->validate([
            'proposed_rate' => 'required|numeric|min:0',
            'message' => 'required|string|max:1000'
        ]);

        try {
            $this->matchingService->negotiateMatch($match, [
                'proposed_rate' => $request->proposed_rate,
                'message' => $request->message
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Negotiation request sent successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Negotiation request sent successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send negotiation request'
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Failed to send negotiation request']);
        }
    }
}

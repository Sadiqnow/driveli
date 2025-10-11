<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\DriverMatch;
use App\Models\Drivers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:company');
    }

    /**
     * Show the company dashboard
     */
    public function index()
    {
        $company = Auth::guard('company')->user();

        // Get dashboard statistics
        $stats = $this->getDashboardStats($company);

        // Get recent requests
        $recentRequests = $company->requests()
            ->with(['matches' => function($query) {
                $query->latest()->take(1);
            }])
            ->latest()
            ->take(5)
            ->get();

        // Get recent matches
        $recentMatches = DriverMatch::whereHas('request', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->with(['request', 'driver'])
        ->latest()
        ->take(5)
        ->get();

        // Get available drivers count (for matching interface)
        $availableDriversCount = Drivers::where('status', 'Active')
            ->where('verification_status', 'Verified')
            ->count();

        return view('company.dashboard', compact(
            'company',
            'stats',
            'recentRequests',
            'recentMatches',
            'availableDriversCount'
        ));
    }

    /**
     * Get dashboard statistics for the company
     */
    private function getDashboardStats(Company $company)
    {
        // Total requests
        $totalRequests = $company->requests()->count();

        // Active requests
        $activeRequests = $company->requests()
            ->whereIn('status', ['active', 'pending'])
            ->count();

        // Completed requests
        $completedRequests = $company->requests()
            ->where('status', 'completed')
            ->count();

        // Total matches
        $totalMatches = DriverMatch::whereHas('request', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->count();

        // Successful matches (completed jobs)
        $successfulMatches = DriverMatch::whereHas('request', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->where('status', 'completed')
        ->count();

        // Fulfillment rate
        $fulfillmentRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 1) : 0;

        // Match success rate
        $matchSuccessRate = $totalMatches > 0 ? round(($successfulMatches / $totalMatches) * 100, 1) : 0;

        // Average rating (from completed matches)
        $averageRating = DriverMatch::whereHas('request', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->where('status', 'completed')
        ->whereNotNull('company_rating')
        ->avg('company_rating') ?? 0;

        $averageRating = round($averageRating, 1);

        return [
            'total_requests' => $totalRequests,
            'active_requests' => $activeRequests,
            'completed_requests' => $completedRequests,
            'total_matches' => $totalMatches,
            'successful_matches' => $successfulMatches,
            'fulfillment_rate' => $fulfillmentRate,
            'match_success_rate' => $matchSuccessRate,
            'average_rating' => $averageRating,
        ];
    }

    /**
     * Show company requests
     */
    public function requestsIndex(Request $request)
    {
        $company = Auth::guard('company')->user();

        $query = $company->requests()->with(['matches.driver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by position title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('position_title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest()->paginate(10);

        return view('company.requests.index', compact('requests'));
    }

    /**
     * Show create request form
     */
    public function createRequest()
    {
        return view('company.requests.create');
    }

    /**
     * Store new request
     */
    public function storeRequest(Request $request)
    {
        $request->validate([
            'position_title' => 'required|string|max:255',
            'request_type' => 'required|in:full_time,part_time,contract,temporary',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'requirements' => 'required|array',
            'requirements.*' => 'string|max:255',
            'salary_range' => 'nullable|string|max:100',
            'drivers_needed' => 'required|integer|min:1|max:50',
            'urgency' => 'required|in:low,medium,high,critical',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $company = Auth::guard('company')->user();

        // Generate request ID
        $requestId = $this->generateRequestId();

        CompanyRequest::create([
            'company_id' => $company->id,
            'request_id' => $requestId,
            'position_title' => $request->position_title,
            'request_type' => $request->request_type,
            'description' => $request->description,
            'location' => $request->location,
            'requirements' => $request->requirements,
            'salary_range' => $request->salary_range,
            'status' => 'pending',
            'priority' => $this->mapUrgencyToPriority($request->urgency),
            'created_by' => $company->id, // Company creates their own requests
            'expires_at' => $request->expires_at ?? now()->addDays(30),
        ]);

        return redirect()->route('company.requests.index')
            ->with('success', 'Request created successfully and is pending admin approval.');
    }

    /**
     * Show edit request form
     */
    public function editRequest(CompanyRequest $request)
    {
        $this->authorize('update', $request);

        return view('company.requests.edit', compact('request'));
    }

    /**
     * Update request
     */
    public function updateRequest(Request $request, CompanyRequest $companyRequest)
    {
        $this->authorize('update', $companyRequest);

        $request->validate([
            'position_title' => 'required|string|max:255',
            'request_type' => 'required|in:full_time,part_time,contract,temporary',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'requirements' => 'required|array',
            'requirements.*' => 'string|max:255',
            'salary_range' => 'nullable|string|max:100',
            'drivers_needed' => 'required|integer|min:1|max:50',
            'urgency' => 'required|in:low,medium,high,critical',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $companyRequest->update([
            'position_title' => $request->position_title,
            'request_type' => $request->request_type,
            'description' => $request->description,
            'location' => $request->location,
            'requirements' => $request->requirements,
            'salary_range' => $request->salary_range,
            'priority' => $this->mapUrgencyToPriority($request->urgency),
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('company.requests.index')
            ->with('success', 'Request updated successfully.');
    }

    /**
     * Cancel request
     */
    public function cancelRequest(CompanyRequest $companyRequest, Request $request)
    {
        $this->authorize('update', $companyRequest);

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $companyRequest->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return redirect()->route('company.requests.index')
            ->with('success', 'Request cancelled successfully.');
    }

    /**
     * Show driver matching interface
     */
    public function matchingIndex(Request $request)
    {
        $company = Auth::guard('company')->user();

        $query = Drivers::where('status', 'Active')
            ->where('verification_status', 'Verified')
            ->with(['performance', 'documents']);

        // Filter by location
        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        // Filter by experience
        if ($request->filled('experience')) {
            $query->where('years_of_experience', '>=', $request->experience);
        }

        // Filter by vehicle type
        if ($request->filled('vehicle_type')) {
            $query->whereJsonContains('vehicle_types', $request->vehicle_type);
        }

        // Search by name or ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('driver_id', 'like', "%{$search}%");
            });
        }

        $drivers = $query->paginate(12);

        // Get available states for filter
        $states = Drivers::where('status', 'Active')
            ->whereNotNull('state')
            ->distinct()
            ->pluck('state')
            ->sort();

        return view('company.matching.index', compact('drivers', 'states'));
    }

    /**
     * Show driver profile for matching
     */
    public function showDriver(Drivers $driver)
    {
        // Get driver's performance stats
        $performance = $driver->performance;

        // Get driver's recent jobs/matches
        $recentJobs = DriverMatch::where('driver_id', $driver->id)
            ->with('request.company')
            ->latest()
            ->take(5)
            ->get();

        return view('company.matching.show', compact('driver', 'performance', 'recentJobs'));
    }

    /**
     * Initiate match with driver
     */
    public function initiateMatch(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'request_id' => 'required|exists:company_requests,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $company = Auth::guard('company')->user();
        $companyRequest = CompanyRequest::findOrFail($request->request_id);

        // Verify the request belongs to this company
        if ($companyRequest->company_id !== $company->id) {
            abort(403, 'Unauthorized');
        }

        // Check if match already exists
        $existingMatch = DriverMatch::where('company_request_id', $request->request_id)
            ->where('driver_id', $request->driver_id)
            ->first();

        if ($existingMatch) {
            return back()->with('error', 'A match already exists for this driver and request.');
        }

        // Create the match
        DriverMatch::create([
            'match_id' => $this->generateMatchId(),
            'company_request_id' => $request->request_id,
            'driver_id' => $request->driver_id,
            'status' => 'pending',
            'matched_at' => now(),
            'match_notes' => $request->notes,
        ]);

        return redirect()->route('company.matching.index')
            ->with('success', 'Match request sent to driver. Waiting for acceptance.');
    }

    /**
     * Show company profile
     */
    public function profileIndex()
    {
        $company = Auth::guard('company')->user();

        return view('company.profile.index', compact('company'));
    }

    /**
     * Update company profile
     */
    public function updateProfile(Request $request)
    {
        $company = Auth::guard('company')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_email' => 'required|email|max:255|unique:companies,contact_person_email,' . $company->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'state' => 'required|string|max:100',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $company->update($request->only([
            'name',
            'contact_person_name',
            'contact_person_email',
            'phone',
            'address',
            'city',
            'state',
            'website',
            'description',
        ]));

        return redirect()->route('company.profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Generate unique request ID
     */
    private function generateRequestId()
    {
        do {
            $id = 'REQ' . date('Y') . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (CompanyRequest::where('request_id', $id)->exists());

        return $id;
    }

    /**
     * Generate unique match ID
     */
    private function generateMatchId()
    {
        do {
            $id = 'MAT' . date('Y') . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (DriverMatch::where('match_id', $id)->exists());

        return $id;
    }

    /**
     * Map urgency to priority
     */
    private function mapUrgencyToPriority($urgency)
    {
        $mapping = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ];

        return $mapping[$urgency] ?? 2;
    }

    /**
     * Get status badge for requests
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => ['text' => 'Pending', 'class' => 'badge-warning'],
            'active' => ['text' => 'Active', 'class' => 'badge-success'],
            'completed' => ['text' => 'Completed', 'class' => 'badge-info'],
            'cancelled' => ['text' => 'Cancelled', 'class' => 'badge-danger'],
        ];

        return $badges[$status] ?? ['text' => ucfirst($status), 'class' => 'badge-secondary'];
    }

    /**
     * Get status badge for companies
     */
    private function getCompanyStatusBadge($company)
    {
        if ($company->verification_status === 'verified') {
            return ['text' => 'Verified', 'class' => 'badge-success'];
        } elseif ($company->verification_status === 'pending') {
            return ['text' => 'Pending Verification', 'class' => 'badge-warning'];
        } elseif ($company->verification_status === 'rejected') {
            return ['text' => 'Rejected', 'class' => 'badge-danger'];
        } else {
            return ['text' => 'Unverified', 'class' => 'badge-secondary'];
        }
    }
}

<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyRequest;
use App\Models\CompanyMatch;
use App\Models\Fleet;
use App\Models\Vehicle;
use App\Models\Invoice;

class DashboardController extends Controller
{
    /**
     * Show the company dashboard
     */
    public function index()
    {
        $company = Auth::guard('company')->user();

        // Get dashboard statistics
        $stats = [
            'total_requests' => CompanyRequest::where('company_id', $company->id)->count(),
            'active_requests' => CompanyRequest::where('company_id', $company->id)->where('status', 'active')->count(),
            'total_matches' => CompanyMatch::where('company_id', $company->id)->count(),
            'total_fleets' => Fleet::where('company_id', $company->id)->count(),
            'total_vehicles' => Vehicle::where('company_id', $company->id)->count(),
            'pending_invoices' => Invoice::where('company_id', $company->id)->where('status', 'pending')->count(),
            'total_spent' => Invoice::where('company_id', $company->id)->where('status', 'paid')->sum('amount'),
            'completed_requests' => CompanyRequest::where('company_id', $company->id)->where('status', 'completed')->count(),
        ];

        // Get recent activity
        $recentRequests = CompanyRequest::where('company_id', $company->id)->latest()->take(5)->get();
        $recentMatches = CompanyMatch::where('company_id', $company->id)->with('companyRequest')->latest()->take(5)->get();

        return view('company.dashboard', compact('stats', 'recentRequests', 'recentMatches'));
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function getStats()
    {
        $company = Auth::guard('company')->user();

        $stats = [
            'total_requests' => CompanyRequest::where('company_id', $company->id)->count(),
            'active_requests' => CompanyRequest::where('company_id', $company->id)->where('status', 'active')->count(),
            'total_matches' => CompanyMatch::where('company_id', $company->id)->count(),
            'total_fleets' => Fleet::where('company_id', $company->id)->count(),
            'total_vehicles' => Vehicle::where('company_id', $company->id)->count(),
            'pending_invoices' => Invoice::where('company_id', $company->id)->where('status', 'pending')->count(),
            'total_spent' => Invoice::where('company_id', $company->id)->where('status', 'paid')->sum('amount'),
            'completed_requests' => CompanyRequest::where('company_id', $company->id)->where('status', 'completed')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get recent activity via AJAX
     */
    public function getRecentActivity()
    {
        $company = Auth::guard('company')->user();

        $recentRequests = CompanyRequest::where('company_id', $company->id)
            ->latest()
            ->take(5)
            ->select('id', 'request_id', 'pickup_location', 'dropoff_location', 'status', 'created_at')
            ->get();

        $recentMatches = CompanyMatch::where('company_id', $company->id)
            ->with(['companyRequest:id,request_id', 'driver:id,name'])
            ->latest()
            ->take(5)
            ->select('id', 'company_request_id', 'driver_id', 'status', 'created_at')
            ->get();

        return response()->json([
            'recentRequests' => $recentRequests,
            'recentMatches' => $recentMatches
        ]);
    }
}

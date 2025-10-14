<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyRequest;
use App\Models\DriverMatch;
use App\Models\Drivers as Driver;
use App\Models\Company;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    public function index()
    {
        $requests = CompanyRequest::with(['company', 'matches'])
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(20);
                                 
        return view('admin.requests.index', compact('requests'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $drivers = Driver::orderBy('first_name')->get();
                         
        return view('admin.requests.create', compact('companies', 'drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'request_type' => 'required|string',
            'driver_id' => 'nullable|exists:drivers,id',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $companyRequest = CompanyRequest::create([
            'company_id' => $request->company_id,
            'driver_id' => $request->driver_id,
            'status' => $request->status ?? 'pending',
            'description' => $request->description,
        ]);

        return redirect()->route('admin.requests.index')
                        ->with('success', 'Company request created successfully!');
    }

    public function show(CompanyRequest $request)
    {
        $request->load(['company', 'driver', 'matches.driver']);
        return view('admin.requests.show', compact('request'));
    }

    public function edit(CompanyRequest $request)
    {
        $companies = Company::orderBy('name')->get();
        $drivers = Driver::orderBy('first_name')->get();
        
        return view('admin.requests.edit', compact('request', 'companies', 'drivers'));
    }

    public function update(Request $request, CompanyRequest $companyRequest)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'request_type' => 'required|string',
            'driver_id' => 'nullable|exists:drivers,id',
            'description' => 'nullable|string',
            'status' => 'required|in:approved,completed,cancelled,pending,rejected',
        ]);

        $companyRequest->update([
            'company_id' => $request->company_id,
            'driver_id' => $request->driver_id,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.requests.index')
                        ->with('success', 'Company request updated successfully!');
    }

    public function destroy(CompanyRequest $request)
    {
        $request->delete();
        
        return redirect()->route('admin.requests.index')
                        ->with('success', 'Company request deleted successfully!');
    }

    public function approve(CompanyRequest $request)
    {
        $request->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Request approved successfully!');
    }

    public function reject(CompanyRequest $request)
    {
        $request->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Request rejected successfully!');
    }

    public function cancel(CompanyRequest $request)
    {
        $request->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Request cancelled successfully!');
    }

    public function viewMatches(CompanyRequest $request)
    {
        $matches = DriverMatch::with('driver')
                             ->where('company_request_id', $request->id)
                             ->get();
                             
        return view('admin.requests.matches', compact('request', 'matches'));
    }

    public function createMatch(Request $request, CompanyRequest $companyRequest)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $driver = Driver::findOrFail($request->driver_id);

        if (!$driver->isAvailableForJob()) {
            return back()->with('error', 'Selected driver is not available for jobs!');
        }

        $match = DriverMatch::create([
            'match_id' => $this->generateMatchId(),
            'company_request_id' => $companyRequest->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
            'commission_rate' => $request->commission_rate,
            'matched_at' => now(),
            'matched_by_admin' => true,
        ]);

        return back()->with('success', 'Match created successfully!');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->action;
        $requestIds = $request->request_ids;

        switch ($action) {
            case 'approve':
                CompanyRequest::whereIn('id', $requestIds)->update([
                    'status' => 'approved',
                    'approved_at' => now()
                ]);
                $message = 'Selected requests approved successfully!';
                break;
            case 'reject':
                CompanyRequest::whereIn('id', $requestIds)->update([
                    'status' => 'rejected',
                    'rejected_at' => now()
                ]);
                $message = 'Selected requests rejected successfully!';
                break;
            case 'cancel':
                CompanyRequest::whereIn('id', $requestIds)->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now()
                ]);
                $message = 'Selected requests cancelled successfully!';
                break;
            default:
                $message = 'Unknown action!';
        }

        return back()->with('success', $message);
    }

    public function acceptPage(Request $request)
    {
        $query = CompanyRequest::with(['company'])
                              ->whereIn('status', ['Pending', 'Under Review']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $requests = $query->orderByRaw("CASE 
                                       WHEN priority = 'Urgent' THEN 1 
                                       WHEN priority = 'High' THEN 2 
                                       ELSE 3 END")
                         ->orderBy('created_at', 'asc')
                         ->paginate(20);
        
        // Calculate statistics
        $stats = [
            'pending' => CompanyRequest::where('status', 'Pending')->count(),
            'accepted' => CompanyRequest::where('status', 'Accepted')
                                      ->whereDate('updated_at', today())
                                      ->count(),
            'processing' => CompanyRequest::where('status', 'Processing')->count(),
            'urgent' => CompanyRequest::where('priority', 'Urgent')
                                    ->where('status', 'Pending')
                                    ->count(),
        ];
        
        // Get administrators for assignment
        $administrators = \App\Models\AdminUser::where('status', 'Active')->get();
        
        return view('admin.requests.accept', compact('requests', 'stats', 'administrators'));
    }

    public function queueManagement(Request $request)
    {
        // Get pending requests ordered by queue position
        $pendingRequests = CompanyRequest::with(['company'])
                                        ->where('status', 'Pending')
                                        ->orderBy('queue_position', 'asc')
                                        ->orderBy('created_at', 'asc')
                                        ->get();
        
        // Get processing requests
        $processingRequests = CompanyRequest::with(['company', 'assignedAdmin'])
                                          ->where('status', 'Processing')
                                          ->orderBy('updated_at', 'desc')
                                          ->get();
        
        // Get completed requests (last 30 days)
        $completedRequests = CompanyRequest::with(['company', 'assignedAdmin'])
                                         ->where('status', 'Completed')
                                         ->where('completed_at', '>=', now()->subDays(30))
                                         ->orderBy('completed_at', 'desc')
                                         ->limit(50)
                                         ->get();
        
        // Get urgent requests
        $urgentRequests = CompanyRequest::with(['company'])
                                      ->where('priority', 'Urgent')
                                      ->whereIn('status', ['Pending', 'Processing'])
                                      ->orderBy('created_at', 'asc')
                                      ->get();
        
        // Calculate queue statistics
        $queueStats = [
            'pending' => $pendingRequests->count(),
            'processing' => $processingRequests->count(),
            'completed' => CompanyRequest::where('status', 'Completed')->count(),
            'urgent' => $urgentRequests->count(),
            'overdue' => CompanyRequest::where('estimated_completion', '<', now())
                                     ->whereIn('status', ['Pending', 'Processing'])
                                     ->count(),
            'avg_processing_time' => $this->calculateAverageProcessingTime(),
        ];
        
        $administrators = \App\Models\AdminUser::where('status', 'Active')->get();
        
        return view('admin.requests.queue', compact(
            'pendingRequests', 
            'processingRequests', 
            'completedRequests', 
            'urgentRequests', 
            'queueStats',
            'administrators'
        ));
    }

    public function acceptRequest(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:company_requests,id',
            'notes' => 'nullable|string',
            'estimated_completion' => 'nullable|date',
            'assigned_to' => 'nullable|exists:admin_users,id',
            'priority' => 'required|in:Normal,High,Urgent',
            'auto_assign_drivers' => 'boolean'
        ]);

        $companyRequest = CompanyRequest::findOrFail($request->request_id);
        
        $updateData = [
            'status' => 'Accepted',
            'accepted_at' => now(),
            'acceptance_notes' => $request->notes,
            'estimated_completion' => $request->estimated_completion,
            'assigned_to' => $request->assigned_to,
            'priority' => $request->priority,
        ];
        
        $companyRequest->update($updateData);
        
        // Auto-assign drivers if requested
        if ($request->auto_assign_drivers) {
            $this->autoAssignDrivers($companyRequest);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Request accepted successfully.'
        ]);
    }

    public function bulkAcceptRequests(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:company_requests,id'
        ]);

        $processedCount = CompanyRequest::whereIn('id', $request->request_ids)
                                      ->update([
                                          'status' => 'Accepted',
                                          'accepted_at' => now(),
                                          'priority' => 'Normal'
                                      ]);
        
        return response()->json([
            'success' => true,
            'message' => "{$processedCount} requests accepted successfully."
        ]);
    }

    public function updateRequestStatus(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:company_requests,id',
            'status' => 'required|string'
        ]);

        $companyRequest = CompanyRequest::findOrFail($request->request_id);
        $companyRequest->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true,
            'message' => 'Request status updated successfully.'
        ]);
    }

    public function getAvailableDrivers(Request $request)
    {
        $requestId = $request->request_id;
        $companyRequest = CompanyRequest::findOrFail($requestId);
        
        // Get available drivers based on location and other criteria
        $drivers = Driver::where('status', 'active')
                        ->where('is_active', true)
                        ->where('verification_status', 'verified')
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->get();
        
        $html = view('admin.requests.partials.available-drivers', compact('drivers', 'companyRequest'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function performQueueAction(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:company_requests,id',
            'action' => 'required|in:process,complete,pause',
            'assigned_to' => 'nullable|exists:admin_users,id',
            'notes' => 'nullable|string',
            'completion_notes' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        $companyRequest = CompanyRequest::findOrFail($request->request_id);
        
        switch ($request->action) {
            case 'process':
                $companyRequest->update([
                    'status' => 'Processing',
                    'assigned_to' => $request->assigned_to,
                    'processing_notes' => $request->notes,
                    'started_at' => now()
                ]);
                break;
                
            case 'complete':
                $companyRequest->update([
                    'status' => 'Completed',
                    'completion_notes' => $request->completion_notes,
                    'rating' => $request->rating,
                    'completed_at' => now()
                ]);
                break;
                
            case 'pause':
                $companyRequest->update([
                    'status' => 'Paused',
                    'pause_reason' => $request->notes,
                    'paused_at' => now()
                ]);
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Queue action performed successfully.'
        ]);
    }

    public function moveRequestInQueue(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:company_requests,id',
            'direction' => 'required|in:up,down'
        ]);

        $companyRequest = CompanyRequest::findOrFail($request->request_id);
        
        // Simple implementation - update timestamps to change order
        if ($request->direction === 'up') {
            $companyRequest->update(['created_at' => now()->subMinute()]);
        } else {
            $companyRequest->update(['created_at' => now()->addMinute()]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Request moved in queue successfully.'
        ]);
    }

    public function batchProcessRequests(Request $request)
    {
        $pendingRequests = CompanyRequest::where('status', 'Pending')
                                       ->orderBy('created_at', 'asc')
                                       ->limit(5)
                                       ->get();
        
        $processedCount = 0;
        foreach ($pendingRequests as $req) {
            $req->update([
                'status' => 'Processing',
                'started_at' => now()
            ]);
            $processedCount++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$processedCount} requests moved to processing."
        ]);
    }

    public function reorderQueueByPriority(Request $request)
    {
        // Update queue positions based on priority
        CompanyRequest::where('status', 'Pending')
                     ->where('priority', 'Urgent')
                     ->update(['queue_position' => 1]);
        
        CompanyRequest::where('status', 'Pending')
                     ->where('priority', 'High')
                     ->update(['queue_position' => 2]);
        
        CompanyRequest::where('status', 'Pending')
                     ->where('priority', 'Normal')
                     ->update(['queue_position' => 3]);
        
        return response()->json([
            'success' => true,
            'message' => 'Queue reordered by priority successfully.'
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        // TODO: Implement export functionality
        
        return back()->with('info', 'Export functionality coming soon!');
    }

    private function autoAssignDrivers($companyRequest)
    {
        // Simple auto-assignment logic
        $availableDrivers = Driver::where('status', 'active')
                                 ->where('is_active', true)
                                 ->where('verification_status', 'verified')
                                 ->limit(3)
                                 ->get();
        
        foreach ($availableDrivers as $driver) {
            DriverMatch::create([
                'match_id' => $this->generateMatchId(),
                'company_request_id' => $companyRequest->id,
                'driver_id' => $driver->id,
                'status' => 'pending',
                'commission_rate' => 10, // Default rate
                'matched_at' => now(),
                'matched_by_admin' => true,
            ]);
        }
    }

    private function calculateAverageProcessingTime()
    {
        $completedRequests = CompanyRequest::where('status', 'Completed')
                                         ->whereNotNull('started_at')
                                         ->whereNotNull('completed_at')
                                         ->get();
        
        if ($completedRequests->isEmpty()) {
            return 0;
        }
        
        $totalHours = 0;
        foreach ($completedRequests as $request) {
            $totalHours += $request->started_at->diffInHours($request->completed_at);
        }
        
        return round($totalHours / $completedRequests->count(), 1);
    }

    private function generateMatchId()
    {
        do {
            $id = 'MT' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (DriverMatch::where('match_id', $id)->exists());
        
        return $id;
    }
}
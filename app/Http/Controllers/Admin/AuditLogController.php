<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
    }

    /**
     * Display audit logs
     */
    public function index(Request $request)
    {
        $query = UserActivity::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(50);

        return view('admin.superadmin.audit-logs', compact('activities'));
    }

    /**
     * Get activity details
     */
    public function getActivityDetails($id)
    {
        $activity = UserActivity::with('user')->findOrFail($id);

        return response()->json([
            'activity' => $activity,
            'formatted_data' => [
                'user' => $activity->user ? $activity->user->name : 'Unknown User',
                'action' => ucfirst($activity->action),
                'description' => $activity->description,
                'timestamp' => $activity->created_at->format('Y-m-d H:i:s'),
                'ip_address' => $activity->ip_address,
                'user_agent' => $activity->user_agent,
                'old_values' => $activity->old_values,
                'new_values' => $activity->new_values,
                'metadata' => $activity->metadata
            ]
        ]);
    }
}

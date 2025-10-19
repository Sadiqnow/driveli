<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditTrailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('role:super_admin');
    }

    /**
     * Display a listing of audit trails.
     */
    public function index(Request $request)
    {
        $query = AuditTrail::with(['user', 'role', 'targetUser']);

        // Apply filters
        if ($request->filled('action_type')) {
            $query->byActionType($request->action_type);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('target_user_id')) {
            $query->byTargetUser($request->target_user_id);
        }

        if ($request->filled('role_id')) {
            $query->byRole($request->role_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $auditTrails = $query->orderBy('created_at', 'desc')
                            ->paginate(20);

        $users = AdminUser::select('id', 'name')->get();
        $roles = Role::select('id', 'display_name')->get();

        return view('superadmin.audit-trails.index', compact('auditTrails', 'users', 'roles'));
    }

    /**
     * Display the specified audit trail.
     */
    public function show(AuditTrail $auditTrail)
    {
        $auditTrail->load(['user', 'role', 'targetUser']);

        return view('superadmin.audit-trails.show', compact('auditTrail'));
    }

    /**
     * Export audit trails to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = AuditTrail::with(['user', 'role', 'targetUser']);

        // Apply same filters as index
        if ($request->filled('action_type')) {
            $query->byActionType($request->action_type);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('target_user_id')) {
            $query->byTargetUser($request->target_user_id);
        }

        if ($request->filled('role_id')) {
            $query->byRole($request->role_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $auditTrails = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit-trails-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($auditTrails) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Action Type',
                'User',
                'Role',
                'Target User',
                'Description',
                'IP Address',
                'Created At'
            ]);

            // CSV data
            foreach ($auditTrails as $audit) {
                fputcsv($file, [
                    $audit->id,
                    ucfirst($audit->action_type),
                    $audit->user ? $audit->user->name : 'N/A',
                    $audit->role ? $audit->role->display_name : 'N/A',
                    $audit->targetUser ? $audit->targetUser->name : 'N/A',
                    $audit->description,
                    $audit->ip_address,
                    $audit->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export audit trails to PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = AuditTrail::with(['user', 'role', 'targetUser']);

        // Apply same filters as index
        if ($request->filled('action_type')) {
            $query->byActionType($request->action_type);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('target_user_id')) {
            $query->byTargetUser($request->target_user_id);
        }

        if ($request->filled('role_id')) {
            $query->byRole($request->role_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $auditTrails = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('superadmin.audit-trails.pdf', compact('auditTrails'));

        $filename = 'audit-trails-' . now()->format('Y-m-d-H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get audit trail statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_actions' => AuditTrail::count(),
            'actions_today' => AuditTrail::whereDate('created_at', today())->count(),
            'actions_this_week' => AuditTrail::where('created_at', '>=', now()->startOfWeek())->count(),
            'actions_this_month' => AuditTrail::where('created_at', '>=', now()->startOfMonth())->count(),
            'actions_by_type' => AuditTrail::selectRaw('action_type, COUNT(*) as count')
                                        ->groupBy('action_type')
                                        ->pluck('count', 'action_type')
                                        ->toArray(),
            'most_active_users' => AuditTrail::with('user')
                                           ->selectRaw('user_id, COUNT(*) as count')
                                           ->whereNotNull('user_id')
                                           ->groupBy('user_id')
                                           ->orderBy('count', 'desc')
                                           ->limit(5)
                                           ->get()
                                           ->map(function($item) {
                                               return [
                                                   'user' => $item->user,
                                                   'count' => $item->count
                                               ];
                                           }),
        ];

        return response()->json($stats);
    }
}

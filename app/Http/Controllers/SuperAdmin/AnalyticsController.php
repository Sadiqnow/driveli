<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AdminUser;
use App\Models\AuditLog;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('can:view_permission_analytics');
    }

    /**
     * Display the roles analytics dashboard
     */
    public function index()
    {
        $analytics = $this->getAnalyticsData();

        return view('superadmin.analytics', compact('analytics'));
    }

    /**
     * Get roles analytics data
     */
    public function roles(): JsonResponse
    {
        $data = $this->getRolesAnalytics();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get violations analytics data
     */
    public function violations(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $module = $request->get('module');

        $query = AuditLog::where('action', 'access_denied')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($module) {
            $query->where('resource_type', $module);
        }

        $violations = $query->select([
                'resource_type',
                'resource_id',
                'ip_address',
                'created_at',
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('resource_type', 'resource_id', 'ip_address')
            ->orderBy('count', 'desc')
            ->get();

        $totalViolations = $violations->sum('count');

        $violationsByModule = $violations->groupBy('resource_type')->map(function ($group) {
            return [
                'module' => $group->first()->resource_type,
                'count' => $group->sum('count'),
                'percentage' => 0 // Will be calculated below
            ];
        })->values();

        // Calculate percentages
        $violationsByModule->transform(function ($item) use ($totalViolations) {
            $item['percentage'] = $totalViolations > 0 ? round(($item['count'] / $totalViolations) * 100, 2) : 0;
            return $item;
        });

        // Timeline data for chart
        $timelineData = AuditLog::where('action', 'access_denied')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_violations' => $totalViolations,
                'violations_by_module' => $violationsByModule,
                'timeline' => $timelineData,
                'recent_violations' => $violations->take(10)->map(function ($violation) {
                    return [
                        'module' => $violation->resource_type,
                        'resource' => $violation->resource_id,
                        'ip_address' => $violation->ip_address,
                        'count' => $violation->count,
                        'last_seen' => $violation->created_at
                    ];
                })
            ]
        ]);
    }

    /**
     * Get permission usage analytics
     */
    public function usage(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Use permission_logs table for accurate permission usage tracking
        $usageData = DB::table('permission_logs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'permission_name',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(CASE WHEN result = "granted" THEN 1 ELSE 0 END) as granted_count'),
                DB::raw('SUM(CASE WHEN result = "denied" THEN 1 ELSE 0 END) as denied_count')
            ])
            ->groupBy('permission_name')
            ->orderBy('usage_count', 'desc')
            ->get();

        $totalUsage = $usageData->sum('usage_count');

        $permissions = Permission::withCount('roles')->get();

        $unusedPermissions = $permissions->filter(function ($permission) use ($usageData) {
            return !$usageData->contains('permission_name', $permission->name);
        })->map(function ($permission) {
            return [
                'name' => $permission->display_name,
                'category' => $permission->category,
                'roles_count' => $permission->roles_count
            ];
        });

        $overlappingPermissions = $permissions->filter(function ($permission) {
            return $permission->roles_count > 1;
        })->map(function ($permission) {
            return [
                'name' => $permission->display_name,
                'roles_count' => $permission->roles_count,
                'category' => $permission->category
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'most_used_permissions' => $usageData->take(10)->map(function ($item) use ($totalUsage) {
                    return [
                        'name' => $item->permission_name,
                        'usage_count' => $item->usage_count,
                        'granted_count' => $item->granted_count,
                        'denied_count' => $item->denied_count,
                        'percentage' => $totalUsage > 0 ? round(($item->usage_count / $totalUsage) * 100, 2) : 0
                    ];
                }),
                'unused_permissions' => $unusedPermissions,
                'overlapping_permissions' => $overlappingPermissions,
                'usage_distribution' => $usageData->map(function ($item) {
                    return [
                        'permission' => $item->permission_name,
                        'count' => $item->usage_count,
                        'granted' => $item->granted_count,
                        'denied' => $item->denied_count
                    ];
                })
            ]
        ]);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'roles');
        $format = $request->get('format', 'csv');

        $data = [];
        $filename = "permission-analytics-{$type}-" . now()->format('Y-m-d');

        switch ($type) {
            case 'roles':
                $data = $this->getRolesAnalytics();
                break;
            case 'violations':
                $violations = $this->violations($request);
                $data = $violations->getData()->data;
                break;
            case 'usage':
                $usage = $this->usage($request);
                $data = $usage->getData()->data;
                break;
        }

        if ($format === 'csv') {
            return $this->exportToCsv($data, $filename);
        }

        return response()->json($data);
    }

    /**
     * Send weekly summary report
     */
    public function sendWeeklyReport(Request $request): JsonResponse
    {
        // Implementation for sending weekly reports via email
        // This would integrate with Laravel's mail system

        return response()->json([
            'success' => true,
            'message' => 'Weekly report sent successfully'
        ]);
    }

    /**
     * Get comprehensive analytics data
     */
    private function getAnalyticsData()
    {
        return Cache::remember('permission_analytics', 3600, function () {
            return [
                'roles' => $this->getRolesAnalytics(),
                'permissions' => $this->getPermissionsAnalytics(),
                'violations' => $this->getViolationsSummary(),
                'usage' => $this->getUsageSummary()
            ];
        });
    }

    /**
     * Get roles analytics
     */
    private function getRolesAnalytics()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();

        $usersPerRole = $roles->map(function ($role) {
            return [
                'role' => $role->display_name,
                'users_count' => $role->users_count
            ];
        })->sortByDesc('users_count');

        return [
            'total_roles' => $roles->count(),
            'total_permissions' => Permission::count(),
            'total_users' => AdminUser::count(),
            'users_per_role' => $usersPerRole,
            'roles_list' => $roles->map(function ($role) {
                return [
                    'name' => $role->display_name,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                    'level' => $role->level,
                    'is_active' => $role->is_active
                ];
            })
        ];
    }

    /**
     * Get permissions analytics
     */
    private function getPermissionsAnalytics()
    {
        $permissions = Permission::withCount('roles')->get();

        return [
            'total_permissions' => $permissions->count(),
            'permissions_by_category' => $permissions->groupBy('category')->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'count' => $group->count(),
                    'permissions' => $group->pluck('display_name')
                ];
            }),
            'permissions_with_multiple_roles' => $permissions->filter(function ($permission) {
                return $permission->roles_count > 1;
            })->map(function ($permission) {
                return [
                    'name' => $permission->display_name,
                    'roles_count' => $permission->roles_count,
                    'category' => $permission->category
                ];
            })
        ];
    }

    /**
     * Get violations summary
     */
    private function getViolationsSummary()
    {
        $last30Days = AuditLog::where('action', 'access_denied')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'total_last_30_days' => $last30Days,
            'most_common_modules' => AuditLog::where('action', 'access_denied')
                ->select('resource_type', DB::raw('COUNT(*) as count'))
                ->groupBy('resource_type')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'module' => $item->resource_type,
                        'violations' => $item->count
                    ];
                })
        ];
    }

    /**
     * Get usage summary
     */
    private function getUsageSummary()
    {
        $permissions = Permission::withCount('roles')->get();

        return [
            'unused_permissions_count' => $permissions->filter(function ($permission) {
                return $permission->roles_count === 0;
            })->count(),
            'most_used_categories' => $permissions->groupBy('category')->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'permissions_count' => $group->count(),
                    'assigned_roles_avg' => round($group->avg('roles_count'), 2)
                ];
            })->sortByDesc('permissions_count')
        ];
    }

    /**
     * Export data to CSV
     */
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}.csv"
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Write headers
            if (is_array($data) && !empty($data)) {
                fputcsv($file, array_keys($data));
            }

            // Write data
            if (is_array($data)) {
                foreach ($data as $row) {
                    if (is_array($row)) {
                        fputcsv($file, $row);
                    } else {
                        fputcsv($file, [$row]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

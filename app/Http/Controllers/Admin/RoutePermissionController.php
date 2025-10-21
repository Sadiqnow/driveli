<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\RoutePermission;

class RoutePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('check.permission:manage_route_permissions');
    }

    /**
     * Display route-permission mapping dashboard
     */
    public function index(Request $request)
    {
        // Get all routes with their permissions
        $routes = $this->getAllRoutes();

        // Get all permissions for mapping
        $permissions = Permission::where('is_active', true)->orderBy('name')->get();

        // Get existing route-permission mappings
        $routePermissions = RoutePermission::with(['permission', 'route'])
                                          ->orderBy('route_name')
                                          ->get()
                                          ->keyBy('route_name');

        return view('admin.route-permissions.index', compact('routes', 'permissions', 'routePermissions'));
    }

    /**
     * Store route-permission mapping
     */
    public function store(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string',
            'permission_id' => 'required|exists:permissions,id',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            RoutePermission::updateOrCreate(
                ['route_name' => $request->route_name],
                [
                    'permission_id' => $request->permission_id,
                    'description' => $request->description,
                    'is_active' => true
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Route permission mapping saved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save route permission mapping: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update route-permission mapping
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $routePermission = RoutePermission::findOrFail($id);
            $routePermission->update([
                'permission_id' => $request->permission_id,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Route permission mapping updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update route permission mapping: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove route-permission mapping
     */
    public function destroy($id)
    {
        try {
            $routePermission = RoutePermission::findOrFail($id);
            $routePermission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Route permission mapping removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove route permission mapping: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update route permissions
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.route_name' => 'required|string',
            'mappings.*.permission_id' => 'nullable|exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->mappings as $mapping) {
                if ($mapping['permission_id']) {
                    RoutePermission::updateOrCreate(
                        ['route_name' => $mapping['route_name']],
                        [
                            'permission_id' => $mapping['permission_id'],
                            'is_active' => true
                        ]
                    );
                } else {
                    // Remove mapping if permission_id is null
                    RoutePermission::where('route_name', $mapping['route_name'])->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk route permission mappings updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bulk mappings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync routes with database
     */
    public function syncRoutes()
    {
        try {
            $routes = $this->getAllRoutes();

            foreach ($routes as $route) {
                // Create route record if it doesn't exist
                \App\Models\Route::updateOrCreate(
                    ['name' => $route['name']],
                    [
                        'uri' => $route['uri'],
                        'methods' => json_encode($route['methods']),
                        'controller' => $route['controller'] ?? null,
                        'action' => $route['action'] ?? null,
                        'middleware' => json_encode($route['middleware'] ?? []),
                        'is_active' => true
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Routes synchronized successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync routes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all application routes
     */
    private function getAllRoutes()
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            // Only include admin routes
            if (str_starts_with($route->getName(), 'admin.')) {
                $routes[] = [
                    'name' => $route->getName(),
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'controller' => $route->getController() ? get_class($route->getController()) : null,
                    'action' => $route->getActionName(),
                    'middleware' => $route->middleware()
                ];
            }
        }

        return $routes;
    }

    /**
     * Export route permissions
     */
    public function export()
    {
        $routePermissions = RoutePermission::with(['permission', 'route'])
                                          ->orderBy('route_name')
                                          ->get();

        $data = $routePermissions->map(function ($rp) {
            return [
                'Route Name' => $rp->route_name,
                'Permission' => $rp->permission->name,
                'Description' => $rp->description,
                'Created At' => $rp->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $rp->updated_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}

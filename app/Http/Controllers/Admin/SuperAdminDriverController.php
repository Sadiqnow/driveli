<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SuperAdminDriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
    }

    /**
     * Display a listing of drivers
     */
    public function index(Request $request)
    {
        // Use optimized service for better performance
        $optimizationService = app(\App\Services\DriverQueryOptimizationService::class);

        // Get cached statistics
        $stats = $optimizationService->getDashboardStats();

        // Prepare filters array
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'verification_status' => $request->get('verification_status'),
            'kyc_status' => $request->get('kyc_status'),
        ];

        // Remove null values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        // Get optimized paginated results
        $drivers = $optimizationService->getAdminDriverList($filters, 25);

        return view('admin.superadmin.drivers.index', compact('drivers', 'stats'));
    }

    /**
     * Show the form for creating a new driver
     */
    public function create()
    {
        return view('admin.superadmin.drivers.create');
    }

    /**
     * Store a newly created driver
     */
    public function store(\App\Http\Requests\StoreDriverRequest $request)
    {
        try {
            $data = $request->validated();
            $data['driver_id'] = 'DRV-' . strtoupper(uniqid());
            $data['profile_completion_percentage'] = 0; // Will be calculated based on filled fields

            $driver = \App\Models\Drivers::create($data);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverCreation($driver, $request);

            return redirect()->route('admin.superadmin.drivers.show', $driver)
                           ->with('success', 'Driver created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified driver
     */
    public function show(\App\Models\Drivers $driver)
    {
        // Use optimized service for better performance
        $optimizationService = app(\App\Services\DriverQueryOptimizationService::class);
        $driver = $optimizationService->getDriverDetails($driver->id);

        if (!$driver) {
            abort(404, 'Driver not found');
        }

        return view('admin.superadmin.drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver
     */
    public function edit(\App\Models\Drivers $driver)
    {
        return view('admin.superadmin.drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver
     */
    public function update(\App\Http\Requests\UpdateDriverRequest $request, \App\Models\Drivers $driver)
    {
        try {
            $oldValues = $driver->toArray();
            $driver->update($request->validated());

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverUpdate($driver, $oldValues, $driver->toArray(), $request);

            return redirect()->route('admin.superadmin.drivers.show', $driver)
                           ->with('success', 'Driver updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified driver
     */
    public function destroy(\App\Models\Drivers $driver)
    {
        try {
            // Log activity before deletion
            \App\Services\SuperadminActivityLogger::logDriverDeletion($driver, request());

            $driver->delete();

            return redirect()->route('admin.superadmin.drivers.index')
                           ->with('success', 'Driver deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve driver application
     */
    public function approve(Request $request, \App\Models\Drivers $driver)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $driver->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth('admin')->id(),
            ]);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverApproval($driver, $request->notes, $request);

            return response()->json([
                'success' => true,
                'message' => 'Driver approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject driver application
     */
    public function reject(Request $request, \App\Models\Drivers $driver)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $driver->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $request->reason,
            ]);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverRejection($driver, $request->reason, $request);

            return response()->json([
                'success' => true,
                'message' => 'Driver rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Flag driver
     */
    public function flag(Request $request, \App\Models\Drivers $driver)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $driver->update(['status' => 'flagged']);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverFlagging($driver, $request->reason, $request);

            return response()->json([
                'success' => true,
                'message' => 'Driver flagged successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to flag driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore driver
     */
    public function restore(\App\Models\Drivers $driver)
    {
        try {
            $driver->update(['status' => 'active']);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverRestoration($driver, request());

            return response()->json([
                'success' => true,
                'message' => 'Driver restored successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve drivers
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update([
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'verified_by' => auth('admin')->id(),
                ]);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('approve', 'driver', $request->driver_ids, null, $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject drivers
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id',
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $request->reason,
                ]);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('reject', 'driver', $request->driver_ids, ['reason' => $request->reason], $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk reject drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk flag drivers
     */
    public function bulkFlag(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id',
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update(['status' => 'flagged']);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('flag', 'driver', $request->driver_ids, ['reason' => $request->reason], $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers flagged successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk flag drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk restore drivers
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update(['status' => 'active']);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('restore', 'driver', $request->driver_ids, null, $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers restored successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk restore drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete drivers
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                // Log individual deletion
                \App\Services\SuperadminActivityLogger::logDriverDeletion($driver, $request);
                $driver->delete();
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('delete', 'driver', $request->driver_ids, null, $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk delete drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export drivers
     */
    public function export(Request $request)
    {
        try {
            // Run the export command
            $command = 'drivers:export';

            if ($request->filled('format')) {
                $command .= ' --format=' . $request->format;
            }

            if ($request->filled('status')) {
                $command .= ' --status=' . $request->status;
            }

            if ($request->filled('verification')) {
                $command .= ' --verification=' . $request->verification;
            }

            if ($request->filled('kyc')) {
                $command .= ' --kyc=' . $request->kyc;
            }

            if ($request->filled('date_from')) {
                $command .= ' --date-from=' . $request->date_from;
            }

            if ($request->filled('date_to')) {
                $command .= ' --date-to=' . $request->date_to;
            }

            // Execute the command
            Artisan::call($command);

            $output = Artisan::output();

            // Log activity
            \App\Services\SuperadminActivityLogger::log(
                'export',
                'Exported drivers data',
                null,
                null,
                null,
                ['command' => $command, 'output' => $output],
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Export completed successfully.',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

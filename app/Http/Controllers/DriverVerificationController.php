<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyDriverRequest;
use App\Http\Resources\DriverVerificationResource;
use App\Jobs\RunDriverVerificationJob;
use App\Models\Drivers;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverVerificationController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Start driver verification process
     */
    public function start(VerifyDriverRequest $request, $driverId)
    {
        try {
            $driver = Drivers::findOrFail($driverId);

            // Dispatch verification job
            RunDriverVerificationJob::dispatch($driver, false);

            Log::info('Driver verification job dispatched', [
                'driver_id' => $driverId,
                'action' => 'start_verification'
            ]);

            return new DriverVerificationResource([
                'success' => true,
                'message' => 'Driver verification started successfully',
                'driver_id' => $driverId,
                'status' => 'queued'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start driver verification', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return new DriverVerificationResource([
                'success' => false,
                'message' => 'Failed to start verification: ' . $e->getMessage(),
                'driver_id' => $driverId
            ], 500);
        }
    }

    /**
     * Reverify driver (force re-verification)
     */
    public function reverify(VerifyDriverRequest $request, $driverId)
    {
        try {
            $driver = Drivers::findOrFail($driverId);

            // Dispatch re-verification job with isReverify = true
            RunDriverVerificationJob::dispatch($driver, true);

            Log::info('Driver re-verification job dispatched', [
                'driver_id' => $driverId,
                'action' => 'reverify'
            ]);

            return new DriverVerificationResource([
                'success' => true,
                'message' => 'Driver re-verification started successfully',
                'driver_id' => $driverId,
                'status' => 'queued'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start driver re-verification', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return new DriverVerificationResource([
                'success' => false,
                'message' => 'Failed to start re-verification: ' . $e->getMessage(),
                'driver_id' => $driverId
            ], 500);
        }
    }

    /**
     * Get driver verification report
     */
    public function report(Request $request, $driverId)
    {
        try {
            $report = $this->reportService->generate($driverId);

            if (!$report['success']) {
                return new DriverVerificationResource([
                    'success' => false,
                    'message' => 'Failed to generate report: ' . $report['error'],
                    'driver_id' => $driverId
                ], 404);
            }

            return new DriverVerificationResource([
                'success' => true,
                'message' => 'Report generated successfully',
                'driver_id' => $driverId,
                'report' => $report['report']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate driver verification report', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return new DriverVerificationResource([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage(),
                'driver_id' => $driverId
            ], 500);
        }
    }
}

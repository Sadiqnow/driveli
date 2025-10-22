<?php

namespace App\Jobs;

use App\Models\DriverMatch;
use App\Models\Drivers;
use App\Models\CompanyRequest;
use App\Jobs\NotificationJob;
use App\Jobs\AdminAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessDriverMatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1min, 5min, 10min

    protected $matchData;
    protected $match;

    /**
     * Create a new job instance.
     */
    public function __construct(array $matchData)
    {
        $this->matchData = $matchData;
        $this->queue = 'matches';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing driver match', ['match_data' => $this->matchData]);

            // 1. Validate match data
            $this->validateMatchData();

            // 2. Check driver availability
            if (!$this->isDriverAvailable()) {
                throw new \Exception('Driver is not available for matching');
            }

            // 3. Calculate commission and fees
            $commission = $this->calculateCommission();

            // 4. Create/update driver_match record
            $this->match = DriverMatch::updateOrCreate(
                [
                    'company_request_id' => $this->matchData['company_request_id'],
                    'driver_id' => $this->matchData['driver_id']
                ],
                [
                    'match_id' => $this->matchData['match_id'] ?? $this->generateMatchId(),
                    'status' => 'matched',
                    'commission_rate' => $this->matchData['commission_rate'],
                    'commission_amount' => $commission,
                    'matched_at' => now(),
                    'matched_by_admin' => $this->matchData['matched_by_admin'] ?? null,
                    'auto_matched' => $this->matchData['auto_matched'] ?? false,
                ]
            );

            // 5. Send notifications to driver and company
            $this->sendNotifications();

            // 6. Update related records
            $this->updateRelatedRecords();

            Log::info('Driver match processed successfully', ['match_id' => $this->match->match_id]);

        } catch (Exception $e) {
            Log::error('Match processing failed: ' . $e->getMessage(), [
                'match_data' => $this->matchData,
                'attempt' => $this->attempts()
            ]);

            // Update match status to failed if it exists
            if ($this->match) {
                $this->match->update(['status' => 'failed']);
            }

            throw $e; // Re-throw to trigger retry or failure handling
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::critical('Driver match processing permanently failed', [
            'match_data' => $this->matchData,
            'error' => $exception->getMessage()
        ]);

        // Send alert to admin
        AdminAlert::dispatch('Match processing failed: ' . $exception->getMessage());
    }

    /**
     * Validate match data.
     */
    private function validateMatchData(): void
    {
        if (!isset($this->matchData['company_request_id']) || !isset($this->matchData['driver_id'])) {
            throw new \InvalidArgumentException('Missing required match data');
        }

        // Validate company request exists and is active
        $companyRequest = CompanyRequest::find($this->matchData['company_request_id']);
        if (!$companyRequest || !in_array($companyRequest->status, ['pending', 'approved'])) {
            throw new \Exception('Invalid or inactive company request');
        }

        // Validate driver exists
        $driver = Drivers::find($this->matchData['driver_id']);
        if (!$driver) {
            throw new \Exception('Driver not found');
        }
    }

    /**
     * Check if driver is available for matching.
     */
    private function isDriverAvailable(): bool
    {
        $driver = Drivers::find($this->matchData['driver_id']);

        return $driver &&
               $driver->status === 'active' &&
               $driver->is_active &&
               $driver->verification_status === 'verified';
    }

    /**
     * Calculate commission amount.
     */
    private function calculateCommission(): float
    {
        $companyRequest = CompanyRequest::find($this->matchData['company_request_id']);
        $commissionRate = $this->matchData['commission_rate'] ?? 10.0;

        // Calculate based on request value or use default
        $baseAmount = $companyRequest->estimated_value ?? 0;
        return ($baseAmount * $commissionRate) / 100;
    }

    /**
     * Send notifications to driver and company.
     */
    private function sendNotifications(): void
    {
        $driver = Drivers::find($this->matchData['driver_id']);
        $companyRequest = CompanyRequest::find($this->matchData['company_request_id']);

        // Notify driver
        if ($driver) {
            NotificationJob::dispatch($driver, 'match_assigned', [
                'match_id' => $this->match->match_id,
                'company_name' => $companyRequest->company->name ?? 'Unknown Company',
                'commission_rate' => $this->match->commission_rate
            ]);
        }

        // Notify company
        if ($companyRequest && $companyRequest->company) {
            NotificationJob::dispatch($companyRequest->company, 'driver_assigned', [
                'match_id' => $this->match->match_id,
                'driver_name' => $driver->first_name . ' ' . $driver->last_name,
                'commission_rate' => $this->match->commission_rate
            ]);
        }
    }

    /**
     * Update related records.
     */
    private function updateRelatedRecords(): void
    {
        // Update company request status if needed
        $companyRequest = CompanyRequest::find($this->matchData['company_request_id']);
        if ($companyRequest && $companyRequest->status === 'pending') {
            $companyRequest->update(['status' => 'processing']);
        }
    }

    /**
     * Generate unique match ID.
     */
    private function generateMatchId(): string
    {
        do {
            $id = 'MT' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (DriverMatch::where('match_id', $id)->exists());

        return $id;
    }
}

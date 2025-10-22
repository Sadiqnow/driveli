<?php

namespace App\Jobs;

use App\Services\DriverMatcherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDriverMatching implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companyRequestId;
    protected $criteria;

    /**
     * Create a new job instance.
     */
    public function __construct(int $companyRequestId, array $criteria = [])
    {
        $this->companyRequestId = $companyRequestId;
        $this->criteria = $criteria;
    }

    /**
     * Execute the job.
     */
    public function handle(DriverMatcherService $matcherService): void
    {
        try {
            Log::info("Starting async driver matching for request {$this->companyRequestId}");

            $results = $matcherService->findMatchingDrivers($this->companyRequestId, $this->criteria);

            // Here you could dispatch notifications, update request status, etc.
            Log::info("Driver matching completed for request {$this->companyRequestId}", [
                'drivers_found' => count($results),
                'top_score' => $results[0]['final_score'] ?? 0
            ]);

        } catch (\Exception $e) {
            Log::error("Async driver matching failed for request {$this->companyRequestId}: " . $e->getMessage());
            throw $e;
        }
    }
}

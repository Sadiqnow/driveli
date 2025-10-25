<?php

namespace App\Jobs;

use App\Models\CompanyRequest;
use App\Services\MatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MatchDriversJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companyRequest;

    public function __construct(CompanyRequest $companyRequest)
    {
        $this->companyRequest = $companyRequest;
    }

    public function handle(MatchingService $matchingService)
    {
        try {
            Log::info("Starting driver matching for request ID: {$this->companyRequest->id}");

            // Find matches for the request
            $matches = $matchingService->findMatchesForRequest($this->companyRequest);

            if (empty($matches)) {
                Log::info("No matches found for request ID: {$this->companyRequest->id}");
                return;
            }

            // Create match records
            foreach ($matches as $match) {
                $this->companyRequest->matches()->create([
                    'driver_id' => $match['driver']->id,
                    'match_score' => $match['match_score'],
                    'match_reasons' => $match['reasons'],
                    'status' => 'pending',
                ]);
            }

            Log::info("Created " . count($matches) . " matches for request ID: {$this->companyRequest->id}");

            // Dispatch notification job for the company
            SendNotificationJob::dispatch(
                $this->companyRequest->company,
                'matches_found',
                [
                    'request_id' => $this->companyRequest->id,
                    'matches_count' => count($matches),
                ]
            );

        } catch (\Exception $e) {
            Log::error("Error in MatchDriversJob for request ID {$this->companyRequest->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("MatchDriversJob failed for request ID {$this->companyRequest->id}: " . $exception->getMessage());

        // Notify company about matching failure
        SendNotificationJob::dispatch(
            $this->companyRequest->company,
            'matching_failed',
            [
                'request_id' => $this->companyRequest->id,
                'error' => 'Unable to find suitable drivers at this time.',
            ]
        );
    }
}

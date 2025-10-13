<?php

namespace App\Jobs;

use App\Models\Drivers;
use App\Services\VerificationLoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NINVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driverId;
    protected $ninNumber;
    protected $verificationLogger;

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param string $ninNumber
     * @return void
     */
    public function __construct($driverId, $ninNumber)
    {
        $this->driverId = $driverId;
        $this->ninNumber = $ninNumber;
        $this->verificationLogger = new VerificationLoggerService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $startTime = microtime(true);

            // Call NIMC API (placeholder - replace with actual API endpoint)
            $response = $this->callNIMCAPI($this->ninNumber);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Process the response
            $verificationResult = $this->processNIMCResponse($response, $responseTime);

            // Log the verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'nin_verification',
                'nimc_api',
                $verificationResult
            );

            // Update driver verification status if successful
            if ($verificationResult['status'] === 'completed') {
                $this->updateDriverVerification($verificationResult);
            }

        } catch (\Exception $e) {
            Log::error('NIN Verification Job Failed', [
                'driver_id' => $this->driverId,
                'nin_number' => $this->ninNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Log failed verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'nin_verification',
                'nimc_api',
                [
                    'status' => 'failed',
                    'score' => 0,
                    'error' => $e->getMessage(),
                    'response_time_ms' => 0
                ]
            );
        }
    }

    /**
     * Call NIMC API (placeholder implementation)
     *
     * @param string $ninNumber
     * @return array
     */
    protected function callNIMCAPI($ninNumber)
    {
        // Placeholder for actual NIMC API integration
        // Replace with real API endpoint and authentication

        $apiUrl = config('services.nimc.api_url', 'https://api.nimc.gov.ng/verify');
        $apiKey = config('services.nimc.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'nin' => $ninNumber,
            'verification_type' => 'basic'
        ]);

        return $response->json();
    }

    /**
     * Process NIMC API response
     *
     * @param array $response
     * @param float $responseTime
     * @return array
     */
    protected function processNIMCResponse($response, $responseTime)
    {
        $result = [
            'status' => 'failed',
            'score' => 0,
            'api_response' => $response,
            'response_time_ms' => round($responseTime),
            'response_timestamp' => now(),
            'external_reference_id' => $response['reference_id'] ?? null,
            'expires_at' => Carbon::now()->addMonths(6), // NIMC data typically valid for 6 months
        ];

        if (isset($response['success']) && $response['success']) {
            $result['status'] = 'completed';
            $result['score'] = $this->calculateNINScore($response);
        }

        return $result;
    }

    /**
     * Calculate verification score based on NIMC response
     *
     * @param array $response
     * @return int
     */
    protected function calculateNINScore($response)
    {
        $score = 0;

        // Basic verification (NIN exists)
        if (isset($response['data']['nin'])) {
            $score += 40;
        }

        // Name verification
        if (isset($response['data']['firstname']) && isset($response['data']['lastname'])) {
            $score += 30;
        }

        // Additional data verification (DOB, gender, etc.)
        if (isset($response['data']['birthdate'])) {
            $score += 15;
        }

        if (isset($response['data']['gender'])) {
            $score += 10;
        }

        // Photo verification (if available)
        if (isset($response['data']['photo'])) {
            $score += 5;
        }

        return min($score, 100);
    }

    /**
     * Update driver verification status
     *
     * @param array $verificationResult
     * @return void
     */
    protected function updateDriverVerification($verificationResult)
    {
        $driver = Drivers::find($this->driverId);

        if ($driver) {
            $verificationData = [
                'nin_verification' => [
                    'status' => $verificationResult['status'] === 'completed' ? 'verified' : 'failed',
                    'score' => $verificationResult['score'],
                    'verified_at' => now(),
                    'source' => 'nimc_api',
                    'expires_at' => $verificationResult['expires_at']
                ]
            ];

            // Update through VerificationStatusService
            app(VerificationStatusService::class)->updateDriverVerificationStatus(
                $this->driverId,
                $verificationData
            );
        }
    }
}

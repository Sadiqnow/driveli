<?php

namespace App\Jobs;

use App\Models\Drivers;
use App\Services\VerificationLoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PassportVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driverId;
    protected $passportNumber;
    protected $verificationLogger;

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param string $passportNumber
     * @return void
     */
    public function __construct($driverId, $passportNumber)
    {
        $this->driverId = $driverId;
        $this->passportNumber = $passportNumber;
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

            // Call Immigration API (placeholder - replace with actual API endpoint)
            $response = $this->callImmigrationAPI($this->passportNumber);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Process the response
            $verificationResult = $this->processImmigrationResponse($response, $responseTime);

            // Log the verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'passport_verification',
                'immigration_api',
                $verificationResult
            );

            // Update driver verification status if successful
            if ($verificationResult['status'] === 'completed') {
                $this->updateDriverVerification($verificationResult);
            }

        } catch (\Exception $e) {
            Log::error('Passport Verification Job Failed', [
                'driver_id' => $this->driverId,
                'passport_number' => $this->passportNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Log failed verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'passport_verification',
                'immigration_api',
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
     * Call Immigration API (placeholder implementation)
     *
     * @param string $passportNumber
     * @return array
     */
    protected function callImmigrationAPI($passportNumber)
    {
        // Placeholder for actual Immigration API integration
        // Replace with real API endpoint and authentication

        $apiUrl = config('services.immigration.api_url', 'https://api.immigration.gov.ng/verify-passport');
        $apiKey = config('services.immigration.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'passport_number' => $passportNumber,
            'verification_type' => 'full'
        ]);

        return $response->json();
    }

    /**
     * Process Immigration API response
     *
     * @param array $response
     * @param float $responseTime
     * @return array
     */
    protected function processImmigrationResponse($response, $responseTime)
    {
        $result = [
            'status' => 'failed',
            'score' => 0,
            'api_response' => $response,
            'response_time_ms' => round($responseTime),
            'response_timestamp' => now(),
            'external_reference_id' => $response['reference_id'] ?? null,
            'expires_at' => Carbon::now()->addYears(1), // Passport data typically valid for 1 year
        ];

        if (isset($response['success']) && $response['success']) {
            $result['status'] = 'completed';
            $result['score'] = $this->calculatePassportScore($response);
        }

        return $result;
    }

    /**
     * Calculate verification score based on Immigration response
     *
     * @param array $response
     * @return int
     */
    protected function calculatePassportScore($response)
    {
        $score = 0;

        // Basic passport verification (passport exists and is valid)
        if (isset($response['data']['passport_number'])) {
            $score += 30;
        }

        // Name verification
        if (isset($response['data']['holder_name'])) {
            $score += 25;
        }

        // Passport status (active, not expired/revoked)
        if (isset($response['data']['status']) && $response['data']['status'] === 'active') {
            $score += 20;
        }

        // Expiry date verification (not expired)
        if (isset($response['data']['expiry_date'])) {
            $expiryDate = Carbon::parse($response['data']['expiry_date']);
            if ($expiryDate->isFuture()) {
                $score += 15;
            }
        }

        // Issue date verification (reasonable age)
        if (isset($response['data']['issue_date'])) {
            $issueDate = Carbon::parse($response['data']['issue_date']);
            if ($issueDate->diffInYears(now()) <= 10) { // Issued within last 10 years
                $score += 10;
            }
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
                'passport_verification' => [
                    'status' => $verificationResult['status'] === 'completed' ? 'verified' : 'failed',
                    'score' => $verificationResult['score'],
                    'verified_at' => now(),
                    'source' => 'immigration_api',
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

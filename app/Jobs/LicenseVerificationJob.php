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

class LicenseVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driverId;
    protected $licenseNumber;
    protected $verificationLogger;

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param string $licenseNumber
     * @return void
     */
    public function __construct($driverId, $licenseNumber)
    {
        $this->driverId = $driverId;
        $this->licenseNumber = $licenseNumber;
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

            // Call FRSC API (placeholder - replace with actual API endpoint)
            $response = $this->callFRSCAPI($this->licenseNumber);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Process the response
            $verificationResult = $this->processFRSCResponse($response, $responseTime);

            // Log the verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'license_verification',
                'frsc_api',
                $verificationResult
            );

            // Update driver verification status if successful
            if ($verificationResult['status'] === 'completed') {
                $this->updateDriverVerification($verificationResult);
            }

        } catch (\Exception $e) {
            Log::error('License Verification Job Failed', [
                'driver_id' => $this->driverId,
                'license_number' => $this->licenseNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Log failed verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'license_verification',
                'frsc_api',
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
     * Call FRSC API (placeholder implementation)
     *
     * @param string $licenseNumber
     * @return array
     */
    protected function callFRSCAPI($licenseNumber)
    {
        // Placeholder for actual FRSC API integration
        // Replace with real API endpoint and authentication

        $apiUrl = config('services.frsc.api_url', 'https://api.frsc.gov.ng/verify-license');
        $apiKey = config('services.frsc.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'license_number' => $licenseNumber,
            'verification_type' => 'full'
        ]);

        return $response->json();
    }

    /**
     * Process FRSC API response
     *
     * @param array $response
     * @param float $responseTime
     * @return array
     */
    protected function processFRSCResponse($response, $responseTime)
    {
        $result = [
            'status' => 'failed',
            'score' => 0,
            'api_response' => $response,
            'response_time_ms' => round($responseTime),
            'response_timestamp' => now(),
            'external_reference_id' => $response['reference_id'] ?? null,
            'expires_at' => Carbon::now()->addMonths(3), // License data typically valid for 3 months
        ];

        if (isset($response['success']) && $response['success']) {
            $result['status'] = 'completed';
            $result['score'] = $this->calculateLicenseScore($response);
        }

        return $result;
    }

    /**
     * Calculate verification score based on FRSC response
     *
     * @param array $response
     * @return int
     */
    protected function calculateLicenseScore($response)
    {
        $score = 0;

        // Basic license verification (license exists and is valid)
        if (isset($response['data']['license_number'])) {
            $score += 30;
        }

        // Name verification
        if (isset($response['data']['holder_name'])) {
            $score += 25;
        }

        // License status (active, not suspended/revoked)
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

        // License class verification
        if (isset($response['data']['license_class'])) {
            $score += 10;
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
                'license_verification' => [
                    'status' => $verificationResult['status'] === 'completed' ? 'verified' : 'failed',
                    'score' => $verificationResult['score'],
                    'verified_at' => now(),
                    'source' => 'frsc_api',
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

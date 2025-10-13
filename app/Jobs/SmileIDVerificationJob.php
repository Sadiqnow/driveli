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

class SmileIDVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driverId;
    protected $selfieImage;
    protected $idImage;
    protected $verificationLogger;

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param string $selfieImage Base64 encoded selfie image
     * @param string $idImage Base64 encoded ID image
     * @return void
     */
    public function __construct($driverId, $selfieImage, $idImage)
    {
        $this->driverId = $driverId;
        $this->selfieImage = $selfieImage;
        $this->idImage = $idImage;
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

            // Call SmileID API (placeholder - replace with actual SDK integration)
            $response = $this->callSmileIDAPI($this->selfieImage, $this->idImage);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Process the response
            $verificationResult = $this->processSmileIDResponse($response, $responseTime);

            // Log the verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'biometric_verification',
                'smile_id_sdk',
                $verificationResult
            );

            // Update driver verification status if successful
            if ($verificationResult['status'] === 'completed') {
                $this->updateDriverVerification($verificationResult);
            }

        } catch (\Exception $e) {
            Log::error('SmileID Verification Job Failed', [
                'driver_id' => $this->driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Log failed verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'biometric_verification',
                'smile_id_sdk',
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
     * Call SmileID API/SDK (placeholder implementation)
     *
     * @param string $selfieImage
     * @param string $idImage
     * @return array
     */
    protected function callSmileIDAPI($selfieImage, $idImage)
    {
        // Placeholder for actual SmileID SDK integration
        // Replace with real SDK implementation

        $apiUrl = config('services.smile_id.api_url', 'https://api.smileidentity.com/v2/verify');
        $partnerId = config('services.smile_id.partner_id');
        $apiKey = config('services.smile_id.api_key');
        $sidServer = config('services.smile_id.sid_server', 0); // 0 for sandbox, 1 for production

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'partner_id' => $partnerId,
            'api_key' => $apiKey,
            'sid_server' => $sidServer,
            'product' => 'biometric_kyc',
            'source_sdk' => 'rest_api',
            'source_sdk_version' => '1.0.0',
            'callback_url' => config('app.url') . '/api/smile-id/callback',
            'partner_params' => [
                'job_id' => 'job_' . $this->driverId . '_' . time(),
                'job_type' => 1, // 1 for biometric KYC
                'user_id' => 'user_' . $this->driverId
            ],
            'id_info' => [
                'country' => 'NG', // Nigeria
                'id_type' => 'NIN', // National Identity Number
                'id_number' => '', // Will be filled from driver data if available
                'entered' => false
            ],
            'images' => [
                [
                    'image_type_id' => 0, // Selfie image
                    'image' => $selfieImage,
                    'file_name' => 'selfie.jpg'
                ],
                [
                    'image_type_id' => 3, // ID card image
                    'image' => $idImage,
                    'file_name' => 'id_card.jpg'
                ]
            ]
        ]);

        return $response->json();
    }

    /**
     * Process SmileID API response
     *
     * @param array $response
     * @param float $responseTime
     * @return array
     */
    protected function processSmileIDResponse($response, $responseTime)
    {
        $result = [
            'status' => 'failed',
            'score' => 0,
            'api_response' => $response,
            'response_time_ms' => round($responseTime),
            'response_timestamp' => now(),
            'external_reference_id' => $response['partner_params']['job_id'] ?? null,
            'expires_at' => Carbon::now()->addMonths(6), // Biometric data typically valid for 6 months
        ];

        if (isset($response['success']) && $response['success']) {
            $result['status'] = 'completed';
            $result['score'] = $this->calculateSmileIDScore($response);
        }

        return $result;
    }

    /**
     * Calculate verification score based on SmileID response
     *
     * @param array $response
     * @return int
     */
    protected function calculateSmileIDScore($response)
    {
        $score = 0;

        // Basic verification success
        if (isset($response['success']) && $response['success']) {
            $score += 20;
        }

        // Actions completed (image processing, etc.)
        if (isset($response['actions']) && is_array($response['actions'])) {
            $score += 15;
        }

        // Result codes (lower is better)
        if (isset($response['result_code'])) {
            $resultCode = $response['result_code'];
            if ($resultCode === '0810') { // Approved
                $score += 30;
            } elseif (in_array($resultCode, ['0811', '0812'])) { // Provisional approval
                $score += 20;
            }
        }

        // Confidence value (if available)
        if (isset($response['confidence_value'])) {
            $confidence = (float) $response['confidence_value'];
            $score += min(20, $confidence * 0.2); // Max 20 points for confidence
        }

        // Partner parameters validation
        if (isset($response['partner_params'])) {
            $score += 10;
        }

        // ID number validation
        if (isset($response['id_number']) && !empty($response['id_number'])) {
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
                'biometric_verification' => [
                    'status' => $verificationResult['status'] === 'completed' ? 'verified' : 'failed',
                    'score' => $verificationResult['score'],
                    'verified_at' => now(),
                    'source' => 'smile_id_sdk',
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

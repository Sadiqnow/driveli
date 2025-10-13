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

class FaceIDVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driverId;
    protected $faceImage;
    protected $verificationLogger;

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param string $faceImage Base64 encoded face image
     * @return void
     */
    public function __construct($driverId, $faceImage)
    {
        $this->driverId = $driverId;
        $this->faceImage = $faceImage;
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

            // Call local face dataset API (placeholder - replace with actual implementation)
            $response = $this->callFaceIDAPI($this->faceImage);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Process the response
            $verificationResult = $this->processFaceIDResponse($response, $responseTime);

            // Log the verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'facial_recognition',
                'local_face_dataset',
                $verificationResult
            );

            // Update driver verification status if successful
            if ($verificationResult['status'] === 'completed') {
                $this->updateDriverVerification($verificationResult);
            }

        } catch (\Exception $e) {
            Log::error('FaceID Verification Job Failed', [
                'driver_id' => $this->driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Log failed verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'facial_recognition',
                'local_face_dataset',
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
     * Call local face dataset API (placeholder implementation)
     *
     * @param string $faceImage
     * @return array
     */
    protected function callFaceIDAPI($faceImage)
    {
        // Placeholder for actual face recognition API integration
        // This could be a local ML model, AWS Rekognition, Google Vision, etc.

        $apiUrl = config('services.face_id.api_url', 'https://api.face-recognition.local/verify');
        $apiKey = config('services.face_id.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'image' => $faceImage,
            'verification_type' => 'face_match',
            'driver_id' => $this->driverId,
            'dataset' => 'driver_faces'
        ]);

        return $response->json();
    }

    /**
     * Process face ID API response
     *
     * @param array $response
     * @param float $responseTime
     * @return array
     */
    protected function processFaceIDResponse($response, $responseTime)
    {
        $result = [
            'status' => 'failed',
            'score' => 0,
            'api_response' => $response,
            'response_time_ms' => round($responseTime),
            'response_timestamp' => now(),
            'external_reference_id' => $response['request_id'] ?? null,
            'expires_at' => Carbon::now()->addMonths(3), // Face data typically valid for 3 months
        ];

        if (isset($response['success']) && $response['success']) {
            $result['status'] = 'completed';
            $result['score'] = $this->calculateFaceIDScore($response);
        }

        return $result;
    }

    /**
     * Calculate verification score based on face ID response
     *
     * @param array $response
     * @return int
     */
    protected function calculateFaceIDScore($response)
    {
        $score = 0;

        // Basic face detection
        if (isset($response['face_detected']) && $response['face_detected']) {
            $score += 20;
        }

        // Face quality score
        if (isset($response['face_quality'])) {
            $quality = (float) $response['face_quality'];
            $score += min(20, $quality * 0.2); // Max 20 points for quality
        }

        // Liveness detection (if available)
        if (isset($response['liveness_confidence'])) {
            $liveness = (float) $response['liveness_confidence'];
            $score += min(15, $liveness * 0.15); // Max 15 points for liveness
        }

        // Face matching confidence
        if (isset($response['match_confidence'])) {
            $matchConfidence = (float) $response['match_confidence'];
            $score += min(30, $matchConfidence * 0.3); // Max 30 points for match confidence
        }

        // Anti-spoofing checks
        if (isset($response['anti_spoofing_passed']) && $response['anti_spoofing_passed']) {
            $score += 10;
        }

        // Multiple face detection (should be only one face)
        if (isset($response['face_count']) && $response['face_count'] === 1) {
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
                'facial_recognition' => [
                    'status' => $verificationResult['status'] === 'completed' ? 'verified' : 'failed',
                    'score' => $verificationResult['score'],
                    'verified_at' => now(),
                    'source' => 'local_face_dataset',
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

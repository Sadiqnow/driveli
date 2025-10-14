<?php

namespace App\Jobs;

use App\Models\Drivers;
use App\Services\VerificationLoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeviceVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driverId;
    protected $deviceInfo;
    protected $verificationLogger;

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param array $deviceInfo Device information (IP, user agent, location, etc.)
     * @return void
     */
    public function __construct($driverId, $deviceInfo)
    {
        $this->driverId = $driverId;
        $this->deviceInfo = $deviceInfo;
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

            // Perform device verification (internal tracking service)
            $verificationResult = $this->performDeviceVerification($this->deviceInfo);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Add response time to result
            $verificationResult['response_time_ms'] = round($responseTime);
            $verificationResult['response_timestamp'] = now();

            // Log the verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'device_verification',
                'internal_tracking_service',
                $verificationResult
            );

            // Update driver verification status if successful
            if ($verificationResult['status'] === 'completed') {
                $this->updateDriverVerification($verificationResult);
            }

        } catch (\Exception $e) {
            Log::error('Device Verification Job Failed', [
                'driver_id' => $this->driverId,
                'device_info' => $this->deviceInfo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Log failed verification
            $this->verificationLogger->logVerification(
                $this->driverId,
                'device_verification',
                'internal_tracking_service',
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
     * Perform device verification using internal tracking service
     *
     * @param array $deviceInfo
     * @return array
     */
    protected function performDeviceVerification($deviceInfo)
    {
        $result = [
            'status' => 'failed',
            'score' => 0,
            'api_response' => $deviceInfo,
            'external_reference_id' => 'device_' . $this->driverId . '_' . time(),
            'expires_at' => Carbon::now()->addDays(30), // Device verification valid for 30 days
        ];

        try {
            $score = $this->calculateDeviceScore($deviceInfo);

            // Check for suspicious activity
            $isSuspicious = $this->checkForSuspiciousActivity($deviceInfo);

            if (!$isSuspicious && $score >= 60) {
                $result['status'] = 'completed';
                $result['score'] = $score;
            } elseif ($isSuspicious) {
                $result['status'] = 'failed';
                $result['score'] = max(0, $score - 30); // Reduce score for suspicious activity
                $result['error'] = 'Suspicious device activity detected';
            } else {
                $result['status'] = 'completed';
                $result['score'] = $score;
            }

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Calculate device verification score
     *
     * @param array $deviceInfo
     * @return int
     */
    protected function calculateDeviceScore($deviceInfo)
    {
        $score = 0;

        // IP address validation (not from known VPN/proxy ranges)
        if (isset($deviceInfo['ip_address'])) {
            if (!$this->isFromVPN($deviceInfo['ip_address'])) {
                $score += 25;
            }
        }

        // User agent consistency
        if (isset($deviceInfo['user_agent'])) {
            if ($this->isValidUserAgent($deviceInfo['user_agent'])) {
                $score += 20;
            }
        }

        // Location consistency (IP location matches claimed location)
        if (isset($deviceInfo['ip_location']) && isset($deviceInfo['claimed_location'])) {
            if ($this->locationsMatch($deviceInfo['ip_location'], $deviceInfo['claimed_location'])) {
                $score += 20;
            }
        }

        // Device fingerprint consistency
        if (isset($deviceInfo['device_fingerprint'])) {
            $score += 15;
        }

        // Browser fingerprinting
        if (isset($deviceInfo['browser_fingerprint'])) {
            $score += 10;
        }

        // Time zone validation
        if (isset($deviceInfo['timezone'])) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Check for suspicious device activity
     *
     * @param array $deviceInfo
     * @return bool
     */
    protected function checkForSuspiciousActivity($deviceInfo)
    {
        // Check if IP is from known VPN services
        if (isset($deviceInfo['ip_address']) && $this->isFromVPN($deviceInfo['ip_address'])) {
            return true;
        }

        // Check for multiple failed login attempts from this device
        $recentFailures = DB::table('activity_logs')
            ->where('driver_id', $this->driverId)
            ->where('activity_type', 'login_failed')
            ->where('ip_address', $deviceInfo['ip_address'] ?? null)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentFailures >= 3) {
            return true;
        }

        // Check for unusual login times
        if (isset($deviceInfo['login_time'])) {
            $loginHour = Carbon::parse($deviceInfo['login_time'])->hour;
            if ($loginHour < 5 || $loginHour > 23) { // Unusual hours
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP address is from a known VPN
     *
     * @param string $ipAddress
     * @return bool
     */
    protected function isFromVPN($ipAddress)
    {
        // Placeholder - in real implementation, check against VPN IP ranges
        // This could integrate with services like IPHub, VPNAPI, etc.
        $vpnRanges = config('services.device_verification.vpn_ranges', []);

        foreach ($vpnRanges as $range) {
            if ($this->ipInRange($ipAddress, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user agent is valid
     *
     * @param string $userAgent
     * @return bool
     */
    protected function isValidUserAgent($userAgent)
    {
        // Basic validation - check for common browser signatures
        $validPatterns = [
            '/Mozilla\/.*Chrome\/.*/i',
            '/Mozilla\/.*Firefox\/.*/i',
            '/Mozilla\/.*Safari\/.*/i',
            '/Mozilla\/.*Edge\/.*/i',
        ];

        foreach ($validPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if locations match (within reasonable distance)
     *
     * @param array $ipLocation
     * @param array $claimedLocation
     * @return bool
     */
    protected function locationsMatch($ipLocation, $claimedLocation)
    {
        if (!isset($ipLocation['lat']) || !isset($ipLocation['lng']) ||
            !isset($claimedLocation['lat']) || !isset($claimedLocation['lng'])) {
            return false;
        }

        $distance = $this->calculateDistance(
            $ipLocation['lat'], $ipLocation['lng'],
            $claimedLocation['lat'], $claimedLocation['lng']
        );

        // Allow up to 50km difference (reasonable for mobile users)
        return $distance <= 50;
    }

    /**
     * Calculate distance between two coordinates in kilometers
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float
     */
    protected function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if IP is in given range
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    protected function ipInRange($ip, $range)
    {
        // Simple CIDR check - in production, use proper IP range libraries
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $mask) = explode('/', $range);
        return $this->ipInSubnet($ip, $subnet, $mask);
    }

    /**
     * Check if IP is in subnet
     *
     * @param string $ip
     * @param string $subnet
     * @param int $mask
     * @return bool
     */
    protected function ipInSubnet($ip, $subnet, $mask)
    {
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $mask);

        return ($ip & $mask) === ($subnet & $mask);
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
                'device_verification' => [
                    'status' => $verificationResult['status'] === 'completed' ? 'verified' : 'failed',
                    'score' => $verificationResult['score'],
                    'verified_at' => now(),
                    'source' => 'internal_tracking_service',
                    'expires_at' => $verificationResult['expires_at']
                ]
            ];

            // Update through VerificationStatusService
            app(\App\Services\VerificationStatusService::class)->updateDriverVerificationStatus(
                $this->driverId,
                $verificationData
            );
        }
    }
}

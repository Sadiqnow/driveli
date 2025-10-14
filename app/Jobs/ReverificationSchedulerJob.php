<?php

namespace App\Jobs;

use App\Models\Drivers;
use App\Models\Verification;
use App\Services\VerificationLoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReverificationSchedulerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('Starting reverification scheduler job');

            // Find verifications that need reverification
            $expiredVerifications = $this->getExpiredVerifications();
            $failedVerifications = $this->getFailedVerifications();

            $totalScheduled = 0;

            // Process expired verifications
            foreach ($expiredVerifications as $verification) {
                if ($this->scheduleReverification($verification)) {
                    $totalScheduled++;
                }
            }

            // Process failed verifications (retry logic)
            foreach ($failedVerifications as $verification) {
                if ($this->scheduleFailedReverification($verification)) {
                    $totalScheduled++;
                }
            }

            Log::info('Reverification scheduler completed', [
                'expired_verifications_processed' => count($expiredVerifications),
                'failed_verifications_processed' => count($failedVerifications),
                'total_reverifications_scheduled' => $totalScheduled
            ]);

        } catch (\Exception $e) {
            Log::error('Reverification scheduler job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get verifications that have expired
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getExpiredVerifications()
    {
        return Verification::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereIn('status', ['completed', 'approved']) // Handle both status types
            ->where('requires_reverification', false)
            ->where(function ($query) {
                $query->whereNull('last_reverification_check')
                      ->orWhere('last_reverification_check', '<', now()->subDays(1));
            }) // Don't check too frequently
            ->get();
    }

    /**
     * Get failed verifications that can be retried
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getFailedVerifications()
    {
        return Verification::where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(30)) // Only retry recent failures
            ->whereRaw('(SELECT COUNT(*) FROM verifications v2
                         WHERE v2.verifiable_id = verifications.verifiable_id
                         AND v2.verifiable_type = verifications.verifiable_type
                         AND v2.type = verifications.type
                         AND v2.verification_source = verifications.verification_source
                         AND v2.status = "completed") = 0') // No successful verification of same type exists
            ->get();
    }

    /**
     * Schedule reverification for an expired verification
     *
     * @param Verification $verification
     * @return bool
     */
    protected function scheduleReverification(Verification $verification)
    {
        try {
            // Mark verification as requiring reverification
            $verification->update([
                'requires_reverification' => true,
                'last_reverification_check' => now(),
            ]);

            // Update driver verification status
            $this->updateDriverReverificationStatus($verification->verifiable_id);

            // Schedule the appropriate verification job based on type
            $this->scheduleVerificationJob($verification);

            Log::info('Reverification scheduled for expired verification', [
                'verification_id' => $verification->id,
                'driver_id' => $verification->verifiable_id,
                'type' => $verification->type,
                'source' => $verification->verification_source,
                'expired_at' => $verification->expires_at
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to schedule reverification', [
                'verification_id' => $verification->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Schedule reverification for a failed verification
     *
     * @param Verification $verification
     * @return bool
     */
    protected function scheduleFailedReverification(Verification $verification)
    {
        try {
            // Check retry count to avoid infinite loops
            $retryCount = Verification::where('verifiable_id', $verification->verifiable_id)
                ->where('verifiable_type', $verification->verifiable_type)
                ->where('type', $verification->type)
                ->where('verification_source', $verification->verification_source)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            if ($retryCount >= 3) { // Max 3 retries per week
                Log::info('Maximum retry attempts reached for failed verification', [
                    'verification_id' => $verification->id,
                    'driver_id' => $verification->verifiable_id,
                    'type' => $verification->type
                ]);
                return false;
            }

            // Schedule the verification job
            $this->scheduleVerificationJob($verification);

            Log::info('Retry scheduled for failed verification', [
                'verification_id' => $verification->id,
                'driver_id' => $verification->verifiable_id,
                'type' => $verification->type,
                'retry_count' => $retryCount + 1
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to schedule failed verification retry', [
                'verification_id' => $verification->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Schedule the appropriate verification job based on verification type
     *
     * @param Verification $verification
     * @return void
     */
    protected function scheduleVerificationJob(Verification $verification)
    {
        $driver = Drivers::find($verification->verifiable_id);

        if (!$driver) {
            Log::warning('Driver not found for verification scheduling', [
                'driver_id' => $verification->verifiable_id,
                'verification_id' => $verification->id
            ]);
            return;
        }

        switch ($verification->type) {
            case 'nin_verification':
                if ($driver->nin_number) {
                    NINVerificationJob::dispatch($verification->verifiable_id, $driver->nin_number)
                        ->delay(now()->addMinutes(5)); // Small delay to prevent overwhelming APIs
                }
                break;

            case 'license_verification':
                if ($driver->license_number) {
                    LicenseVerificationJob::dispatch($verification->verifiable_id, $driver->license_number)
                        ->delay(now()->addMinutes(5));
                }
                break;

            case 'biometric_verification':
                // For biometric reverification, we'd need to trigger the KYC flow again
                // This might require sending a notification to the driver
                $this->notifyDriverForBiometricReverification($driver);
                break;

            case 'facial_recognition':
                // Similar to biometric, may need driver interaction
                $this->notifyDriverForBiometricReverification($driver);
                break;

            case 'passport_verification':
                if ($driver->passport_number) {
                    PassportVerificationJob::dispatch($verification->verifiable_id, $driver->passport_number)
                        ->delay(now()->addMinutes(5));
                }
                break;

            case 'device_verification':
                // Device verification can be done automatically with current session data
                // This would require access to current device info
                $this->scheduleDeviceReverification($verification->verifiable_id);
                break;

            default:
                Log::warning('Unknown verification type for scheduling', [
                    'type' => $verification->type,
                    'verification_id' => $verification->id
                ]);
        }
    }

    /**
     * Update driver verification status to indicate reverification needed
     *
     * @param int $driverId
     * @return void
     */
    protected function updateDriverReverificationStatus($driverId)
    {
        $driver = Drivers::find($driverId);

        if ($driver) {
            // Update verification status to indicate reverification needed
            $driver->update([
                'verification_status' => 'requires_reverification',
                'reverification_required_at' => now(),
            ]);

            // Send notification to driver
            app(\App\Services\NotificationService::class)->sendKycNotification(
                $driver,
                'Some of your verifications have expired and need to be updated. Please complete the reverification process.',
                [
                    'driver_id' => $driverId,
                    'reason' => 'expired_verifications',
                    'action_url' => route('driver.kyc.step1')
                ]
            );
        }
    }

    /**
     * Notify driver for biometric reverification (requires user interaction)
     *
     * @param Drivers $driver
     * @return void
     */
    protected function notifyDriverForBiometricReverification($driver)
    {
        app(\App\Services\NotificationService::class)->sendKycNotification(
            $driver,
            'Your biometric verification has expired. Please complete the verification process again through your dashboard.',
            [
                'driver_id' => $driver->id,
                'action_url' => route('driver.kyc.step1'), // Assuming this route exists
                'reason' => 'expired_biometric'
            ]
        );
    }

    /**
     * Schedule device reverification
     *
     * @param int $driverId
     * @return void
     */
    protected function scheduleDeviceReverification($driverId)
    {
        // Get last known device info from activity logs
        $lastActivity = DB::table('activity_logs')
            ->where('driver_id', $driverId)
            ->whereNotNull('ip_address')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastActivity) {
            $deviceInfo = [
                'ip_address' => $lastActivity->ip_address,
                'user_agent' => $lastActivity->user_agent,
                'login_time' => $lastActivity->created_at,
            ];

            DeviceVerificationJob::dispatch($driverId, $deviceInfo)
                ->delay(now()->addMinutes(5));
        }
    }
}

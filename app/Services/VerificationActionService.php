<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationActionService
{
    /**
     * Approve driver verification
     */
    public function approveVerification(int $driverId, array $data): array
    {
        try {
            DB::beginTransaction();

            $driver = Driver::findOrFail($driverId);

            // Override score if provided
            $finalScore = $data['override_score'] ?? $driver->overall_verification_score;

            // Update verification status
            $verificationData = [
                'manual_review' => [
                    'status' => 'verified',
                    'score' => $finalScore,
                    'verified_at' => now(),
                    'verified_by' => auth()->user()->name ?? 'Admin',
                    'notes' => $data['notes'] ?? null
                ]
            ];

            $result = app(VerificationStatusService::class)->updateDriverVerificationStatus($driverId, $verificationData);

            // Log the manual approval
            $this->logVerificationAction($driverId, 'manual_approval', $finalScore, $verificationData, $data['notes'] ?? null);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Driver verification approved successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Driver verification approval failed', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to approve verification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject driver verification
     */
    public function rejectVerification(int $driverId, array $data): array
    {
        try {
            DB::beginTransaction();

            $driver = Driver::findOrFail($driverId);

            // Update verification status to failed
            $verificationData = [
                'manual_review' => [
                    'status' => 'failed',
                    'score' => 0,
                    'verified_at' => now(),
                    'verified_by' => auth()->user()->name ?? 'Admin',
                    'rejection_reason' => $data['rejection_reason']
                ]
            ];

            $result = app(VerificationStatusService::class)->updateDriverVerificationStatus($driverId, $verificationData);

            // Log the manual rejection
            $this->logVerificationAction($driverId, 'manual_rejection', 0, $verificationData, $data['rejection_reason']);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Driver verification rejected'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Driver verification rejection failed', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject verification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk approve verifications
     */
    public function bulkApproveVerifications(array $driverIds, ?string $notes = null): array
    {
        $successCount = 0;
        $failureCount = 0;

        foreach ($driverIds as $driverId) {
            try {
                $result = $this->approveVerification($driverId, [
                    'notes' => $notes ?? 'Bulk approved',
                    'override_score' => 85 // Default bulk approval score
                ]);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $failureCount++;
                Log::error('Bulk approval failed for driver', [
                    'driver_id' => $driverId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'success' => true,
            'message' => "Bulk approval completed: {$successCount} approved, {$failureCount} failed",
            'success_count' => $successCount,
            'failure_count' => $failureCount
        ];
    }

    /**
     * Retry failed verification
     */
    public function retryVerification(int $driverId): array
    {
        $result = app(VerificationStatusService::class)->retryFailedVerification($driverId);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Verification retry initiated'
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['error']
            ];
        }
    }

    /**
     * Log verification action
     */
    private function logVerificationAction(int $driverId, string $type, float $score, array $verificationData, ?string $notes): void
    {
        DB::table('driver_verifications')->insert([
            'driver_id' => $driverId,
            'verification_type' => $type,
            'status' => 'completed',
            'verification_score' => $score,
            'verification_data' => json_encode($verificationData),
            'notes' => $notes,
            'verified_by' => auth()->user()->name ?? 'Admin',
            'verified_at' => now(),
            'attempt_count' => 1,
            'last_attempt_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}

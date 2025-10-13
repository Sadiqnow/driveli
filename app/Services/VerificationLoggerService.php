<?php

namespace App\Services;

use App\Models\Verification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationLoggerService
{
    /**
     * Log a verification attempt
     *
     * @param int $driverId
     * @param string $verificationType
     * @param string $verificationSource
     * @param array $result
     * @return Verification
     */
    public function logVerification($driverId, $verificationType, $verificationSource, $result)
    {
        try {
            $verification = Verification::create([
                'driver_id' => $driverId,
                'type' => $verificationType,
                'verification_source' => $verificationSource,
                'status' => $result['status'],
                'score' => $result['score'] ?? 0,
                'api_response' => $result['api_response'] ?? null,
                'response_timestamp' => $result['response_timestamp'] ?? now(),
                'response_time_ms' => $result['response_time_ms'] ?? 0,
                'external_reference_id' => $result['external_reference_id'] ?? null,
                'expires_at' => $result['expires_at'] ?? null,
                'notes' => $result['error'] ?? null,
            ]);

            Log::info('Verification logged successfully', [
                'verification_id' => $verification->id,
                'driver_id' => $driverId,
                'type' => $verificationType,
                'source' => $verificationSource,
                'status' => $result['status'],
                'score' => $result['score'] ?? 0
            ]);

            return $verification;

        } catch (\Exception $e) {
            Log::error('Failed to log verification', [
                'driver_id' => $driverId,
                'type' => $verificationType,
                'source' => $verificationSource,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get verification history for a driver
     *
     * @param int $driverId
     * @param string|null $type
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVerificationHistory($driverId, $type = null, $limit = 50)
    {
        $query = Verification::where('driver_id', $driverId)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get latest verification for a specific type and source
     *
     * @param int $driverId
     * @param string $type
     * @param string $source
     * @return Verification|null
     */
    public function getLatestVerification($driverId, $type, $source)
    {
        return Verification::where('driver_id', $driverId)
            ->where('type', $type)
            ->where('verification_source', $source)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Check if a verification is still valid (not expired)
     *
     * @param Verification $verification
     * @return bool
     */
    public function isVerificationValid(Verification $verification)
    {
        if (!$verification->expires_at) {
            return true; // No expiration set
        }

        return $verification->expires_at->isFuture();
    }

    /**
     * Get verification statistics for a driver
     *
     * @param int $driverId
     * @return array
     */
    public function getVerificationStats($driverId)
    {
        $stats = Verification::where('driver_id', $driverId)
            ->selectRaw('
                COUNT(*) as total_verifications,
                COUNT(CASE WHEN status = "completed" THEN 1 END) as successful_verifications,
                COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_verifications,
                AVG(score) as average_score,
                MAX(created_at) as last_verification_date
            ')
            ->first();

        return [
            'total_verifications' => $stats->total_verifications ?? 0,
            'successful_verifications' => $stats->successful_verifications ?? 0,
            'failed_verifications' => $stats->failed_verifications ?? 0,
            'success_rate' => $stats->total_verifications > 0
                ? round(($stats->successful_verifications / $stats->total_verifications) * 100, 2)
                : 0,
            'average_score' => round($stats->average_score ?? 0, 2),
            'last_verification_date' => $stats->last_verification_date,
        ];
    }

    /**
     * Mark verifications as requiring re-verification
     *
     * @param int $driverId
     * @param array $types
     * @return int Number of verifications marked
     */
    public function markForReverification($driverId, array $types = [])
    {
        $query = Verification::where('driver_id', $driverId)
            ->where('status', 'completed');

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        return $query->update([
            'requires_reverification' => true,
            'last_reverification_check' => now(),
        ]);
    }

    /**
     * Clean up old verification records
     *
     * @param int $daysOld
     * @return int Number of records deleted
     */
    public function cleanupOldVerifications($daysOld = 365)
    {
        return Verification::where('created_at', '<', now()->subDays($daysOld))
            ->where('status', '!=', 'completed') // Keep successful verifications longer
            ->delete();
    }
}

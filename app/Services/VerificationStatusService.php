<?php

namespace App\Services;

use App\Models\DriverNormalized;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificationStatusService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function updateDriverVerificationStatus($driverId, $verificationData)
    {
        try {
            DB::beginTransaction();

            $driver = DriverNormalized::findOrFail($driverId);
            
            // Calculate overall verification score
            $overallScore = $this->calculateOverallScore($verificationData);
            
            // Determine verification status based on score and individual results
            $verificationStatus = $this->determineVerificationStatus($verificationData, $overallScore);
            
            // Update driver record
            $driver->update([
                'verification_status' => $verificationStatus,
                'overall_verification_score' => $overallScore,
                'verification_completed_at' => $verificationStatus === 'verified' ? now() : null,
                'verification_summary' => $this->generateVerificationSummary($verificationData, $overallScore)
            ]);

            // Log status change
            $this->logStatusChange($driverId, $verificationStatus, $overallScore, $verificationData);

            // Send notifications
            $this->sendStatusNotification($driver, $verificationStatus, $overallScore);

            DB::commit();

            return [
                'success' => true,
                'status' => $verificationStatus,
                'score' => $overallScore,
                'driver_id' => $driverId
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update driver verification status', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'driver_id' => $driverId
            ];
        }
    }

    protected function calculateOverallScore($verificationData)
    {
        $weights = [
            'nin_verification' => 25,      // 25% weight
            'license_verification' => 20,  // 20% weight
            'bvn_verification' => 20,      // 20% weight
            'document_ocr' => 20,          // 20% weight
            'referee_verification' => 15   // 15% weight
        ];

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($weights as $component => $weight) {
            if (isset($verificationData[$component]) && isset($verificationData[$component]['score'])) {
                $componentScore = (float) $verificationData[$component]['score'];
                $totalScore += $componentScore * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }

    protected function determineVerificationStatus($verificationData, $overallScore)
    {
        // Check for critical failures
        $criticalComponents = ['nin_verification', 'license_verification'];
        foreach ($criticalComponents as $component) {
            if (isset($verificationData[$component])) {
                $status = $verificationData[$component]['status'] ?? 'failed';
                if ($status === 'failed' && $overallScore < 50) {
                    return 'failed';
                }
                if ($status === 'requires_manual_review') {
                    return 'requires_manual_review';
                }
            }
        }

        // Determine status based on overall score
        if ($overallScore >= 85) {
            return 'verified';
        } elseif ($overallScore >= 70) {
            return 'requires_manual_review';
        } elseif ($overallScore >= 50) {
            return 'pending';
        } else {
            return 'failed';
        }
    }

    protected function generateVerificationSummary($verificationData, $overallScore)
    {
        $summary = [
            'overall_score' => $overallScore,
            'completion_date' => now()->toISOString(),
            'components' => [],
            'key_findings' => [],
            'recommendations' => []
        ];

        // Process each verification component
        foreach ($verificationData as $component => $data) {
            if (is_array($data)) {
                $summary['components'][$component] = [
                    'status' => $data['status'] ?? 'unknown',
                    'score' => $data['score'] ?? 0,
                    'last_verified' => $data['verified_at'] ?? null,
                    'issues' => $data['issues'] ?? []
                ];

                // Collect key findings
                if (isset($data['issues']) && !empty($data['issues'])) {
                    $summary['key_findings'] = array_merge(
                        $summary['key_findings'],
                        $data['issues']
                    );
                }

                // Collect recommendations
                if (isset($data['recommendations']) && !empty($data['recommendations'])) {
                    $summary['recommendations'] = array_merge(
                        $summary['recommendations'],
                        $data['recommendations']
                    );
                }
            }
        }

        return $summary;
    }

    protected function logStatusChange($driverId, $status, $score, $verificationData)
    {
        DB::table('driver_verifications')->insert([
            'driver_id' => $driverId,
            'verification_type' => 'status_update',
            'status' => 'completed',
            'verification_score' => $score,
            'verification_data' => json_encode([
                'new_status' => $status,
                'verification_components' => $verificationData,
                'updated_by' => 'system'
            ]),
            'verified_at' => now(),
            'attempt_count' => 1,
            'last_attempt_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    protected function sendStatusNotification($driver, $status, $score)
    {
        $message = $this->getStatusMessage($status, $score);
        
        $this->notificationService->sendDriverNotification(
            $driver->id,
            'Verification Status Update',
            $message,
            'verification_status',
            [
                'status' => $status,
                'score' => $score,
                'driver_id' => $driver->id
            ]
        );

        // Send email notification
        $this->notificationService->sendDriverEmail(
            $driver->email,
            'Driver Verification Status Update',
            'emails.driver-verification-status',
            [
                'driver' => $driver,
                'status' => $status,
                'score' => $score,
                'message' => $message
            ]
        );
    }

    protected function getStatusMessage($status, $score)
    {
        switch ($status) {
            case 'verified':
                return "Congratulations! Your driver verification has been completed successfully with a score of {$score}%. You can now access all driver features.";
            
            case 'requires_manual_review':
                return "Your driver verification is under review. Our team will complete the verification process within 24-48 hours. Current score: {$score}%.";
            
            case 'pending':
                return "Your driver verification is in progress. Some additional information may be required. Current score: {$score}%.";
            
            case 'failed':
                return "Unfortunately, your driver verification could not be completed. Please contact support for assistance. Score: {$score}%.";
            
            default:
                return "Your verification status has been updated. Current score: {$score}%.";
        }
    }

    public function getDriverVerificationDetails($driverId)
    {
        try {
            $driver = DriverNormalized::findOrFail($driverId);

            // Get latest workflow
            $workflow = null;
            if (Schema::hasTable('verification_workflows')) {
                $workflow = DB::table('verification_workflows')
                    ->where('driver_id', $driverId)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            // Get all verification records
            $verifications = collect();
            if (Schema::hasTable('driver_verifications')) {
                $verifications = DB::table('driver_verifications')
                    ->where('driver_id', $driverId)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Get OCR results
            $ocrResults = collect();
            if (Schema::hasTable('document_ocr_results')) {
                $ocrResults = DB::table('document_ocr_results')
                    ->where('driver_id', $driverId)
                    ->get();
            }

            // Get API verification logs
            $apiLogs = collect();
            if (Schema::hasTable('api_verification_logs')) {
                $apiLogs = DB::table('api_verification_logs')
                    ->where('driver_id', $driverId)
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
            }

            // Get referee verifications
            $refereeVerifications = collect();
            if (Schema::hasTable('referee_verifications')) {
                $refereeVerifications = DB::table('referee_verifications')
                    ->where('driver_id', $driverId)
                    ->get();
            }

            return [
                'success' => true,
                'driver' => $driver,
                'workflow' => $workflow,
                'verifications' => $verifications,
                'ocr_results' => $ocrResults,
                'api_logs' => $apiLogs,
                'referee_verifications' => $refereeVerifications,
                'verification_summary' => $driver->verification_summary
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get driver verification details', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function retryFailedVerification($driverId, $verificationTypes = [])
    {
        try {
            $driver = DriverNormalized::findOrFail($driverId);

            if ($driver->verification_status !== 'failed') {
                return [
                    'success' => false,
                    'error' => 'Driver verification is not in failed status'
                ];
            }

            // Reset verification status
            $driver->update([
                'verification_status' => 'pending',
                'verification_started_at' => now(),
                'verification_completed_at' => null
            ]);

            // Mark specific verification types for retry if specified
            if (!empty($verificationTypes)) {
                DB::table('driver_verifications')
                    ->where('driver_id', $driverId)
                    ->whereIn('verification_type', $verificationTypes)
                    ->update([
                        'status' => 'pending',
                        'updated_at' => now()
                    ]);
            }

            Log::info('Driver verification retry initiated', [
                'driver_id' => $driverId,
                'verification_types' => $verificationTypes
            ]);

            return [
                'success' => true,
                'message' => 'Verification retry initiated',
                'driver_id' => $driverId
            ];

        } catch (\Exception $e) {
            Log::error('Failed to retry driver verification', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getVerificationStatistics($dateRange = null)
    {
        try {
            $query = DriverNormalized::query();

            if ($dateRange) {
                $query->whereBetween('verification_started_at', [
                    Carbon::parse($dateRange['start']),
                    Carbon::parse($dateRange['end'])
                ]);
            }

            $stats = [
                'total_drivers' => $query->count(),
                'status_breakdown' => $query->groupBy('verification_status')
                    ->selectRaw('verification_status, count(*) as count')
                    ->pluck('count', 'verification_status')
                    ->toArray(),
                'average_score' => $query->whereNotNull('overall_verification_score')
                    ->avg('overall_verification_score'),
                'completion_times' => []
            ];

            // Calculate average completion times
            $completionTimes = $query->whereNotNull('verification_completed_at')
                ->whereNotNull('verification_started_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, verification_started_at, verification_completed_at)) as avg_hours')
                ->first();

            $stats['completion_times']['average_hours'] = $completionTimes->avg_hours ?? 0;

            return [
                'success' => true,
                'statistics' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get verification statistics', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
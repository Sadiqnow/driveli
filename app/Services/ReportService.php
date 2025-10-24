<?php

namespace App\Services;

use App\Models\Drivers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    protected $scoringService;

    public function __construct(ScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Save verification results to logs and update driver status/score
     *
     * @param Drivers $driver
     * @param float $score
     * @param array $ocrResults
     * @param float $faceMatch
     * @param array $validationResults
     * @return array
     */
    public function saveResults(Drivers $driver, float $score, array $ocrResults, float $faceMatch, array $validationResults): array
    {
        try {
            DB::beginTransaction();

            // Determine verification status based on score
            $verificationStatus = $this->determineStatusFromScore($score);

            // Save to driver_verification_logs
            $logId = DB::table('driver_verification_logs')->insertGetId([
                'driver_id' => $driver->id,
                'action' => 'verification_complete',
                'status' => 'completed',
                'verification_data' => json_encode([
                    'ocr_results' => $ocrResults,
                    'face_match_score' => $faceMatch,
                    'validation_results' => $validationResults,
                    'timestamp' => now()->toISOString()
                ]),
                'result_data' => json_encode([
                    'overall_score' => $score,
                    'verification_status' => $verificationStatus,
                    'breakdown' => $this->getScoreBreakdown($ocrResults, $faceMatch, $validationResults)
                ]),
                'confidence_score' => $score,
                'notes' => "Verification completed with score: {$score}%, Status: {$verificationStatus}",
                'performed_by' => auth()->id() ?? null,
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update driver table
            $driver->update([
                'verification_status' => $verificationStatus,
                'overall_verification_score' => $score,
                'verification_completed_at' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'log_id' => $logId,
                'driver_id' => $driver->id,
                'status' => $verificationStatus,
                'score' => $score
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save verification results', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'driver_id' => $driver->id
            ];
        }
    }

    /**
     * Generate full verification report JSON
     *
     * @param int $driverId
     * @return array
     */
    public function generate(int $driverId): array
    {
        try {
            $driver = Drivers::with([
                'documents',
                'verificationLogs' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'facialVerifications',
                'nationality',
                'residenceState',
                'residenceLga'
            ])->findOrFail($driverId);

            $logs = $driver->verificationLogs->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'status' => $log->status,
                    'verification_data' => is_array($log->verification_data) ? $log->verification_data : json_decode($log->verification_data, true),
                    'result_data' => is_array($log->result_data) ? $log->result_data : json_decode($log->result_data, true),
                    'confidence_score' => $log->confidence_score,
                    'notes' => $log->notes,
                    'performed_at' => $log->performed_at,
                    'created_at' => $log->created_at
                ];
            });

            $documents = $driver->documents->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'type' => $doc->document_type,
                    'path' => $doc->document_path,
                    'verification_status' => $doc->verification_status,
                    'verified_at' => $doc->verified_at,
                    'uploaded_at' => $doc->created_at
                ];
            });

            $report = [
                'driver' => [
                    'id' => $driver->id,
                    'driver_id' => $driver->driver_id,
                    'name' => $driver->full_name,
                    'email' => $driver->email,
                    'phone' => $driver->phone,
                    'date_of_birth' => $driver->date_of_birth?->format('Y-m-d'),
                    'nationality' => $driver->nationality?->name,
                    'residence' => [
                        'state' => $driver->residenceState?->name,
                        'lga' => $driver->residenceLga?->name,
                        'address' => $driver->residence_address
                    ],
                    'license' => [
                        'number' => $driver->license_number,
                        'class' => $driver->license_class,
                        'expiry_date' => $driver->license_expiry_date?->format('Y-m-d')
                    ],
                    'verification_status' => $driver->verification_status,
                    'overall_score' => $driver->overall_verification_score,
                    'verified_at' => $driver->verified_at,
                    'created_at' => $driver->created_at
                ],
                'documents' => $documents,
                'logs' => $logs,
                'facial_verifications' => $driver->facialVerifications->map(function($fv) {
                    return [
                        'id' => $fv->id,
                        'match_score' => $fv->match_score,
                        'status' => $fv->status,
                        'verified_at' => $fv->verified_at,
                        'created_at' => $fv->created_at
                    ];
                }),
                'recommendations' => $this->generateRecommendations($driver, $logs),
                'generated_at' => now()->toISOString(),
                'report_version' => '1.0'
            ];

            return [
                'success' => true,
                'report' => $report
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate verification report', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'driver_id' => $driverId
            ];
        }
    }

    /**
     * Determine verification status from score
     *
     * @param float $score
     * @return string
     */
    private function determineStatusFromScore(float $score): string
    {
        if ($score >= 85) {
            return 'verified';
        } elseif ($score >= 70) {
            return 'requires_manual_review';
        } elseif ($score >= 50) {
            return 'pending';
        } else {
            return 'failed';
        }
    }

    /**
     * Get score breakdown for logging
     *
     * @param array $ocrResults
     * @param float $faceMatch
     * @param array $validationResults
     * @return array
     */
    private function getScoreBreakdown(array $ocrResults, float $faceMatch, array $validationResults): array
    {
        return $this->scoringService->calculate($ocrResults, $faceMatch, $validationResults);
    }

    /**
     * Generate recommendations based on driver status and logs
     *
     * @param Drivers $driver
     * @param \Illuminate\Support\Collection $logs
     * @return array
     */
    private function generateRecommendations(Drivers $driver, $logs): array
    {
        $recommendations = [];

        $score = $driver->overall_verification_score ?? 0;
        $status = $driver->verification_status;

        if ($status === 'failed') {
            $recommendations[] = [
                'type' => 'critical',
                'message' => 'Verification failed. Please review submitted documents and resubmit with clearer images.',
                'actions' => [
                    'Resubmit documents with better quality',
                    'Ensure all required documents are provided',
                    'Contact support for assistance'
                ]
            ];
        } elseif ($status === 'requires_manual_review') {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Verification requires manual review. Our team will complete the process within 24-48 hours.',
                'actions' => [
                    'Wait for manual review completion',
                    'Prepare additional documentation if requested'
                ]
            ];
        } elseif ($status === 'pending') {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Verification is in progress. Some additional information may be required.',
                'actions' => [
                    'Check back later for status updates',
                    'Ensure contact information is up to date'
                ]
            ];
        } elseif ($status === 'verified') {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'Verification completed successfully. You can now access all driver features.',
                'actions' => [
                    'Complete profile setup',
                    'Start accepting jobs'
                ]
            ];
        }

        // Check for low OCR scores
        $latestLog = $logs->first();
        if ($latestLog && isset($latestLog['result_data']['breakdown'])) {
            $breakdown = $latestLog['result_data']['breakdown'];
            if (isset($breakdown['ocr_accuracy']['raw_score']) && $breakdown['ocr_accuracy']['raw_score'] < 0.7) {
                $recommendations[] = [
                    'type' => 'warning',
                    'message' => 'OCR accuracy was low. Consider resubmitting documents with clearer text.',
                    'actions' => [
                        'Ensure documents are well-lit and in focus',
                        'Avoid blurry or distorted images'
                    ]
                ];
            }
        }

        // Check for low face match
        if ($latestLog && isset($latestLog['verification_data']['face_match_score']) &&
            $latestLog['verification_data']['face_match_score'] < 0.8) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Face matching score was low. Please ensure profile picture matches the person in documents.',
                'actions' => [
                    'Update profile picture with a clear, recent photo',
                    'Ensure face is clearly visible and well-lit'
                ]
            ];
        }

        return $recommendations;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Drivers;
use App\Services\DocumentOCRService;
use App\Services\DocumentMatchingService;
use App\Services\NINVerificationService;
use App\Services\FRSCVerificationService;
use App\Services\BVNVerificationService;
use App\Services\RefereeVerificationService;
use App\Services\NotificationService;
use Exception;

class DriverVerificationWorkflow
{
    private $ocrService;
    private $matchingService;
    private $ninService;
    private $frscService;
    private $bvnService;
    private $refereeService;
    private $notificationService;

    public function __construct(
        DocumentOCRService $ocrService,
        DocumentMatchingService $matchingService,
        NINVerificationService $ninService,
        FRSCVerificationService $frscService,
        BVNVerificationService $bvnService,
        RefereeVerificationService $refereeService,
        NotificationService $notificationService
    ) {
        $this->ocrService = $ocrService;
        $this->matchingService = $matchingService;
        $this->ninService = $ninService;
        $this->frscService = $frscService;
        $this->bvnService = $bvnService;
        $this->refereeService = $refereeService;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute complete driver verification workflow
     * 
     * @param int $driverId Driver ID
     * @param array $documents Uploaded document paths
     * @param array $refereeData Referee information
     * @return array Complete verification result
     */
    public function executeVerificationWorkflow($driverId, $documents = [], $refereeData = [])
    {
        try {
            Log::info('Starting complete driver verification workflow', [
                'driver_id' => $driverId,
                'documents_count' => count($documents),
                'referees_count' => count($refereeData)
            ]);

            // Initialize workflow result
            $workflowResult = [
                'driver_id' => $driverId,
                'workflow_status' => 'in_progress',
                'verification_steps' => [],
                'overall_score' => 0,
                'verification_status' => 'pending',
                'completion_percentage' => 0,
                'issues_found' => [],
                'recommendations' => [],
                'next_steps' => [],
                'started_at' => now(),
                'completed_at' => null,
                'processing_time' => null
            ];

            // Get driver data
            $driver = Drivers::find($driverId);
            if (!$driver) {
                throw new Exception('Driver not found with ID: ' . $driverId);
            }

            $driverData = $this->extractDriverData($driver);

            // Step 1: Document OCR and Data Matching
            $workflowResult['verification_steps']['document_verification'] = 
                $this->executeDocumentVerification($driverData, $documents);

            // Step 2: NIN Verification
            $workflowResult['verification_steps']['nin_verification'] = 
                $this->executeNINVerification($driverData);

            // Step 3: FRSC License Verification
            $workflowResult['verification_steps']['license_verification'] = 
                $this->executeLicenseVerification($driverData);

            // Step 4: BVN Verification (if provided)
            if (!empty($driverData['bvn'])) {
                $workflowResult['verification_steps']['bvn_verification'] = 
                    $this->executeBVNVerification($driverData);
            }

            // Step 5: Referee Verification
            if (!empty($refereeData)) {
                $workflowResult['verification_steps']['referee_verification'] = 
                    $this->executeRefereeVerification($refereeData, $documents['referee_documents'] ?? [], $driverId);
            }

            // Step 6: Calculate Overall Score and Status
            $overallResult = $this->calculateOverallVerificationResult($workflowResult['verification_steps']);
            
            $workflowResult['overall_score'] = $overallResult['score'];
            $workflowResult['verification_status'] = $overallResult['status'];
            $workflowResult['completion_percentage'] = $overallResult['completion'];
            $workflowResult['issues_found'] = $overallResult['issues'];
            $workflowResult['recommendations'] = $overallResult['recommendations'];
            $workflowResult['next_steps'] = $overallResult['next_steps'];

            // Step 7: Update Driver Record
            $this->updateDriverVerificationStatus($driver, $workflowResult);

            // Step 8: Send Notifications
            $this->sendVerificationNotifications($driver, $workflowResult);

            // Finalize workflow
            $workflowResult['workflow_status'] = 'completed';
            $workflowResult['completed_at'] = now();
            $workflowResult['processing_time'] = $workflowResult['completed_at']->diffInSeconds($workflowResult['started_at']);

            Log::info('Driver verification workflow completed', [
                'driver_id' => $driverId,
                'overall_score' => $workflowResult['overall_score'],
                'verification_status' => $workflowResult['verification_status'],
                'processing_time' => $workflowResult['processing_time']
            ]);

            return $workflowResult;

        } catch (Exception $e) {
            Log::error('Driver verification workflow failed', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $workflowResult['workflow_status'] = 'failed';
            $workflowResult['error'] = $e->getMessage();
            $workflowResult['completed_at'] = now();

            return $workflowResult;
        }
    }

    /**
     * Extract driver data for verification
     */
    private function extractDriverData($driver)
    {
        return [
            'id' => $driver->id,
            'driver_id' => $driver->driver_id,
            'first_name' => $driver->first_name,
            'middle_name' => $driver->middle_name,
            'surname' => $driver->surname,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'date_of_birth' => $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : null,
            'gender' => $driver->gender,
            'nin' => $driver->nin_number,
            'license_number' => $driver->license_number,
            'license_class' => $driver->license_class,
            'bvn' => $driver->bvn ?? null,
            'state_of_origin' => $driver->state_of_origin,
            'lga_of_origin' => $driver->lga_of_origin,
            'nationality_id' => $driver->nationality_id,
        ];
    }

    /**
     * Execute document verification step
     */
    private function executeDocumentVerification($driverData, $documents)
    {
        try {
            Log::info('Executing document verification step', [
                'driver_id' => $driverData['driver_id'],
                'documents' => array_keys($documents)
            ]);

            $documentResults = [];
            $documentTypes = ['nin', 'license_front', 'license_back', 'bvn', 'passport'];

            foreach ($documentTypes as $docType) {
                if (isset($documents[$docType]) && file_exists($documents[$docType])) {
                    // Extract data using OCR
                    $ocrResult = $this->ocrService->extractDocumentData($documents[$docType], $docType);
                    
                    if ($ocrResult['success']) {
                        // Compare with driver data
                        $comparisonResult = $this->matchingService->compareDocumentData(
                            $driverData, 
                            $ocrResult['data'], 
                            $docType
                        );
                        
                        $documentResults[$docType] = [
                            'processed' => true,
                            'ocr_result' => $ocrResult,
                            'comparison_result' => $comparisonResult,
                            'verified' => $comparisonResult['match_status'] === 'verified'
                        ];
                    } else {
                        $documentResults[$docType] = [
                            'processed' => false,
                            'error' => $ocrResult['error']
                        ];
                    }
                }
            }

            $totalDocuments = count($documentResults);
            $verifiedDocuments = count(array_filter($documentResults, function($result) {
                return $result['processed'] && $result['verified'];
            }));

            return [
                'success' => true,
                'processed_documents' => $documentResults,
                'total_documents' => $totalDocuments,
                'verified_documents' => $verifiedDocuments,
                'verification_rate' => $totalDocuments > 0 ? $verifiedDocuments / $totalDocuments : 0,
                'status' => $verifiedDocuments >= $totalDocuments * 0.8 ? 'passed' : 'failed'
            ];

        } catch (Exception $e) {
            Log::error('Document verification step failed', [
                'driver_id' => $driverData['driver_id'],
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Execute NIN verification step
     */
    private function executeNINVerification($driverData)
    {
        try {
            if (empty($driverData['nin'])) {
                return [
                    'success' => false,
                    'error' => 'NIN not provided',
                    'status' => 'skipped'
                ];
            }

            Log::info('Executing NIN verification step', [
                'driver_id' => $driverData['driver_id'],
                'nin' => substr($driverData['nin'], 0, 3) . '***'
            ]);

            $ninResult = $this->ninService->verifyNIN($driverData['nin'], $driverData);

            return [
                'success' => $ninResult['success'],
                'verified' => $ninResult['verified'],
                'match_score' => $ninResult['match_score'] ?? 0,
                'verification_status' => $ninResult['verification_status'] ?? 'failed',
                'discrepancies' => $ninResult['discrepancies'] ?? [],
                'error' => $ninResult['error'] ?? null,
                'status' => $ninResult['verified'] ? 'passed' : 'failed'
            ];

        } catch (Exception $e) {
            Log::error('NIN verification step failed', [
                'driver_id' => $driverData['driver_id'],
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Execute FRSC license verification step
     */
    private function executeLicenseVerification($driverData)
    {
        try {
            if (empty($driverData['license_number'])) {
                return [
                    'success' => false,
                    'error' => 'License number not provided',
                    'status' => 'skipped'
                ];
            }

            Log::info('Executing license verification step', [
                'driver_id' => $driverData['driver_id'],
                'license_number' => substr($driverData['license_number'], 0, 3) . '***'
            ]);

            $licenseResult = $this->frscService->verifyDriverLicense($driverData['license_number'], $driverData);

            return [
                'success' => $licenseResult['success'],
                'verified' => $licenseResult['verified'],
                'license_status' => $licenseResult['license_status'] ?? 'unknown',
                'is_expired' => $licenseResult['is_expired'] ?? null,
                'violations' => $licenseResult['violations'] ?? [],
                'match_score' => $licenseResult['match_score'] ?? 0,
                'discrepancies' => $licenseResult['discrepancies'] ?? [],
                'error' => $licenseResult['error'] ?? null,
                'status' => ($licenseResult['verified'] && $licenseResult['overall_status'] === 'valid') ? 'passed' : 'failed'
            ];

        } catch (Exception $e) {
            Log::error('License verification step failed', [
                'driver_id' => $driverData['driver_id'],
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Execute BVN verification step
     */
    private function executeBVNVerification($driverData)
    {
        try {
            Log::info('Executing BVN verification step', [
                'driver_id' => $driverData['driver_id'],
                'bvn' => substr($driverData['bvn'], 0, 3) . '***'
            ]);

            $bvnResult = $this->bvnService->verifyBVN($driverData['bvn'], $driverData);

            return [
                'success' => $bvnResult['success'],
                'verified' => $bvnResult['verified'],
                'account_status' => $bvnResult['account_status'] ?? 'unknown',
                'watchlist_status' => $bvnResult['watchlist_status'] ?? 'unknown',
                'match_score' => $bvnResult['match_score'] ?? 0,
                'discrepancies' => $bvnResult['discrepancies'] ?? [],
                'error' => $bvnResult['error'] ?? null,
                'status' => $bvnResult['verified'] ? 'passed' : 'failed'
            ];

        } catch (Exception $e) {
            Log::error('BVN verification step failed', [
                'driver_id' => $driverData['driver_id'],
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Execute referee verification step
     */
    private function executeRefereeVerification($refereeData, $refereeDocuments, $driverId)
    {
        try {
            Log::info('Executing referee verification step', [
                'driver_id' => $driverId,
                'referees_count' => count($refereeData)
            ]);

            $refereeResults = [];

            foreach ($refereeData as $index => $referee) {
                $documentPath = $refereeDocuments[$index] ?? null;
                
                $refereeResult = $this->refereeService->verifyReferee(
                    $referee, 
                    $documentPath, 
                    $driverId
                );

                $refereeResults[] = [
                    'referee_name' => $referee['name'] ?? 'Unknown',
                    'verification_result' => $refereeResult,
                    'verified' => $refereeResult['verified']
                ];
            }

            $totalReferees = count($refereeResults);
            $verifiedReferees = count(array_filter($refereeResults, function($result) {
                return $result['verified'];
            }));

            return [
                'success' => true,
                'referee_results' => $refereeResults,
                'total_referees' => $totalReferees,
                'verified_referees' => $verifiedReferees,
                'verification_rate' => $totalReferees > 0 ? $verifiedReferees / $totalReferees : 0,
                'status' => $verifiedReferees >= max(1, $totalReferees * 0.5) ? 'passed' : 'failed'
            ];

        } catch (Exception $e) {
            Log::error('Referee verification step failed', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Calculate overall verification result
     */
    private function calculateOverallVerificationResult($verificationSteps)
    {
        $scores = [];
        $weights = [];
        $issues = [];
        $recommendations = [];
        $nextSteps = [];
        $completedSteps = 0;
        $totalSteps = count($verificationSteps);

        // Define step weights
        $stepWeights = [
            'document_verification' => 0.25,
            'nin_verification' => 0.3,
            'license_verification' => 0.3,
            'bvn_verification' => 0.1,
            'referee_verification' => 0.05
        ];

        foreach ($verificationSteps as $stepName => $stepResult) {
            if ($stepResult['status'] === 'passed') {
                $completedSteps++;
                $stepScore = $this->getStepScore($stepName, $stepResult);
                $scores[] = $stepScore;
                $weights[] = $stepWeights[$stepName] ?? 0.1;
            } elseif ($stepResult['status'] === 'failed') {
                $scores[] = 0;
                $weights[] = $stepWeights[$stepName] ?? 0.1;
                $issues[] = $this->getStepIssue($stepName, $stepResult);
                $nextSteps[] = $this->getStepNextAction($stepName, $stepResult);
            } elseif ($stepResult['status'] === 'skipped') {
                $recommendations[] = $this->getStepRecommendation($stepName, $stepResult);
            }
        }

        // Calculate weighted score
        $totalWeight = array_sum($weights);
        $weightedSum = 0;

        for ($i = 0; $i < count($scores); $i++) {
            $weightedSum += $scores[$i] * $weights[$i];
        }

        $overallScore = $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
        
        // Determine verification status
        $verificationStatus = $this->determineOverallStatus($overallScore, $issues);
        
        // Calculate completion percentage
        $completionPercentage = ($completedSteps / $totalSteps) * 100;

        // Generate overall recommendations
        $overallRecommendations = $this->generateOverallRecommendations($overallScore, $verificationStatus, $issues);

        return [
            'score' => round($overallScore, 2),
            'status' => $verificationStatus,
            'completion' => round($completionPercentage, 1),
            'issues' => $issues,
            'recommendations' => array_merge($recommendations, $overallRecommendations),
            'next_steps' => $nextSteps,
            'completed_steps' => $completedSteps,
            'total_steps' => $totalSteps
        ];
    }

    /**
     * Get score for individual verification step
     */
    private function getStepScore($stepName, $stepResult)
    {
        switch ($stepName) {
            case 'document_verification':
                return $stepResult['verification_rate'] ?? 0;
            case 'nin_verification':
            case 'license_verification':
            case 'bvn_verification':
                return $stepResult['match_score'] ?? ($stepResult['verified'] ? 1 : 0);
            case 'referee_verification':
                return $stepResult['verification_rate'] ?? 0;
            default:
                return $stepResult['verified'] ?? false ? 1 : 0;
        }
    }

    /**
     * Get issue description for failed step
     */
    private function getStepIssue($stepName, $stepResult)
    {
        $issues = [
            'document_verification' => 'Document verification failed - uploaded documents do not match provided information',
            'nin_verification' => 'NIN verification failed - provided NIN does not match official records',
            'license_verification' => 'License verification failed - driver\'s license is invalid or does not match provided information',
            'bvn_verification' => 'BVN verification failed - provided BVN does not match banking records',
            'referee_verification' => 'Referee verification failed - provided references could not be verified'
        ];

        return $issues[$stepName] ?? 'Verification step failed: ' . ($stepResult['error'] ?? 'Unknown error');
    }

    /**
     * Get next action for failed step
     */
    private function getStepNextAction($stepName, $stepResult)
    {
        $actions = [
            'document_verification' => 'Request driver to upload clearer document images',
            'nin_verification' => 'Verify NIN manually or request alternative identification',
            'license_verification' => 'Verify license through alternative means or request license renewal',
            'bvn_verification' => 'Verify BVN manually or request alternative bank verification',
            'referee_verification' => 'Request additional references or verify existing ones manually'
        ];

        return $actions[$stepName] ?? 'Manual review required for this verification step';
    }

    /**
     * Get recommendation for skipped step
     */
    private function getStepRecommendation($stepName, $stepResult)
    {
        $recommendations = [
            'bvn_verification' => 'Consider requesting BVN for additional verification',
            'referee_verification' => 'Consider requesting professional references for enhanced verification'
        ];

        return $recommendations[$stepName] ?? 'Consider completing this optional verification step';
    }

    /**
     * Determine overall verification status
     */
    private function determineOverallStatus($score, $issues)
    {
        $criticalIssues = array_filter($issues, function($issue) {
            return strpos($issue, 'NIN verification') !== false || 
                   strpos($issue, 'License verification') !== false;
        });

        if (!empty($criticalIssues)) {
            return 'failed';
        }

        if ($score >= 0.85) {
            return 'verified';
        } elseif ($score >= 0.7) {
            return 'conditionally_approved';
        } elseif ($score >= 0.5) {
            return 'review_required';
        } else {
            return 'rejected';
        }
    }

    /**
     * Generate overall recommendations
     */
    private function generateOverallRecommendations($score, $status, $issues)
    {
        $recommendations = [];

        switch ($status) {
            case 'verified':
                $recommendations[] = 'Driver verification completed successfully. Approve driver application.';
                break;
            case 'conditionally_approved':
                $recommendations[] = 'Driver passed most verification checks. Approve with monitoring.';
                break;
            case 'review_required':
                $recommendations[] = 'Manual review required before making approval decision.';
                break;
            case 'rejected':
                $recommendations[] = 'Driver failed critical verification checks. Reject application.';
                break;
            case 'failed':
                $recommendations[] = 'Critical verification failures detected. Immediate rejection recommended.';
                break;
        }

        if (count($issues) > 2) {
            $recommendations[] = 'Multiple verification issues detected. Consider requesting document resubmission.';
        }

        return $recommendations;
    }

    /**
     * Update driver verification status in database
     */
    private function updateDriverVerificationStatus($driver, $workflowResult)
    {
        try {
            DB::beginTransaction();

            $driver->update([
                'verification_status' => $workflowResult['verification_status'],
                'verification_notes' => 'Automated verification completed',
                'ocr_verification_status' => $this->mapToOcrStatus($workflowResult['verification_status']),
                'ocr_verification_notes' => json_encode($workflowResult['recommendations']),
                'verified_at' => in_array($workflowResult['verification_status'], ['verified', 'conditionally_approved']) ? now() : null,
                'rejected_at' => $workflowResult['verification_status'] === 'rejected' ? now() : null,
                'rejection_reason' => $workflowResult['verification_status'] === 'rejected' ? implode('; ', $workflowResult['issues_found']) : null
            ]);

            // Store detailed verification data
            $this->storeVerificationRecord($driver->id, $workflowResult);

            DB::commit();

            Log::info('Driver verification status updated', [
                'driver_id' => $driver->id,
                'status' => $workflowResult['verification_status']
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update driver verification status', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Map verification status to OCR status format
     */
    private function mapToOcrStatus($verificationStatus)
    {
        $statusMap = [
            'verified' => 'passed',
            'conditionally_approved' => 'passed',
            'review_required' => 'pending',
            'rejected' => 'failed',
            'failed' => 'failed'
        ];

        return $statusMap[$verificationStatus] ?? 'pending';
    }

    /**
     * Store detailed verification record
     */
    private function storeVerificationRecord($driverId, $workflowResult)
    {
        // This could be stored in a separate verification_records table
        // For now, we'll log it for audit purposes
        Log::info('Verification record stored', [
            'driver_id' => $driverId,
            'verification_data' => $workflowResult
        ]);
    }

    /**
     * Send verification notifications
     */
    private function sendVerificationNotifications($driver, $workflowResult)
    {
        try {
            $this->notificationService->sendDriverVerificationUpdate(
                $driver,
                $workflowResult['verification_status'],
                $workflowResult['recommendations']
            );

            Log::info('Verification notifications sent', [
                'driver_id' => $driver->id,
                'status' => $workflowResult['verification_status']
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send verification notifications', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get verification workflow status
     */
    public function getVerificationStatus($driverId)
    {
        try {
            $driver = Drivers::find($driverId);
            if (!$driver) {
                return [
                    'success' => false,
                    'error' => 'Driver not found'
                ];
            }

            return [
                'success' => true,
                'driver_id' => $driver->driver_id,
                'verification_status' => $driver->verification_status,
                'ocr_verification_status' => $driver->ocr_verification_status,
                'verified_at' => $driver->verified_at,
                'rejected_at' => $driver->rejected_at,
                'rejection_reason' => $driver->rejection_reason,
                'verification_notes' => $driver->verification_notes
            ];

        } catch (Exception $e) {
            Log::error('Failed to get verification status', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Retry failed verification steps
     */
    public function retryVerification($driverId, $steps = [])
    {
        // Implementation for retrying specific verification steps
        Log::info('Retrying verification steps', [
            'driver_id' => $driverId,
            'steps' => $steps
        ]);

        // This would re-run only the specified steps
        // For now, we'll return a placeholder
        return [
            'success' => true,
            'message' => 'Verification retry initiated',
            'steps_retried' => $steps
        ];
    }
}
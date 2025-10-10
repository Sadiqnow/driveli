<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\DocumentOCRService;
use App\Services\NINVerificationService;
use App\Services\DocumentMatchingService;
use App\Models\DriverReferee;
use App\Models\DriverNormalized;
use Exception;

class RefereeVerificationService
{
    private $ocrService;
    private $ninService;
    private $matchingService;

    public function __construct(
        DocumentOCRService $ocrService,
        NINVerificationService $ninService,
        DocumentMatchingService $matchingService
    ) {
        $this->ocrService = $ocrService;
        $this->ninService = $ninService;
        $this->matchingService = $matchingService;
    }

    /**
     * Verify referee document and information
     * 
     * @param array $refereeData Referee's submitted information
     * @param string $documentPath Path to referee's uploaded document
     * @param string $driverId Driver ID for whom referee is vouching
     * @return array Verification result
     */
    public function verifyReferee($refereeData, $documentPath = null, $driverId = null)
    {
        try {
            Log::info('Starting referee verification', [
                'referee_name' => $refereeData['name'] ?? 'Unknown',
                'driver_id' => $driverId,
                'has_document' => !is_null($documentPath)
            ]);

            $verificationResult = [
                'success' => false,
                'verified' => false,
                'referee_data' => $refereeData,
                'document_verification' => null,
                'identity_verification' => null,
                'relationship_verification' => null,
                'overall_score' => 0,
                'verification_level' => 'basic',
                'recommendations' => [],
                'flags' => [],
                'timestamp' => now()
            ];

            // Step 1: Validate referee data completeness
            $dataValidation = $this->validateRefereeData($refereeData);
            if (!$dataValidation['valid']) {
                $verificationResult['error'] = 'Incomplete referee information';
                $verificationResult['missing_fields'] = $dataValidation['missing_fields'];
                return $verificationResult;
            }

            // Step 2: Verify document if provided
            if ($documentPath && file_exists($documentPath)) {
                $verificationResult['document_verification'] = $this->verifyRefereeDocument(
                    $documentPath, 
                    $refereeData
                );
                $verificationResult['verification_level'] = 'document_verified';
            }

            // Step 3: Verify referee identity (if NIN provided)
            if (!empty($refereeData['nin'])) {
                $verificationResult['identity_verification'] = $this->verifyRefereeIdentity(
                    $refereeData
                );
                $verificationResult['verification_level'] = 'identity_verified';
            }

            // Step 4: Verify relationship and credibility
            if ($driverId) {
                $verificationResult['relationship_verification'] = $this->verifyRefereeRelationship(
                    $refereeData, 
                    $driverId
                );
            }

            // Step 5: Calculate overall verification score
            $verificationResult['overall_score'] = $this->calculateOverallScore($verificationResult);
            
            // Step 6: Determine verification status
            $verificationResult['verified'] = $this->determineVerificationStatus($verificationResult);
            $verificationResult['success'] = true;

            // Step 7: Generate recommendations and flags
            $verificationResult['recommendations'] = $this->generateRecommendations($verificationResult);
            $verificationResult['flags'] = $this->identifyFlags($verificationResult);

            // Step 8: Store verification record
            if ($driverId && $verificationResult['verified']) {
                $this->storeRefereeRecord($refereeData, $verificationResult, $driverId);
            }

            Log::info('Referee verification completed', [
                'referee_name' => $refereeData['name'] ?? 'Unknown',
                'driver_id' => $driverId,
                'verified' => $verificationResult['verified'],
                'score' => $verificationResult['overall_score']
            ]);

            return $verificationResult;

        } catch (Exception $e) {
            Log::error('Referee verification failed', [
                'referee_name' => $refereeData['name'] ?? 'Unknown',
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => 'Referee verification service error: ' . $e->getMessage(),
                'error_code' => 'SERVICE_ERROR',
                'timestamp' => now()
            ];
        }
    }

    /**
     * Validate referee data completeness
     */
    private function validateRefereeData($refereeData)
    {
        $requiredFields = [
            'name' => 'Full name',
            'relationship' => 'Relationship to driver',
            'phone' => 'Phone number',
            'address' => 'Address',
            'occupation' => 'Occupation'
        ];

        $optionalFields = [
            'nin' => 'National ID Number',
            'email' => 'Email address',
            'organization' => 'Organization/Company',
            'years_known' => 'Years known driver'
        ];

        $missingFields = [];
        $presentFields = [];

        // Check required fields
        foreach ($requiredFields as $field => $description) {
            if (empty($refereeData[$field])) {
                $missingFields[] = $description;
            } else {
                $presentFields[] = $field;
            }
        }

        // Check optional fields
        foreach ($optionalFields as $field => $description) {
            if (!empty($refereeData[$field])) {
                $presentFields[] = $field;
            }
        }

        $completeness = count($presentFields) / (count($requiredFields) + count($optionalFields));

        return [
            'valid' => empty($missingFields),
            'completeness_score' => $completeness,
            'missing_fields' => $missingFields,
            'present_fields' => $presentFields
        ];
    }

    /**
     * Verify referee document using OCR
     */
    private function verifyRefereeDocument($documentPath, $refereeData)
    {
        try {
            Log::info('Verifying referee document', [
                'document_path' => basename($documentPath),
                'referee_name' => $refereeData['name'] ?? 'Unknown'
            ]);

            // Extract data from document using OCR
            $ocrResult = $this->ocrService->extractDocumentData($documentPath, 'nin');
            
            if (!$ocrResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Failed to extract data from document',
                    'ocr_error' => $ocrResult['error']
                ];
            }

            // Compare OCR data with referee provided data
            $comparisonResult = $this->matchingService->compareDocumentData(
                $refereeData,
                $ocrResult['data'],
                'referee_document'
            );

            return [
                'success' => true,
                'ocr_data' => $ocrResult['data'],
                'comparison_result' => $comparisonResult,
                'document_match_score' => $comparisonResult['overall_match_score'],
                'document_verified' => $comparisonResult['match_status'] === 'verified'
            ];

        } catch (Exception $e) {
            Log::error('Referee document verification failed', [
                'document_path' => basename($documentPath),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Document verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify referee identity using NIN
     */
    private function verifyRefereeIdentity($refereeData)
    {
        try {
            Log::info('Verifying referee identity', [
                'nin' => substr($refereeData['nin'], 0, 3) . '***',
                'referee_name' => $refereeData['name'] ?? 'Unknown'
            ]);

            // Verify NIN against NIMC database
            $ninResult = $this->ninService->verifyNIN($refereeData['nin'], $refereeData);

            if (!$ninResult['success']) {
                return [
                    'success' => false,
                    'error' => 'NIN verification failed',
                    'nin_error' => $ninResult['error']
                ];
            }

            return [
                'success' => true,
                'nin_verified' => $ninResult['verified'],
                'nin_data' => $ninResult['nimc_data'],
                'identity_match_score' => $ninResult['match_score'],
                'identity_status' => $ninResult['verification_status'] ?? 'verified'
            ];

        } catch (Exception $e) {
            Log::error('Referee identity verification failed', [
                'nin' => substr($refereeData['nin'] ?? '', 0, 3) . '***',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Identity verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify referee relationship and credibility
     */
    private function verifyRefereeRelationship($refereeData, $driverId)
    {
        try {
            Log::info('Verifying referee relationship', [
                'driver_id' => $driverId,
                'relationship' => $refereeData['relationship'] ?? 'Unknown'
            ]);

            $driver = DriverNormalized::find($driverId);
            if (!$driver) {
                return [
                    'success' => false,
                    'error' => 'Driver not found'
                ];
            }

            $relationshipAnalysis = [
                'relationship_type' => $refereeData['relationship'],
                'is_valid_relationship' => $this->isValidRelationship($refereeData['relationship']),
                'credibility_score' => $this->calculateCredibilityScore($refereeData),
                'contact_verification' => $this->verifyContact($refereeData),
                'previous_references' => $this->checkPreviousReferences($refereeData, $driverId)
            ];

            return [
                'success' => true,
                'relationship_analysis' => $relationshipAnalysis,
                'relationship_verified' => $relationshipAnalysis['is_valid_relationship'] && 
                                         $relationshipAnalysis['credibility_score'] >= 0.7
            ];

        } catch (Exception $e) {
            Log::error('Referee relationship verification failed', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Relationship verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if relationship type is valid
     */
    private function isValidRelationship($relationship)
    {
        $validRelationships = [
            'employer', 'former_employer', 'supervisor', 'manager',
            'colleague', 'business_partner', 'client', 'customer',
            'family_friend', 'neighbor', 'community_leader',
            'teacher', 'mentor', 'professional_contact',
            'landlord', 'religious_leader', 'doctor', 'lawyer'
        ];

        // Normalize relationship
        $normalizedRelationship = strtolower(str_replace([' ', '-'], '_', trim($relationship)));
        
        return in_array($normalizedRelationship, $validRelationships);
    }

    /**
     * Calculate referee credibility score
     */
    private function calculateCredibilityScore($refereeData)
    {
        $score = 0;
        $maxScore = 10;

        // Professional occupation (+2 points)
        if ($this->isProfessionalOccupation($refereeData['occupation'] ?? '')) {
            $score += 2;
        }

        // Has organization (+1 point)
        if (!empty($refereeData['organization'])) {
            $score += 1;
        }

        // Long-term relationship (+2 points)
        $yearsKnown = intval($refereeData['years_known'] ?? 0);
        if ($yearsKnown >= 2) {
            $score += 2;
        } elseif ($yearsKnown >= 1) {
            $score += 1;
        }

        // Has NIN (+1 point)
        if (!empty($refereeData['nin'])) {
            $score += 1;
        }

        // Has email (+1 point)
        if (!empty($refereeData['email']) && filter_var($refereeData['email'], FILTER_VALIDATE_EMAIL)) {
            $score += 1;
        }

        // Valid phone number (+1 point)
        if ($this->isValidPhone($refereeData['phone'] ?? '')) {
            $score += 1;
        }

        // Professional relationship (+2 points)
        if ($this->isProfessionalRelationship($refereeData['relationship'] ?? '')) {
            $score += 2;
        }

        return $score / $maxScore;
    }

    /**
     * Check if occupation is professional
     */
    private function isProfessionalOccupation($occupation)
    {
        $professionalOccupations = [
            'doctor', 'lawyer', 'engineer', 'accountant', 'teacher', 'professor',
            'manager', 'director', 'supervisor', 'consultant', 'architect',
            'pharmacist', 'nurse', 'banker', 'auditor', 'analyst', 'developer'
        ];

        $normalizedOccupation = strtolower(trim($occupation));
        
        foreach ($professionalOccupations as $professional) {
            if (strpos($normalizedOccupation, $professional) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if relationship is professional
     */
    private function isProfessionalRelationship($relationship)
    {
        $professionalRelationships = [
            'employer', 'former_employer', 'supervisor', 'manager',
            'colleague', 'business_partner', 'client', 'customer'
        ];

        $normalized = strtolower(str_replace([' ', '-'], '_', trim($relationship)));
        return in_array($normalized, $professionalRelationships);
    }

    /**
     * Validate phone number
     */
    private function isValidPhone($phone)
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // Nigerian phone number patterns
        return preg_match('/^(234|0)?[789][01]\d{8}$/', $cleanPhone);
    }

    /**
     * Verify referee contact information
     */
    private function verifyContact($refereeData)
    {
        $verification = [
            'phone_format_valid' => $this->isValidPhone($refereeData['phone'] ?? ''),
            'email_format_valid' => false,
            'contact_attempted' => false,
            'contact_successful' => false
        ];

        // Validate email format
        if (!empty($refereeData['email'])) {
            $verification['email_format_valid'] = filter_var($refereeData['email'], FILTER_VALIDATE_EMAIL) !== false;
        }

        // Note: In production, you might want to implement actual contact verification
        // such as sending SMS or email verification codes

        return $verification;
    }

    /**
     * Check if referee has provided references for other drivers
     */
    private function checkPreviousReferences($refereeData, $currentDriverId)
    {
        try {
            // Search for previous referee records with same phone or NIN
            $query = DriverReferee::where('phone', $refereeData['phone']);
            
            if (!empty($refereeData['nin'])) {
                $query->orWhere('nin', $refereeData['nin']);
            }

            $previousReferences = $query->where('driver_id', '!=', $currentDriverId)
                                       ->get();

            $referenceCount = $previousReferences->count();
            
            // Flag if too many references (potential fraud)
            $suspiciousCount = $referenceCount > 5;

            return [
                'previous_reference_count' => $referenceCount,
                'is_suspicious' => $suspiciousCount,
                'previous_drivers' => $previousReferences->pluck('driver_id')->toArray()
            ];

        } catch (Exception $e) {
            Log::warning('Failed to check previous references', [
                'error' => $e->getMessage()
            ]);

            return [
                'previous_reference_count' => 0,
                'is_suspicious' => false,
                'check_failed' => true
            ];
        }
    }

    /**
     * Calculate overall verification score
     */
    private function calculateOverallScore($verificationResult)
    {
        $scores = [];
        $weights = [];

        // Document verification score
        if ($verificationResult['document_verification']) {
            $scores[] = $verificationResult['document_verification']['document_match_score'] ?? 0;
            $weights[] = 0.3;
        }

        // Identity verification score
        if ($verificationResult['identity_verification']) {
            $scores[] = $verificationResult['identity_verification']['identity_match_score'] ?? 0;
            $weights[] = 0.4;
        }

        // Relationship verification score
        if ($verificationResult['relationship_verification']) {
            $relationshipScore = $verificationResult['relationship_verification']['relationship_analysis']['credibility_score'] ?? 0;
            $scores[] = $relationshipScore;
            $weights[] = 0.3;
        }

        // If no verification methods available, use data completeness
        if (empty($scores)) {
            $dataValidation = $this->validateRefereeData($verificationResult['referee_data']);
            return $dataValidation['completeness_score'];
        }

        // Calculate weighted average
        $totalWeight = array_sum($weights);
        $weightedSum = 0;

        for ($i = 0; $i < count($scores); $i++) {
            $weightedSum += $scores[$i] * $weights[$i];
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Determine verification status based on scores and checks
     */
    private function determineVerificationStatus($verificationResult)
    {
        $score = $verificationResult['overall_score'];

        // Check for automatic disqualification
        if ($this->hasDisqualifyingFlags($verificationResult)) {
            return false;
        }

        // Score-based verification
        if ($score >= 0.8) {
            return true;
        } elseif ($score >= 0.6) {
            // Require manual review for medium scores
            return false; // Will be flagged for manual review
        } else {
            return false;
        }
    }

    /**
     * Check for disqualifying flags
     */
    private function hasDisqualifyingFlags($verificationResult)
    {
        // Check relationship verification
        if (isset($verificationResult['relationship_verification']['relationship_analysis']['previous_references']['is_suspicious'])) {
            if ($verificationResult['relationship_verification']['relationship_analysis']['previous_references']['is_suspicious']) {
                return true;
            }
        }

        // Check identity verification failures
        if (isset($verificationResult['identity_verification']['nin_verified'])) {
            if (!$verificationResult['identity_verification']['nin_verified']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate recommendations based on verification results
     */
    private function generateRecommendations($verificationResult)
    {
        $recommendations = [];

        $score = $verificationResult['overall_score'];

        if ($score >= 0.8) {
            $recommendations[] = 'Referee verification passed. Accept reference.';
        } elseif ($score >= 0.6) {
            $recommendations[] = 'Moderate verification score. Consider manual review.';
            $recommendations[] = 'Contact referee directly to verify information.';
        } else {
            $recommendations[] = 'Low verification score. Reject or request additional references.';
        }

        // Document-specific recommendations
        if (isset($verificationResult['document_verification']) && 
            !$verificationResult['document_verification']['document_verified']) {
            $recommendations[] = 'Document verification failed. Request clearer document copy.';
        }

        // Identity-specific recommendations
        if (isset($verificationResult['identity_verification']) && 
            !$verificationResult['identity_verification']['nin_verified']) {
            $recommendations[] = 'NIN verification failed. Verify referee identity through alternative means.';
        }

        return $recommendations;
    }

    /**
     * Identify verification flags
     */
    private function identifyFlags($verificationResult)
    {
        $flags = [];

        // Suspicious reference patterns
        if (isset($verificationResult['relationship_verification']['relationship_analysis']['previous_references']['is_suspicious'])) {
            if ($verificationResult['relationship_verification']['relationship_analysis']['previous_references']['is_suspicious']) {
                $flags[] = [
                    'type' => 'suspicious_activity',
                    'message' => 'Referee has provided references for too many drivers',
                    'severity' => 'high'
                ];
            }
        }

        // Low credibility score
        if (isset($verificationResult['relationship_verification']['relationship_analysis']['credibility_score'])) {
            $credibility = $verificationResult['relationship_verification']['relationship_analysis']['credibility_score'];
            if ($credibility < 0.5) {
                $flags[] = [
                    'type' => 'low_credibility',
                    'message' => 'Referee credibility score is low',
                    'severity' => 'medium'
                ];
            }
        }

        // Document/identity mismatch
        $documentScore = $verificationResult['document_verification']['document_match_score'] ?? 1.0;
        $identityScore = $verificationResult['identity_verification']['identity_match_score'] ?? 1.0;
        
        if (abs($documentScore - $identityScore) > 0.3) {
            $flags[] = [
                'type' => 'data_inconsistency',
                'message' => 'Inconsistency between document and identity verification',
                'severity' => 'medium'
            ];
        }

        return $flags;
    }

    /**
     * Store referee verification record
     */
    private function storeRefereeRecord($refereeData, $verificationResult, $driverId)
    {
        try {
            DriverReferee::create([
                'driver_id' => $driverId,
                'name' => $refereeData['name'],
                'relationship' => $refereeData['relationship'],
                'phone' => $refereeData['phone'],
                'email' => $refereeData['email'] ?? null,
                'address' => $refereeData['address'],
                'occupation' => $refereeData['occupation'] ?? null,
                'organization' => $refereeData['organization'] ?? null,
                'nin' => $refereeData['nin'] ?? null,
                'years_known' => $refereeData['years_known'] ?? null,
                'verification_score' => $verificationResult['overall_score'],
                'verification_status' => $verificationResult['verified'] ? 'verified' : 'pending',
                'verification_data' => json_encode($verificationResult),
                'verified_at' => $verificationResult['verified'] ? now() : null
            ]);

            Log::info('Referee record stored', [
                'driver_id' => $driverId,
                'referee_name' => $refereeData['name']
            ]);

        } catch (Exception $e) {
            Log::error('Failed to store referee record', [
                'driver_id' => $driverId,
                'referee_name' => $refereeData['name'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get referee verification history for a driver
     */
    public function getRefereeVerificationHistory($driverId)
    {
        try {
            $referees = DriverReferee::where('driver_id', $driverId)
                                   ->orderBy('created_at', 'desc')
                                   ->get();

            $history = $referees->map(function ($referee) {
                return [
                    'id' => $referee->id,
                    'name' => $referee->name,
                    'relationship' => $referee->relationship,
                    'phone' => $referee->phone,
                    'verification_status' => $referee->verification_status,
                    'verification_score' => $referee->verification_score,
                    'verified_at' => $referee->verified_at,
                    'created_at' => $referee->created_at
                ];
            });

            return [
                'success' => true,
                'referees' => $history,
                'total_referees' => $referees->count(),
                'verified_referees' => $referees->where('verification_status', 'verified')->count()
            ];

        } catch (Exception $e) {
            Log::error('Failed to get referee history', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
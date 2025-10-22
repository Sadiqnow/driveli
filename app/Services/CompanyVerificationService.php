<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompanyVerificationService
{
    /**
     * Get verification statistics
     */
    public function getVerificationStatistics(array $dateRange = []): array
    {
        $query = CompanyVerification::query();

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ]);
        }

        $stats = $query->selectRaw('
            status,
            verification_type,
            COUNT(*) as count,
            AVG(TIMESTAMPDIFF(DAY, created_at, verified_at)) as avg_processing_days
        ')
        ->groupBy('status', 'verification_type')
        ->get();

        $totalVerifications = CompanyVerification::count();
        $pendingCount = CompanyVerification::where('status', 'pending')->count();
        $approvedCount = CompanyVerification::where('status', 'approved')->count();
        $rejectedCount = CompanyVerification::where('status', 'rejected')->count();
        $underReviewCount = CompanyVerification::where('status', 'under_review')->count();

        return [
            'total_verifications' => $totalVerifications,
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'rejected' => $rejectedCount,
            'under_review' => $underReviewCount,
            'approval_rate' => $totalVerifications > 0 ? round(($approvedCount / $totalVerifications) * 100, 2) : 0,
            'by_type' => $stats->groupBy('verification_type'),
            'by_status' => $stats->groupBy('status'),
            'date_range' => $dateRange
        ];
    }

    /**
     * Log verification action
     */
    public function logVerificationAction(CompanyVerification $verification, string $action, string $notes = null): void
    {
        DB::table('company_verification_logs')->insert([
            'company_verification_id' => $verification->id,
            'company_id' => $verification->company_id,
            'action' => $action,
            'notes' => $notes,
            'performed_by' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Log::info("Company verification action logged", [
            'verification_id' => $verification->id,
            'company_id' => $verification->company_id,
            'action' => $action,
            'performed_by' => auth('admin')->id()
        ]);
    }

    /**
     * Initiate OCR verification for company documents
     */
    public function initiateOCRVerification(int $companyId, array $documents): array
    {
        try {
            $company = Company::findOrFail($companyId);

            $ocrResults = [];
            foreach ($documents as $documentType => $documentPath) {
                // Here you would integrate with OCR service
                // For now, simulate OCR processing

                $ocrResult = [
                    'company_id' => $companyId,
                    'document_type' => $documentType,
                    'document_path' => $documentPath,
                    'ocr_provider' => 'mock_provider',
                    'processing_status' => 'completed',
                    'confidence_score' => rand(85, 98),
                    'extracted_data' => json_encode([
                        'company_name' => $company->name,
                        'registration_number' => 'RC' . rand(100000, 999999),
                        'extracted_at' => now()->toISOString()
                    ]),
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                DB::table('company_ocr_results')->insert($ocrResult);
                $ocrResults[] = $ocrResult;
            }

            return [
                'success' => true,
                'ocr_results' => $ocrResults
            ];

        } catch (\Exception $e) {
            Log::error("OCR verification failed for company {$companyId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create verification request
     */
    public function createVerificationRequest(int $companyId, string $verificationType, array $documents = []): array
    {
        try {
            $verification = CompanyVerification::create([
                'company_id' => $companyId,
                'verification_type' => $verificationType,
                'status' => 'pending',
                'submitted_documents' => json_encode($documents)
            ]);

            $this->logVerificationAction($verification, 'created', 'Verification request submitted');

            return [
                'success' => true,
                'verification' => $verification
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get company verification details
     */
    public function getCompanyVerificationDetails(int $companyId): array
    {
        try {
            $company = Company::with('verifications.verifiedBy')->findOrFail($companyId);

            $verifications = $company->verifications->map(function ($verification) {
                return [
                    'id' => $verification->id,
                    'type' => $verification->verification_type,
                    'status' => $verification->status,
                    'submitted_documents' => json_decode($verification->submitted_documents, true),
                    'verified_at' => $verification->verified_at,
                    'verified_by' => $verification->verifiedBy?->name,
                    'notes' => $verification->notes,
                    'rejection_reason' => $verification->rejection_reason,
                    'created_at' => $verification->created_at
                ];
            });

            return [
                'success' => true,
                'company' => $company,
                'verifications' => $verifications,
                'current_status' => $company->verification_status
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Retry failed verification
     */
    public function retryVerification(int $verificationId): array
    {
        try {
            $verification = CompanyVerification::findOrFail($verificationId);

            if ($verification->status !== 'rejected') {
                return [
                    'success' => false,
                    'error' => 'Only rejected verifications can be retried'
                ];
            }

            // Reset verification status
            $verification->update([
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'rejection_reason' => null
            ]);

            $this->logVerificationAction($verification, 'retried', 'Verification retry initiated');

            return [
                'success' => true,
                'message' => 'Verification retry initiated'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get pending manual reviews
     */
    public function getPendingManualReviews(): array
    {
        $pendingVerifications = CompanyVerification::with('company')
            ->where('status', 'under_review')
            ->orderBy('created_at', 'asc')
            ->get();

        return $pendingVerifications->map(function ($verification) {
            return [
                'id' => $verification->id,
                'company_name' => $verification->company->name,
                'verification_type' => $verification->verification_type,
                'submitted_at' => $verification->created_at,
                'documents' => json_decode($verification->submitted_documents, true),
                'priority' => $this->calculatePriority($verification)
            ];
        })->toArray();
    }

    /**
     * Calculate verification priority
     */
    protected function calculatePriority(CompanyVerification $verification): string
    {
        $daysPending = $verification->created_at->diffInDays(now());

        if ($daysPending > 7) return 'high';
        if ($daysPending > 3) return 'medium';
        return 'low';
    }

    /**
     * Bulk update verification status
     */
    public function bulkUpdateStatus(array $verificationIds, string $status, array $options = []): array
    {
        $successCount = 0;
        $failureCount = 0;

        foreach ($verificationIds as $id) {
            try {
                $verification = CompanyVerification::findOrFail($id);

                $updateData = ['status' => $status];

                if (in_array($status, ['approved', 'rejected'])) {
                    $updateData['verified_at'] = now();
                    $updateData['verified_by'] = auth('admin')->id();
                }

                if (isset($options['notes'])) {
                    $updateData['notes'] = $options['notes'];
                }

                if (isset($options['rejection_reason'])) {
                    $updateData['rejection_reason'] = $options['rejection_reason'];
                }

                $verification->update($updateData);

                // Update company status
                if ($status === 'approved') {
                    $verification->company->update(['verification_status' => 'verified']);
                } elseif ($status === 'rejected') {
                    $verification->company->update(['verification_status' => 'rejected']);
                }

                $this->logVerificationAction($verification, "bulk_{$status}", $options['notes'] ?? null);
                $successCount++;

            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Failed to update verification {$id}: " . $e->getMessage());
            }
        }

        return [
            'success_count' => $successCount,
            'failure_count' => $failureCount
        ];
    }
}

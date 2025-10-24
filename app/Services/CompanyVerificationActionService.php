<?php

namespace App\Services;

use App\Models\CompanyVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyVerificationActionService
{
    /**
     * Approve a company verification
     */
    public function approveVerification(int $verificationId, array $data): array
    {
        try {
            DB::beginTransaction();

            $verification = CompanyVerification::findOrFail($verificationId);

            $this->updateVerificationStatus($verification, 'approved', $data);
            $this->updateCompanyStatus($verification->company, 'verified');
            $this->logVerificationAction($verification, 'approved', $data['notes'] ?? null);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Company verification approved successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company verification approval failed', [
                'verification_id' => $verificationId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to approve verification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject a company verification
     */
    public function rejectVerification(int $verificationId, array $data): array
    {
        try {
            DB::beginTransaction();

            $verification = CompanyVerification::findOrFail($verificationId);

            $this->updateVerificationStatus($verification, 'rejected', $data);
            $this->updateCompanyStatus($verification->company, 'rejected');
            $this->logVerificationAction($verification, 'rejected', $data['rejection_reason']);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Company verification rejected'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company verification rejection failed', [
                'verification_id' => $verificationId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject verification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Move verification to under review
     */
    public function moveToUnderReview(int $verificationId): array
    {
        try {
            $verification = CompanyVerification::findOrFail($verificationId);

            $verification->update([
                'status' => 'under_review',
                'verified_by' => auth('admin')->id()
            ]);

            app(CompanyVerificationService::class)->logVerificationAction(
                $verification,
                'under_review',
                'Moved to under review'
            );

            return [
                'success' => true,
                'message' => 'Verification moved to under review'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to move verification to under review', [
                'verification_id' => $verificationId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update verification status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk approve verifications
     */
    public function bulkApproveVerifications(array $verificationIds, ?string $notes = null): array
    {
        $successCount = 0;
        $failureCount = 0;

        foreach ($verificationIds as $verificationId) {
            try {
                $result = $this->approveVerification($verificationId, ['notes' => $notes ?? 'Bulk approved']);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $failureCount++;
                Log::error('Bulk approval failed for verification', [
                    'verification_id' => $verificationId,
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
     * Update verification status
     */
    private function updateVerificationStatus(CompanyVerification $verification, string $status, array $data): void
    {
        $updateData = [
            'status' => $status,
            'verified_at' => now(),
            'verified_by' => auth('admin')->id(),
        ];

        if ($status === 'approved') {
            $updateData['notes'] = $data['notes'] ?? null;
        } elseif ($status === 'rejected') {
            $updateData['rejection_reason'] = $data['rejection_reason'];
        }

        $verification->update($updateData);
    }

    /**
     * Update company verification status
     */
    private function updateCompanyStatus($company, string $status): void
    {
        $company->update(['verification_status' => $status]);
    }

    /**
     * Log verification action
     */
    private function logVerificationAction(CompanyVerification $verification, string $action, ?string $notes): void
    {
        app(CompanyVerificationService::class)->logVerificationAction($verification, $action, $notes);
    }
}

<?php

namespace App\Traits;

use App\Helpers\DrivelinkHelper;
use App\Services\EncryptionService;
use App\Constants\DrivelinkConstants;

trait DriverVerificationTrait
{
    // ========================================================================================
    // VERIFICATION METHODS
    // ========================================================================================

    /**
     * Check if driver is verified
     */
    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if driver is active
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * Check if driver has complete profile
     */
    public function hasCompleteProfile()
    {
        return DrivelinkHelper::calculateDriverCompletionPercentage($this) >= 80;
    }

    /**
     * Get document completion percentage
     */
    public function getDocumentCompletionPercentage()
    {
        $requiredDocs = ['nin', 'license_front', 'license_back', 'profile_picture'];
        $uploadedDocs = $this->documents()->whereIn('document_type', $requiredDocs)->count();

        return round(($uploadedDocs / count($requiredDocs)) * 100);
    }

    /**
     * Get verification completion percentage
     */
    public function getVerificationCompletionPercentage()
    {
        return DrivelinkHelper::calculateDriverCompletionPercentage($this);
    }

    // ========================================================================================
    // ADMIN UPDATE METHODS
    // ========================================================================================

    /**
     * Admin update driver status
     */
    public function adminUpdateStatus($status, $adminUser)
    {
        $this->update([
            'status' => $status,
            'updated_at' => now()
        ]);
    }

    /**
     * Admin update driver verification
     */
    public function adminUpdateVerification($status, $adminUser, $notes = null)
    {
        $updateData = [
            'verification_status' => $status,
            'verified_by' => $adminUser->id,
            'verification_notes' => $notes,
        ];

        if ($status === 'verified') {
            $updateData['verified_at'] = now();
            $updateData['rejected_at'] = null;
            $updateData['rejection_reason'] = null;
        } elseif ($status === 'rejected') {
            $updateData['rejected_at'] = now();
            $updateData['rejection_reason'] = $notes;
            $updateData['verified_at'] = null;
        }

        $this->update($updateData);
    }

    /**
     * Admin update OCR verification
     */
    public function adminUpdateOCRVerification($status, $notes = null)
    {
        $this->update([
            'ocr_verification_status' => $status,
            'ocr_verification_notes' => $notes,
        ]);
    }

    // ========================================================================================
    // VERIFICATION SCORE METHODS
    // ========================================================================================

    /**
     * Get verification score based on profile completion and document status
     */
    public function getVerificationScore(): int
    {
        $score = 0;

        // Profile completion (40%)
        $score += ($this->profile_completion_percentage ?? 0) * 0.4;

        // Document verification (35%)
        $documentsScore = $this->getDocumentsCompletionScore();
        $score += $documentsScore * 0.35;

        // KYC completion (25%)
        $kycScore = $this->getKycProgressPercentage();
        $score += $kycScore * 0.25;

        return (int) round($score);
    }

    /**
     * Get documents completion score
     */
    private function getDocumentsCompletionScore(): int
    {
        $requiredDocs = [
            DrivelinkConstants::DOC_TYPE_NIN,
            DrivelinkConstants::DOC_TYPE_LICENSE_FRONT,
            DrivelinkConstants::DOC_TYPE_LICENSE_BACK,
            DrivelinkConstants::DOC_TYPE_PASSPORT_PHOTO,
            DrivelinkConstants::DOC_TYPE_PROFILE_PICTURE
        ];

        $uploadedCount = 0;
        foreach ($requiredDocs as $doc) {
            if (!empty($this->$doc)) {
                $uploadedCount++;
            }
        }

        return (int) (($uploadedCount / count($requiredDocs)) * 100);
    }

    // ========================================================================================
    // ENCRYPTION METHODS
    // ========================================================================================

    /**
     * Encrypt sensitive fields before saving
     */
    public function setAttribute($key, $value)
    {
        if (app()->bound(EncryptionService::class)) {
            $encryptionService = app(EncryptionService::class);
            if ($encryptionService->isSensitiveField($key) && !empty($value)) {
                $value = $encryptionService->encryptField($value, $key);
            }
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Decrypt sensitive fields when retrieving
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (app()->bound(EncryptionService::class)) {
            $encryptionService = app(EncryptionService::class);
            if ($encryptionService->isSensitiveField($key) && !empty($value)) {
                return $encryptionService->decryptField($value, $key);
            }
        }

        return $value;
    }

    /**
     * Get masked version of sensitive field for display
     */
    public function getMaskedAttribute(string $field): string
    {
        if (app()->bound(EncryptionService::class)) {
            $encryptionService = app(EncryptionService::class);
            $value = $this->getAttribute($field);
            return $encryptionService->maskSensitiveData($value, $field);
        }

        return $this->getAttribute($field);
    }
}

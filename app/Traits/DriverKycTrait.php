<?php

namespace App\Traits;

trait DriverKycTrait
{
    // ========================================================================================
    // KYC WORKFLOW METHODS
    // ========================================================================================

    /**
     * Check if a specific KYC step is completed
     */
    public function isKycStepCompleted(int $step): bool
    {
        switch ($step) {
            case 1:
                return !is_null($this->kyc_step_1_completed_at);
            case 2:
                return !is_null($this->kyc_step_2_completed_at);
            case 3:
                return !is_null($this->kyc_step_3_completed_at);
            default:
                return false;
        }
    }

    /**
     * Get current KYC step
     */
    public function getCurrentKycStep(): string
    {
        if ($this->kyc_step === 3 && $this->kyc_status === 'completed') {
            return 'completed';
        }

        switch ($this->kyc_step) {
            case 1:
                return 'step_1';
            case 2:
                return 'step_2';
            case 3:
                return 'step_3';
            default:
                return 'not_started';
        }
    }

    /**
     * Get KYC progress percentage
     */
    public function getKycProgressPercentage(): int
    {
        if ($this->kyc_status === 'completed') {
            return 100;
        }

        $completed = 0;
        if ($this->isKycStepCompleted(1)) $completed++;
        if ($this->isKycStepCompleted(2)) $completed++;
        if ($this->isKycStepCompleted(3)) $completed++;

        return round(($completed / 3) * 100);
    }

    /**
     * Reset KYC process (for retries)
     */
    public function resetKyc(string $reason = null): void
    {
        $this->update([
            'kyc_step' => 1,
            'kyc_status' => 'pending',
            'kyc_step_1_completed_at' => null,
            'kyc_step_2_completed_at' => null,
            'kyc_step_3_completed_at' => null,
            'kyc_completed_at' => null,
            'kyc_submitted_at' => null,
            'kyc_reviewed_at' => null,
            'kyc_reviewed_by' => null,
            'verification_status' => 'pending',
            'verification_notes' => $reason ? 'KYC reset: ' . $reason : 'KYC process reset for retry',
            'kyc_last_activity_at' => now(),
        ]);
    }

    /**
     * Get KYC summary for admin review
     */
    public function getKycSummaryForAdmin(): array
    {
        return [
            'driver_info' => [
                'id' => $this->id,
                'driver_id' => $this->driver_id,
                'name' => $this->full_name,
                'email' => $this->email,
                'phone' => $this->phone,
            ],
            'kyc_status' => [
                'status' => $this->kyc_status,
                'current_step' => $this->getCurrentKycStep(),
                'progress_percentage' => $this->getKycProgressPercentage(),
                'submitted_at' => $this->kyc_submitted_at,
                'completed_at' => $this->kyc_completed_at,
            ],
            'step_completion' => [
                'step_1' => $this->isKycStepCompleted(1),
                'step_2' => $this->isKycStepCompleted(2),
                'step_3' => $this->isKycStepCompleted(3),
            ],
            'documents' => $this->getKycDocumentStatus(),
            'license_info' => [
                'license_number' => $this->license_number,
                'license_class' => $this->license_class,
                'license_expiry_date' => $this->license_expiry_date?->format('Y-m-d'),
            ],
        ];
    }

    // ========================================================================================
    // KYC VERIFICATION METHODS
    // ========================================================================================

    /**
     * Check if driver has completed all KYC steps.
     */
    public function hasCompletedKyc(): bool
    {
        return $this->kyc_status === 'completed' &&
               !is_null($this->kyc_completed_at) &&
               !is_null($this->kyc_step_1_completed_at) &&
               !is_null($this->kyc_step_2_completed_at) &&
               !is_null($this->kyc_step_3_completed_at);
    }

    /**
     * Get next required KYC step.
     */
    public function getNextKycStep(): ?string
    {
        $currentStep = $this->getCurrentKycStep();

        return match($currentStep) {
            'not_started' => 'step_1',
            'step_1' => 'step_2',
            'step_2' => 'step_3',
            'step_3' => 'completed',
            'completed' => null,
            default => 'step_1'
        };
    }

    /**
     * Get KYC status badge for display.
     */
    public function getKycStatusBadge(): array
    {
        return match($this->kyc_status) {
            'pending' => ['class' => 'badge-secondary', 'text' => 'Pending'],
            'in_progress' => ['class' => 'badge-warning', 'text' => 'In Progress'],
            'completed' => ['class' => 'badge-success', 'text' => 'Completed'],
            'rejected' => ['class' => 'badge-danger', 'text' => 'Rejected'],
            'expired' => ['class' => 'badge-dark', 'text' => 'Expired'],
            default => ['class' => 'badge-light', 'text' => 'Not Started']
        };
    }

    /**
     * Check if driver can start/continue KYC process.
     */
    public function canPerformKyc(): bool
    {
        // Can't perform KYC if already completed
        if ($this->kyc_status === 'completed') {
            return false;
        }

        // Can retry if rejected (with limitations handled in middleware)
        if ($this->kyc_status === 'rejected') {
            return true;
        }

        // Can perform if pending or in progress
        return in_array($this->kyc_status, ['pending', 'in_progress']);
    }

    /**
     * Get required documents for KYC.
     */
    public function getRequiredKycDocuments(): array
    {
        return [
            'driver_license_scan' => [
                'name' => 'Driver License Scan',
                'description' => 'Clear photo or scan of your driver license',
                'required' => true,
                'formats' => ['JPG', 'PNG', 'PDF'],
                'max_size' => '2MB'
            ],
            'national_id' => [
                'name' => 'National ID',
                'description' => 'Clear photo or scan of your National ID card',
                'required' => true,
                'formats' => ['JPG', 'PNG', 'PDF'],
                'max_size' => '2MB'
            ],
            'passport_photo' => [
                'name' => 'Passport Photo',
                'description' => 'Recent passport-style photograph',
                'required' => true,
                'formats' => ['JPG', 'PNG'],
                'max_size' => '2MB'
            ],
            'utility_bill' => [
                'name' => 'Utility Bill',
                'description' => 'Recent utility bill for address verification',
                'required' => false,
                'formats' => ['JPG', 'PNG', 'PDF'],
                'max_size' => '2MB'
            ],
        ];
    }

    /**
     * Get KYC document upload status.
     */
    public function getKycDocumentStatus(): array
    {
        $requiredDocs = ['driver_license_scan', 'national_id', 'passport_photo'];
        $uploadedDocs = $this->documents()
            ->whereIn('document_type', $requiredDocs)
            ->select(['document_type', 'verification_status', 'created_at'])
            ->get()
            ->keyBy('document_type');

        $status = [];
        foreach ($requiredDocs as $docType) {
            $status[$docType] = [
                'uploaded' => $uploadedDocs->has($docType),
                'status' => $uploadedDocs->has($docType) ?
                    $uploadedDocs[$docType]->verification_status : 'not_uploaded',
                'uploaded_at' => $uploadedDocs->has($docType) ?
                    $uploadedDocs[$docType]->created_at : null,
            ];
        }

        return $status;
    }

    // ========================================================================================
    // KYC SCOPES
    // ========================================================================================

    /**
     * KYC Scopes for querying.
     */
    public function scopeKycPending($query)
    {
        return $query->where('kyc_status', 'pending');
    }

    public function scopeKycInProgress($query)
    {
        return $query->where('kyc_status', 'in_progress');
    }

    public function scopeKycCompleted($query)
    {
        return $query->where('kyc_status', 'completed');
    }

    public function scopeKycRejected($query)
    {
        return $query->where('kyc_status', 'rejected');
    }

    public function scopeAwaitingKycReview($query)
    {
        return $query->where('kyc_status', 'completed')
            ->where('verification_status', 'reviewing');
    }

    public function scopeCompletedKycToday($query)
    {
        return $query->where('kyc_status', 'completed')
            ->whereDate('kyc_completed_at', today());
    }

    /**
     * Scope for admin KYC list
     */
    public function scopeForAdminKycReview($query)
    {
        return $query->select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'kyc_status', 'kyc_step', 'verification_status',
            'kyc_submitted_at', 'kyc_completed_at', 'kyc_retry_count',
            'profile_completion_percentage', 'created_at'
        ]);
    }
}

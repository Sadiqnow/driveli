<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Collection;

class DriverOnboardingProgressService
{
    /**
     * Calculate overall onboarding progress percentage
     */
    public function calculateProgress(Driver $driver): float
    {
        $steps = $this->getOnboardingSteps();
        $totalWeight = collect($steps)->sum('weight');
        $completedWeight = 0;

        foreach ($steps as $step) {
            if ($this->isStepCompleted($driver, $step['key'])) {
                $completedWeight += $step['weight'];
            }
        }

        return round(($completedWeight / $totalWeight) * 100, 2);
    }

    /**
     * Get detailed progress breakdown by step
     */
    public function getProgressBreakdown(Driver $driver): array
    {
        $steps = $this->getOnboardingSteps();
        $breakdown = [];

        foreach ($steps as $step) {
            $breakdown[$step['key']] = [
                'name' => $step['name'],
                'completed' => $this->isStepCompleted($driver, $step['key']),
                'weight' => $step['weight'],
                'fields' => $this->getStepFieldsStatus($driver, $step['key'])
            ];
        }

        return $breakdown;
    }

    /**
     * Get onboarding steps configuration
     */
    public function getOnboardingSteps(): array
    {
        return [
            [
                'key' => 'personal_info',
                'name' => 'Personal Information',
                'weight' => 20,
                'required_fields' => ['first_name', 'surname', 'date_of_birth', 'gender']
            ],
            [
                'key' => 'contact_info',
                'name' => 'Contact & Emergency',
                'weight' => 15,
                'required_fields' => ['phone', 'emergency_contact_name', 'emergency_contact_phone']
            ],
            [
                'key' => 'documents',
                'name' => 'Documents',
                'weight' => 25,
                'required_fields' => ['profile_picture', 'id_document', 'drivers_license']
            ],
            [
                'key' => 'banking',
                'name' => 'Banking Details',
                'weight' => 15,
                'required_fields' => ['account_number', 'account_name', 'bank_name']
            ],
            [
                'key' => 'professional',
                'name' => 'Professional Info',
                'weight' => 15,
                'required_fields' => ['license_number', 'years_experience', 'vehicle_type']
            ],
            [
                'key' => 'verification',
                'name' => 'Verification',
                'weight' => 10,
                'required_fields' => ['email_verified', 'phone_verified']
            ]
        ];
    }

    /**
     * Check if a specific step is completed
     */
    public function isStepCompleted(Driver $driver, string $stepKey): bool
    {
        $fields = $this->getStepFieldsStatus($driver, $stepKey);
        return collect($fields)->every('completed');
    }

    /**
     * Get field completion status for a step
     */
    private function getStepFieldsStatus(Driver $driver, string $stepKey): array
    {
        switch ($stepKey) {
            case 'personal_info':
                return [
                    'first_name' => ['completed' => !empty($driver->first_name), 'value' => $driver->first_name],
                    'surname' => ['completed' => !empty($driver->surname), 'value' => $driver->surname],
                    'date_of_birth' => ['completed' => !empty($driver->personalInfo?->date_of_birth), 'value' => $driver->personalInfo?->date_of_birth],
                    'gender' => ['completed' => !empty($driver->personalInfo?->gender), 'value' => $driver->personalInfo?->gender]
                ];

            case 'contact_info':
                return [
                    'phone' => ['completed' => !empty($driver->phone), 'value' => $driver->phone],
                    'emergency_contact_name' => ['completed' => !empty($driver->personalInfo?->name), 'value' => $driver->personalInfo?->name],
                    'emergency_contact_phone' => ['completed' => !empty($driver->personalInfo?->phone), 'value' => $driver->personalInfo?->phone]
                ];

            case 'documents':
                $documents = $driver->documents->keyBy('document_type');
                return [
                    'profile_picture' => ['completed' => isset($documents['profile_picture']), 'value' => $documents['profile_picture']->document_path ?? null],
                    'id_document' => ['completed' => isset($documents['id_card']) || isset($documents['passport']), 'value' => $documents['id_card']->document_path ?? $documents['passport']->document_path ?? null],
                    'drivers_license' => ['completed' => isset($documents['drivers_license']), 'value' => $documents['drivers_license']->document_path ?? null]
                ];

            case 'banking':
                $primaryBanking = $driver->primaryBankingDetail;
                return [
                    'account_number' => ['completed' => !empty($primaryBanking?->account_number), 'value' => $primaryBanking?->account_number],
                    'account_name' => ['completed' => !empty($primaryBanking?->account_name), 'value' => $primaryBanking?->account_name],
                    'bank_name' => ['completed' => !empty($primaryBanking?->bank_name), 'value' => $primaryBanking?->bank_name]
                ];

            case 'professional':
                return [
                    'license_number' => ['completed' => !empty($driver->performance?->license_number), 'value' => $driver->performance?->license_number],
                    'years_experience' => ['completed' => !empty($driver->performance?->years_of_experience), 'value' => $driver->performance?->years_of_experience],
                    'vehicle_type' => ['completed' => !empty($driver->performance?->vehicle_type), 'value' => $driver->performance?->vehicle_type]
                ];

            case 'verification':
                return [
                    'email_verified' => ['completed' => !empty($driver->email_verified_at), 'value' => $driver->email_verified_at],
                    'phone_verified' => ['completed' => !empty($driver->phone_verified_at), 'value' => $driver->phone_verified_at]
                ];

            default:
                return [];
        }
    }

    /**
     * Get next incomplete step
     */
    public function getNextStep(Driver $driver): ?string
    {
        $steps = $this->getOnboardingSteps();

        foreach ($steps as $step) {
            if (!$this->isStepCompleted($driver, $step['key'])) {
                return $step['key'];
            }
        }

        return null; // All steps completed
    }

    /**
     * Get step completion status for UI
     */
    public function getStepStatus(Driver $driver, string $stepKey): array
    {
        $completed = $this->isStepCompleted($driver, $stepKey);
        $fields = $this->getStepFieldsStatus($driver, $stepKey);
        $completedFields = collect($fields)->where('completed', true)->count();
        $totalFields = count($fields);

        return [
            'completed' => $completed,
            'progress' => $totalFields > 0 ? round(($completedFields / $totalFields) * 100, 1) : 0,
            'completed_fields' => $completedFields,
            'total_fields' => $totalFields,
            'fields' => $fields
        ];
    }

    /**
     * Check if onboarding is fully completed
     */
    public function isOnboardingCompleted(Driver $driver): bool
    {
        return $this->calculateProgress($driver) >= 100;
    }

    /**
     * Get completion summary
     */
    public function getCompletionSummary(Driver $driver): array
    {
        $progress = $this->calculateProgress($driver);
        $breakdown = $this->getProgressBreakdown($driver);
        $completedSteps = collect($breakdown)->where('completed', true)->count();
        $totalSteps = count($breakdown);

        return [
            'overall_progress' => $progress,
            'completed_steps' => $completedSteps,
            'total_steps' => $totalSteps,
            'next_step' => $this->getNextStep($driver),
            'is_completed' => $this->isOnboardingCompleted($driver),
            'breakdown' => $breakdown
        ];
    }
}

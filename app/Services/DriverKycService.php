<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class DriverKycService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Show KYC review dashboard for admin
     */
    public function getKycReviewDashboard(Request $request): array
    {
        $query = Driver::select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'kyc_status', 'kyc_step', 'kyc_submitted_at', 'kyc_completed_at',
            'kyc_retry_count', 'profile_completion_percentage', 'created_at'
        ]);

        // Filter by KYC status
        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        } else {
            // Default to completed KYC submissions awaiting review
            $query->where('kyc_status', 'completed')
                  ->where('verification_status', 'pending');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('surname', 'LIKE', "%{$search}%")
                  ->orWhere('driver_id', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('kyc_submitted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('kyc_submitted_at', '<=', $request->date_to);
        }

        $drivers = $query->orderBy('kyc_submitted_at', 'desc')->paginate(20);

        // Get KYC statistics
        $stats = [
            'pending_review' => Driver::where('kyc_status', 'completed')
                                    ->where('verification_status', 'pending')
                                    ->count(),
            'approved' => Driver::where('verification_status', 'verified')
                                ->where('kyc_status', 'completed')
                                ->count(),
            'rejected' => Driver::where('kyc_status', 'rejected')->count(),
            'in_progress' => Driver::where('kyc_status', 'in_progress')->count(),
        ];

        return [
            'drivers' => $drivers,
            'stats' => $stats
        ];
    }

    /**
     * Show detailed KYC information for review
     */
    public function getKycDetails(Driver $driver): array
    {
        // Load all related KYC data
        $driver->load([
            'nationality:id,name,code',
            'residenceState:id,name',
            'residenceLga:id,name',
            'bank:id,name,code'
        ]);

        // Get KYC summary
        $kycSummary = $driver->getKycSummaryForAdmin();

        // Get document paths with secure access
        $documents = [
            'nin_document' => $driver->nin_document,
            'profile_picture' => $driver->profile_picture,
            'license_front_image' => $driver->license_front_image,
            'license_back_image' => $driver->license_back_image,
            'frsc_document' => $driver->frsc_document,
            'passport_photograph' => $driver->passport_photograph,
        ];

        // Calculate verification readiness
        $verificationReadiness = $this->calculateVerificationReadiness($driver);

        return [
            'driver' => $driver,
            'kycSummary' => $kycSummary,
            'documents' => $documents,
            'verificationReadiness' => $verificationReadiness
        ];
    }

    /**
     * Approve KYC application
     */
    public function approveKyc(Driver $driver, string $notes): array
    {
        try {
            DB::beginTransaction();

            // Update driver verification status
            $driver->update([
                'verification_status' => 'verified',
                'kyc_status' => 'completed',
                'kyc_reviewed_at' => now(),
                'kyc_reviewed_by' => Auth::guard('admin')->id(),
                'verification_notes' => $notes,
                'verified_at' => now(),
                'verified_by' => Auth::guard('admin')->id(),
                'status' => 'active', // Activate the driver
            ]);

            DB::commit();

            // Log the approval
            Log::info('KYC approved by admin', [
                'driver_id' => $driver->driver_id,
                'admin_id' => Auth::guard('admin')->id(),
                'notes' => $notes
            ]);

            // Send approval notification
            try {
                $this->sendKycNotification($driver, 'approved', $notes);
            } catch (\Exception $e) {
                Log::warning('Failed to send KYC approval notification: ' . $e->getMessage());
            }

            return [
                'success' => true,
                'message' => 'KYC application approved successfully! Driver has been notified.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC approval failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to approve KYC: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject KYC application
     */
    public function rejectKyc(Driver $driver, string $reason, string $notes, bool $allowRetry = false): array
    {
        try {
            DB::beginTransaction();

            // Check retry limits
            $canRetry = $allowRetry && $driver->kyc_retry_count < 3;

            // Update driver status
            $driver->update([
                'verification_status' => 'rejected',
                'kyc_status' => 'rejected',
                'kyc_reviewed_at' => now(),
                'kyc_reviewed_by' => Auth::guard('admin')->id(),
                'kyc_rejection_reason' => $reason,
                'verification_notes' => $reason,
                'rejected_at' => now(),
            ]);

            // Reset KYC if retry is allowed
            if ($canRetry) {
                $driver->resetKyc($reason);
            }

            DB::commit();

            // Log the rejection
            Log::info('KYC rejected by admin', [
                'driver_id' => $driver->driver_id,
                'admin_id' => Auth::guard('admin')->id(),
                'reason' => $reason,
                'can_retry' => $canRetry
            ]);

            // Send rejection notification
            try {
                $this->sendKycNotification($driver, 'rejected', $notes, $canRetry);
            } catch (\Exception $e) {
                Log::warning('Failed to send KYC rejection notification: ' . $e->getMessage());
            }

            $message = 'KYC application rejected successfully. Driver has been notified.';
            if ($canRetry) {
                $message .= ' Driver can retry the KYC process.';
            }

            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC rejection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to reject KYC: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Request additional information for KYC
     */
    public function requestKycInfo(Driver $driver, string $infoRequest): array
    {
        try {
            // Update driver status to pending with info request
            $driver->update([
                'verification_status' => 'pending',
                'kyc_status' => 'in_progress',
                'verification_notes' => 'Additional information requested: ' . $infoRequest,
                'kyc_last_activity_at' => now(),
            ]);

            // Log the info request
            Log::info('Additional KYC information requested', [
                'driver_id' => $driver->driver_id,
                'admin_id' => Auth::guard('admin')->id(),
                'request' => $infoRequest
            ]);

            // Send info request notification
            try {
                $this->sendKycNotification($driver, 'info_request', $infoRequest);
            } catch (\Exception $e) {
                Log::warning('Failed to send KYC info request notification: ' . $e->getMessage());
            }

            return [
                'success' => true,
                'message' => 'Additional information request sent to driver.'
            ];

        } catch (\Exception $e) {
            Log::error('KYC info request failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send info request: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk KYC actions
     */
    public function performBulkKycAction(array $driverIds, string $action, string $notes = null): array
    {
        try {
            $drivers = Driver::whereIn('id', $driverIds)->get();
            $processed = 0;
            $failed = 0;

            DB::beginTransaction();

            foreach ($drivers as $driver) {
                try {
                    switch ($action) {
                        case 'approve':
                            $result = $this->approveKyc($driver, $notes ?? 'Bulk approval');
                            if ($result['success']) {
                                $processed++;
                            } else {
                                $failed++;
                            }
                            break;

                        case 'reject':
                            $result = $this->rejectKyc($driver, $notes ?? 'Bulk rejection', $notes ?? 'Bulk rejection');
                            if ($result['success']) {
                                $processed++;
                            } else {
                                $failed++;
                            }
                            break;

                        case 'request_info':
                            $result = $this->requestKycInfo($driver, $notes ?? 'Additional information required for bulk review');
                            if ($result['success']) {
                                $processed++;
                            } else {
                                $failed++;
                            }
                            break;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Bulk KYC action failed for driver ' . $driver->id . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            $actionMessages = [
                'approve' => 'approved',
                'reject' => 'rejected',
                'request_info' => 'requested additional info from'
            ];

            $message = "Bulk KYC action completed: {$processed} drivers {$actionMessages[$action] ?? 'processed'}";
            if ($failed > 0) {
                $message .= ", {$failed} failed";
            }

            return [
                'success' => $failed === 0,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk KYC action failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Complete KYC verification for a driver
     */
    public function completeKyc(Driver $driver, Request $request): array
    {
        try {
            DB::beginTransaction();

            // Handle file uploads
            $filePaths = [];
            $uploadFields = ['driver_license_scan', 'national_id', 'passport_photo'];

            foreach ($uploadFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    if ($file && $file->isValid()) {
                        $fileName = time() . '_' . $field . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $filePath = $file->storeAs('drivers/documents', $fileName, 'public');
                        $filePaths[$field] = $filePath;
                    }
                }
            }

            // Update driver with KYC data
            $driver->update([
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'full_address' => $request->full_address,
                'city' => $request->city,
                'state' => $request->state,
                'license_issue_date' => $request->license_issue_date,
                'license_expiry_date' => $request->license_expiry_date,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'kyc_status' => 'completed',
                'kyc_completed_at' => now(),
                'profile_photo' => $filePaths['passport_photo'] ?? $driver->profile_photo,
                'license_front_image' => $filePaths['driver_license_scan'] ?? $driver->license_front_image,
                'national_id_image' => $filePaths['national_id'] ?? $driver->national_id_image,
                'updated_at' => now()
            ]);

            DB::commit();

            // Log KYC completion
            Log::info('KYC completed for driver', [
                'driver_id' => $driver->driver_id,
                'admin_id' => Auth::guard('admin')->id(),
                'files_uploaded' => array_keys($filePaths)
            ]);

            return [
                'success' => true,
                'message' => 'KYC verification completed successfully! The driver profile is now complete.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete KYC verification', [
                'error' => $e->getMessage(),
                'driver_id' => $driver->id,
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to complete KYC verification. Please try again.'
            ];
        }
    }

    /**
     * Calculate verification readiness score
     */
    private function calculateVerificationReadiness(Driver $driver): array
    {
        $criteria = [
            'profile_completion' => [
                'weight' => 25,
                'score' => $driver->profile_completion_percentage ?? 0,
                'status' => $driver->profile_completion_percentage >= 90 ? 'passed' : 'needs_attention'
            ],
            'kyc_steps_completed' => [
                'weight' => 30,
                'score' => $driver->getKycProgressPercentage(),
                'status' => $driver->hasCompletedKyc() ? 'passed' : 'needs_attention'
            ],
            'required_documents' => [
                'weight' => 25,
                'score' => $this->calculateDocumentScore($driver),
                'status' => $this->calculateDocumentScore($driver) >= 80 ? 'passed' : 'needs_attention'
            ],
            'data_accuracy' => [
                'weight' => 20,
                'score' => $this->calculateDataAccuracyScore($driver),
                'status' => $this->calculateDataAccuracyScore($driver) >= 70 ? 'passed' : 'needs_attention'
            ],
        ];

        $totalScore = 0;
        foreach ($criteria as $criterion) {
            $totalScore += ($criterion['score'] * $criterion['weight']) / 100;
        }

        return [
            'overall_score' => round($totalScore),
            'overall_status' => $totalScore >= 80 ? 'ready' : ($totalScore >= 60 ? 'needs_review' : 'not_ready'),
            'criteria' => $criteria
        ];
    }

    /**
     * Calculate document completeness score
     */
    private function calculateDocumentScore(Driver $driver): int
    {
        $requiredDocs = ['nin_document', 'profile_picture', 'license_front_image', 'license_back_image', 'passport_photograph'];
        $uploaded = 0;

        foreach ($requiredDocs as $doc) {
            if (!empty($driver->$doc)) {
                $uploaded++;
            }
        }

        return round(($uploaded / count($requiredDocs)) * 100);
    }

    /**
     * Calculate data accuracy score
     */
    private function calculateDataAccuracyScore(Driver $driver): int
    {
        $requiredFields = ['first_name', 'surname', 'email', 'phone', 'date_of_birth', 'license_number'];
        $completed = 0;

        foreach ($requiredFields as $field) {
            if (!empty($driver->$field)) {
                $completed++;
            }
        }

        // Additional checks for data quality
        $qualityScore = 100;

        // Check email format
        if (!filter_var($driver->email, FILTER_VALIDATE_EMAIL)) {
            $qualityScore -= 10;
        }

        // Check phone number length
        if (strlen(preg_replace('/[^0-9]/', '', $driver->phone)) < 10) {
            $qualityScore -= 10;
        }

        // Check date of birth (must be at least 18 years old)
        if ($driver->date_of_birth && $driver->date_of_birth->age < 18) {
            $qualityScore -= 20;
        }

        $completionScore = round(($completed / count($requiredFields)) * 100);
        return round(($completionScore + $qualityScore) / 2);
    }

    /**
     * Send KYC notification to driver
     */
    private function sendKycNotification(Driver $driver, string $action, string $notes, bool $canRetry = false): void
    {
        try {
            // Try to use the notification service's KYC notification method
            if (method_exists($this->notificationService, 'sendKycNotification')) {
                $this->notificationService->sendKycNotification($driver, $action, $notes, $canRetry);
            } elseif (method_exists($this->notificationService, 'sendVerificationNotification')) {
                // Fallback to verification notification
                $this->notificationService->sendVerificationNotification($driver, "kyc_{$action}", $notes);
            } else {
                Log::warning('NotificationService does not provide KYC notification methods');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send KYC notification: ' . $e->getMessage());
        }
    }
}

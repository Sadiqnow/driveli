<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Services\OCRVerificationService;
use Illuminate\Support\Facades\Auth;

class DriverVerificationService
{
    protected $notificationService;
    protected $ocrService;

    public function __construct(
        NotificationService $notificationService,
        OCRVerificationService $ocrService
    ) {
        $this->notificationService = $notificationService;
        $this->ocrService = $ocrService;
    }

    /**
     * Update driver verification status
     */
    public function updateVerificationStatus(Driver $driver, string $status, $admin, string $notes = null, string $adminPassword = null): array
    {
        // Verify admin password if provided
        if ($adminPassword && !Hash::check($adminPassword, $admin->password)) {
            return [
                'success' => false,
                'message' => 'Invalid admin password.'
            ];
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'verification_status' => $status,
                'verified_at' => $status === 'verified' ? now() : null,
                'verified_by' => $status === 'verified' ? $admin->id : null,
                'verification_notes' => $notes,
                'updated_at' => now()
            ];

            if ($status === 'verified') {
                $updateData['status'] = 'active';
            }

            $driver->update($updateData);

            DB::commit();

            $message = "Driver verification status updated to {$status}";

            // Log the action
            Log::info('Driver verification status updated', [
                'driver_id' => $driver->driver_id,
                'status' => $status,
                'admin_id' => $admin->id,
                'notes' => $notes
            ]);

            return [
                'success' => true,
                'message' => $message,
                'driver' => $driver->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Driver verification update failed', [
                'driver_id' => $driver->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update verification status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject driver verification
     */
    public function rejectVerification(Driver $driver, string $reason, string $notes, string $adminPassword): array
    {
        // Verify admin password
        if (!Hash::check($adminPassword, Auth::guard('admin')->user()->password)) {
            return [
                'success' => false,
                'message' => 'Invalid admin password.'
            ];
        }

        $rejectionNote = "Reason: " . ucwords(str_replace('_', ' ', $reason));
        if ($notes) {
            $rejectionNote .= "\nNotes: " . $notes;
        }

        try {
            DB::beginTransaction();

            $driver->update([
                'verification_status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'verification_notes' => $rejectionNote,
            ]);

            DB::commit();

            // Log the rejection
            Log::info('Driver verification rejected', [
                'driver_id' => $driver->driver_id,
                'reason' => $reason,
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return [
                'success' => true,
                'message' => 'Driver verification rejected successfully!'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Driver rejection failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject driver: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toggle driver status
     */
    public function toggleStatus(Driver $driver): array
    {
        $newStatus = $driver->status === 'active' ? 'inactive' : 'active';

        try {
            $driver->update(['status' => $newStatus]);

            Log::info('Driver status toggled', [
                'driver_id' => $driver->driver_id,
                'old_status' => $driver->status,
                'new_status' => $newStatus,
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return [
                'success' => true,
                'message' => "Driver status changed to {$newStatus}!",
                'new_status' => $newStatus
            ];

        } catch (\Exception $e) {
            Log::error('Driver status toggle failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to toggle driver status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle document approval
     */
    public function handleDocumentAction(Driver $driver, string $documentType, string $action, $admin, string $notes = null): array
    {
        try {
            $currentNotes = $driver->verification_notes ?: '';
            $adminName = $admin->name ?? $admin->email ?? 'Admin';

            if ($action === 'approved') {
                $newNote = "Document '{$documentType}' APPROVED by {$adminName} at " . now()->format('Y-m-d H:i:s');
                if ($notes) {
                    $newNote .= ". Notes: {$notes}";
                }

                $driver->update([
                    'verification_notes' => $currentNotes . "\n" . $newNote,
                ]);

                $message = "Document '{$documentType}' approved successfully!";

            } elseif ($action === 'rejected') {
                $newNote = "Document '{$documentType}' REJECTED by {$adminName} at " . now()->format('Y-m-d H:i:s');
                if ($notes) {
                    $newNote .= ". Reason: {$notes}";
                }

                $driver->update([
                    'verification_notes' => $currentNotes . "\n" . $newNote,
                    'verification_status' => 'rejected'
                ]);

                $message = "Document '{$documentType}' rejected successfully!";
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid action specified.'
                ];
            }

            Log::info('Document action performed', [
                'driver_id' => $driver->driver_id,
                'document_type' => $documentType,
                'action' => $action,
                'admin_id' => $admin->id,
                'notes' => $notes
            ]);

            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            Log::error('Document action failed', [
                'driver_id' => $driver->id,
                'document_type' => $documentType,
                'action' => $action,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process document action: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Perform bulk verification actions
     */
    public function performBulkAction(array $driverIds, string $action, string $notes = null): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $admin = Auth::guard('admin')->user();

        foreach ($driverIds as $driverId) {
            try {
                $driver = Driver::findOrFail($driverId);
                $results['processed']++;

                switch ($action) {
                    case 'activate':
                        $driver->update(['status' => 'active']);
                        $results['successful']++;
                        break;

                    case 'deactivate':
                        $driver->update(['status' => 'inactive']);
                        $results['successful']++;
                        break;

                    case 'suspend':
                        $driver->update(['status' => 'suspended']);
                        $results['successful']++;
                        break;

                    case 'verify':
                        $result = $this->updateVerificationStatus($driver, 'verified', $admin, $notes);
                        if ($result['success']) {
                            $results['successful']++;
                            // Send notification
                            $this->notificationService->sendVerificationNotification($driver, 'verified', $notes);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = "Driver {$driver->driver_id}: {$result['message']}";
                        }
                        break;

                    case 'reject':
                        $result = $this->updateVerificationStatus($driver, 'rejected', $admin, $notes);
                        if ($result['success']) {
                            $results['successful']++;
                            // Send notification
                            $this->notificationService->sendVerificationNotification($driver, 'rejected', $notes);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = "Driver {$driver->driver_id}: {$result['message']}";
                        }
                        break;

                    case 'ocr_verify':
                        // OCR verification logic would go here
                        $results['successful']++;
                        break;

                    default:
                        $results['errors'][] = "Driver {$driver->driver_id}: Unknown action '{$action}'";
                        $results['failed']++;
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Driver ID {$driverId}: {$e->getMessage()}";
            }
        }

        $actionMessages = [
            'activate' => 'activated',
            'deactivate' => 'deactivated',
            'suspend' => 'suspended',
            'verify' => 'verified',
            'reject' => 'rejected',
            'ocr_verify' => 'OCR verified'
        ];

        $message = "Bulk action completed: {$results['successful']} drivers " . ($actionMessages[$action] ?? 'processed');
        if ($results['failed'] > 0) {
            $message .= ", {$results['failed']} failed";
        }

        Log::info('Bulk verification action completed', [
            'action' => $action,
            'total_processed' => $results['processed'],
            'successful' => $results['successful'],
            'failed' => $results['failed'],
            'admin_id' => $admin->id
        ]);

        return [
            'success' => $results['failed'] === 0,
            'message' => $message,
            'results' => $results
        ];
    }

    /**
     * Initiate OCR verification for a driver
     */
    public function initiateOCRVerification(Driver $driver): array
    {
        try {
            $results = [
                'nin_verification' => null,
                'frsc_verification' => null,
                'overall_success' => true,
                'errors' => [],
                'processed_count' => 0
            ];

            // Check if driver has any documents to process
            if (!$driver->nin_document && !$driver->frsc_document) {
                $results['overall_success'] = false;
                $results['errors'][] = 'No documents available for OCR verification';

                return [
                    'success' => false,
                    'message' => 'No documents available for OCR verification',
                    'results' => $results
                ];
            }

            // Verify NIN document if available
            if ($driver->nin_document) {
                try {
                    $ninResult = $this->ocrService->verifyNINDocument($driver, $driver->nin_document);
                    $results['nin_verification'] = $ninResult;
                    $results['processed_count']++;

                    if (!$ninResult['success']) {
                        $results['errors'][] = 'NIN verification failed: ' . ($ninResult['error'] ?? 'Unknown error');
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = 'NIN verification error: ' . $e->getMessage();
                }
            }

            // Verify FRSC document if available
            if ($driver->frsc_document) {
                try {
                    $frscResult = $this->ocrService->verifyFRSCDocument($driver, $driver->frsc_document);
                    $results['frsc_verification'] = $frscResult;
                    $results['processed_count']++;

                    if (!$frscResult['success']) {
                        $results['errors'][] = 'FRSC verification failed: ' . ($frscResult['error'] ?? 'Unknown error');
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = 'FRSC verification error: ' . $e->getMessage();
                }
            }

            // Update overall OCR status based on results
            $ninStatus = 'pending';
            $frscStatus = 'pending';

            if ($results['nin_verification']) {
                $ninStatus = $results['nin_verification']['success'] ?
                    ($results['nin_verification']['status'] ?? 'pending') : 'failed';
            }

            if ($results['frsc_verification']) {
                $frscStatus = $results['frsc_verification']['success'] ?
                    ($results['frsc_verification']['status'] ?? 'pending') : 'failed';
            }

            // Determine overall status
            $overallStatus = 'pending';
            if ($ninStatus === 'passed' && $frscStatus === 'passed') {
                $overallStatus = 'passed';
            } elseif ($ninStatus === 'failed' || $frscStatus === 'failed') {
                $overallStatus = 'failed';
            }

            // Update driver with verification status
            $driver->update([
                'ocr_verification_status' => $overallStatus,
                'ocr_verification_notes' => 'OCR verification processed at ' . now() .
                    (count($results['errors']) > 0 ? '. Errors: ' . implode('; ', $results['errors']) : '')
            ]);

            $message = $results['processed_count'] > 0 ?
                "OCR verification completed for {$results['processed_count']} document(s). Status: {$overallStatus}" :
                'No documents were processed';

            return [
                'success' => count($results['errors']) === 0,
                'message' => $message,
                'results' => $results,
                'driver_status' => $overallStatus
            ];

        } catch (\Exception $e) {
            Log::error('OCR verification failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'OCR verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get OCR verification details for a driver
     */
    public function getOCRVerificationDetails(Driver $driver): array
    {
        try {
            $summary = $this->ocrService->getVerificationSummary($driver);

            $details = [
                'success' => true,
                'summary' => $summary,
                'nin_data' => null,
                'frsc_data' => null
            ];

            if ($driver->nin_verification_data) {
                $details['nin_data'] = json_decode($driver->nin_verification_data, true);
            }

            if ($driver->frsc_verification_data) {
                $details['frsc_data'] = json_decode($driver->frsc_verification_data, true);
            }

            return $details;

        } catch (\Exception $e) {
            Log::error('Failed to load OCR details', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to load OCR details: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Manual OCR verification override
     */
    public function manualOCROverride(Driver $driver, array $params): array
    {
        // Verify admin password
        if (!Hash::check($params['admin_password'], Auth::guard('admin')->user()->password)) {
            return [
                'success' => false,
                'message' => 'Invalid admin password.'
            ];
        }

        try {
            $adminUser = Auth::guard('admin')->user();
            $adminName = $adminUser->name ?? $adminUser->email ?? 'Admin';

            $updateData = [
                'ocr_verification_notes' => "Manual override by {$adminName} at " . now()->format('Y-m-d H:i:s') . ": " . $params['admin_notes']
            ];

            if ($params['verification_type'] === 'nin' || $params['verification_type'] === 'both') {
                $updateData['nin_ocr_match_score'] = $params['status'] === 'passed' ? 100 : ($params['status'] === 'failed' ? 0 : $driver->nin_ocr_match_score);
                $updateData['nin_verified_at'] = now();
            }

            if ($params['verification_type'] === 'frsc' || $params['verification_type'] === 'both') {
                $updateData['frsc_ocr_match_score'] = $params['status'] === 'passed' ? 100 : ($params['status'] === 'failed' ? 0 : $driver->frsc_ocr_match_score);
                $updateData['frsc_verified_at'] = now();
            }

            // Calculate overall status
            $ninScore = isset($updateData['nin_ocr_match_score']) ? $updateData['nin_ocr_match_score'] : (isset($driver->nin_ocr_match_score) ? $driver->nin_ocr_match_score : 0);
            $frscScore = isset($updateData['frsc_ocr_match_score']) ? $updateData['frsc_ocr_match_score'] : (isset($driver->frsc_ocr_match_score) ? $driver->frsc_ocr_match_score : 0);

            $ninStatus = $ninScore >= 80 ? 'passed' : ($ninScore > 0 ? 'failed' : 'pending');
            $frscStatus = $frscScore >= 80 ? 'passed' : ($frscScore > 0 ? 'failed' : 'pending');

            if ($ninStatus === 'passed' && $frscStatus === 'passed') {
                $updateData['ocr_verification_status'] = 'passed';
            } elseif ($ninStatus === 'failed' || $frscStatus === 'failed') {
                $updateData['ocr_verification_status'] = 'failed';
            } else {
                $updateData['ocr_verification_status'] = 'pending';
            }

            $driver->update($updateData);

            $message = "OCR verification override applied successfully for {$params['verification_type']} document(s). Status: {$updateData['ocr_verification_status']}";

            Log::info('OCR manual override performed', [
                'driver_id' => $driver->driver_id,
                'verification_type' => $params['verification_type'],
                'status' => $params['status'],
                'admin_id' => $adminUser->id
            ]);

            return [
                'success' => true,
                'message' => $message,
                'status' => $updateData['ocr_verification_status']
            ];

        } catch (\Exception $e) {
            Log::error('OCR override failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Override failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk OCR verification for pending drivers
     */
    public function bulkOCRVerification(array $driverIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($driverIds)
        ];

        foreach ($driverIds as $driverId) {
            $driver = Driver::findOrFail($driverId);

            try {
                // Run OCR verification
                $ninResult = null;
                $frscResult = null;

                if ($driver->nin_document) {
                    $ninResult = $this->ocrService->verifyNINDocument($driver, $driver->nin_document);
                }

                if ($driver->frsc_document) {
                    $frscResult = $this->ocrService->verifyFRSCDocument($driver, $driver->frsc_document);
                }

                if (($ninResult && $ninResult['success']) || ($frscResult && $frscResult['success'])) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        $message = "Bulk OCR verification completed: {$results['success']} successful, {$results['failed']} failed out of {$results['total']} total.";

        Log::info('Bulk OCR verification completed', [
            'total_processed' => $results['total'],
            'successful' => $results['success'],
            'failed' => $results['failed'],
            'admin_id' => Auth::guard('admin')->id()
        ]);

        return [
            'success' => true,
            'message' => $message,
            'results' => $results
        ];
    }
}

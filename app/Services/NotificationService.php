<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationService
{
    /**
     * Send notification for step completion
     */
    public function sendStepCompletionNotification(Driver $driver, string $stepKey, array $stepData = []): bool
    {
        try {
            // Email notification
            $this->sendEmailNotification($driver, 'step_completed', [
                'step' => $this->getStepName($stepKey),
                'progress' => app(DriverOnboardingProgressService::class)->calculateProgress($driver),
                'next_step' => app(DriverOnboardingProgressService::class)->getNextStep($driver)
            ]);

            // SMS notification (if phone verified)
            if ($driver->phone_verified_at) {
                $this->sendSMSNotification($driver, 'step_completed', [
                    'step' => $this->getStepName($stepKey)
                ]);
            }

            Log::info("Step completion notification sent for driver {$driver->driver_id}, step: {$stepKey}");
            return true;

        } catch (Exception $e) {
            Log::error("Failed to send step completion notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for onboarding submission
     */
    public function sendOnboardingSubmissionNotification(Driver $driver): bool
    {
        try {
            // Notify driver
            $this->sendEmailNotification($driver, 'onboarding_submitted', [
                'driver_name' => $driver->full_name,
                'submission_date' => now()->format('M d, Y H:i')
            ]);

            // Notify all superadmins
            $superadmins = AdminUser::whereHas('roles', function($query) {
                $query->where('name', 'superadmin');
            })->get();

            foreach ($superadmins as $admin) {
                $this->sendEmailNotification($admin, 'new_onboarding_submission', [
                    'driver_name' => $driver->full_name,
                    'driver_id' => $driver->driver_id,
                    'submission_date' => now()->format('M d, Y H:i'),
                    'review_url' => route('admin.superadmin.drivers.onboarding.review', $driver)
                ], 'admin');
            }

            Log::info("Onboarding submission notifications sent for driver {$driver->driver_id}");
            return true;

        } catch (Exception $e) {
            Log::error("Failed to send onboarding submission notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for admin review decision
     */
    public function sendAdminReviewNotification(Driver $driver, string $decision, string $adminNotes = null): bool
    {
        try {
            $decisionData = [
                'decision' => $decision,
                'admin_notes' => $adminNotes,
                'review_date' => now()->format('M d, Y H:i'),
                'driver_name' => $driver->full_name
            ];

            // Notify driver
            $this->sendEmailNotification($driver, 'onboarding_reviewed', $decisionData);

            if ($driver->phone_verified_at) {
                $this->sendSMSNotification($driver, 'onboarding_reviewed', $decisionData);
            }

            Log::info("Admin review notification sent for driver {$driver->driver_id}, decision: {$decision}");
            return true;

        } catch (Exception $e) {
            Log::error("Failed to send admin review notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($recipient, string $type, array $data, string $recipientType = 'driver'): bool
    {
        try {
            $emailData = array_merge($data, [
                'recipient_type' => $recipientType,
                'recipient' => $recipient
            ]);

            // For now, log the email that would be sent
            // In production, you would use Mail::to() with actual mail templates
            Log::info("Email notification queued", [
                'type' => $type,
                'recipient' => $recipientType === 'driver' ? $recipient->email : $recipient->email,
                'data' => $emailData
            ]);

            // Uncomment when email templates are ready
            /*
            Mail::to($recipientType === 'driver' ? $recipient->email : $recipient->email)
                ->send(new DriverOnboardingNotification($type, $emailData));
            */

            return true;

        } catch (Exception $e) {
            Log::error("Failed to send email notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSMSNotification(Driver $driver, string $type, array $data): bool
    {
        try {
            // For now, log the SMS that would be sent
            // In production, integrate with SMS service like Twilio, AWS SNS, etc.
            Log::info("SMS notification queued", [
                'type' => $type,
                'phone' => $driver->phone,
                'data' => $data
            ]);

            // Uncomment when SMS service is integrated
            /*
            $smsService = app(SMSService::class);
            $message = $this->buildSMSMessage($type, $data);
            $smsService->send($driver->phone, $message);
            */

            return true;

        } catch (Exception $e) {
            Log::error("Failed to send SMS notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get human-readable step name
     */
    private function getStepName(string $stepKey): string
    {
        $stepNames = [
            'personal_info' => 'Personal Information',
            'contact_info' => 'Contact & Emergency Details',
            'documents' => 'Document Upload',
            'banking' => 'Banking Information',
            'professional' => 'Professional Details',
            'verification' => 'Verification'
        ];

        return $stepNames[$stepKey] ?? ucfirst(str_replace('_', ' ', $stepKey));
    }

    /**
     * Build SMS message content
     */
    private function buildSMSMessage(string $type, array $data): string
    {
        switch ($type) {
            case 'step_completed':
                return "Great! You've completed the {$data['step']} step in your Drivelink driver onboarding.";

            case 'onboarding_submitted':
                return "Your Drivelink driver application has been submitted successfully. We'll review it and get back to you soon.";

            case 'onboarding_reviewed':
                $status = $data['decision'] === 'approved' ? 'approved' : 'requires attention';
                return "Your Drivelink driver application has been {$status}. Please check your email for details.";

            default:
                return "Update on your Drivelink driver onboarding application.";
        }
    }

    /**
     * Send bulk notifications (for system announcements)
     */
    public function sendBulkNotification(array $drivers, string $type, array $data): array
    {
        $results = ['success' => 0, 'failed' => 0];

        foreach ($drivers as $driver) {
            if ($this->sendEmailNotification($driver, $type, $data)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        Log::info("Bulk notification completed", [
            'type' => $type,
            'total' => count($drivers),
            'success' => $results['success'],
            'failed' => $results['failed']
        ]);

        return $results;
    }

    /**
     * Send verification notification to driver
     */
    public function sendVerificationNotification(Driver $driver, string $status, string $notes = null): array
    {
        try {
            $message = $this->getVerificationMessage($status, $driver->full_name, $notes);

            // Log the notification
            Log::info('Verification notification sent', [
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
                'status' => $status,
                'message' => $message,
                'notes' => $notes,
            ]);

            // Send email notification
            $this->sendEmailNotification($driver, 'verification_status_update', [
                'status' => $status,
                'message' => $message,
                'notes' => $notes,
                'driver_name' => $driver->full_name
            ]);

            // Send SMS if phone is verified
            if ($driver->phone_verified_at) {
                $this->sendSMSNotification($driver, 'verification_status_update', [
                    'status' => $status,
                    'driver_name' => $driver->full_name
                ]);
            }

            return ['success' => true, 'message' => 'Verification notification sent'];
        } catch (Exception $e) {
            Log::error('Failed to send verification notification', [
                'driver_id' => $driver->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Failed to send verification notification'];
        }
    }

    /**
     * Send document action notification
     */
    public function sendDocumentActionNotification(Driver $driver, string $documentType, string $action, string $notes = null): array
    {
        try {
            $message = $this->getDocumentActionMessage($action, $documentType, $driver->full_name, $notes);

            Log::info('Document action notification sent', [
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
                'document_type' => $documentType,
                'action' => $action,
                'message' => $message,
                'notes' => $notes,
            ]);

            // Send email notification
            $this->sendEmailNotification($driver, 'document_status_update', [
                'document_type' => $documentType,
                'action' => $action,
                'message' => $message,
                'notes' => $notes,
                'driver_name' => $driver->full_name
            ]);

            return ['success' => true, 'message' => 'Document action notification sent'];
        } catch (Exception $e) {
            Log::error('Failed to send document action notification', [
                'driver_id' => $driver->id,
                'document_type' => $documentType,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Failed to send document action notification'];
        }
    }

    /**
     * Get verification message based on status
     */
    private function getVerificationMessage(string $status, string $driverName, string $notes = null): string
    {
        $baseMessage = "Dear {$driverName}, ";

        switch ($status) {
            case 'verified':
                $message = $baseMessage . "Congratulations! Your driver application has been verified and approved. You can now start accepting jobs.";
                break;
            case 'rejected':
                $message = $baseMessage . "We regret to inform you that your driver application has been rejected.";
                if ($notes) {
                    $message .= " Reason: {$notes}";
                }
                $message .= " Please contact support for more information.";
                break;
            case 'pending':
                $message = $baseMessage . "Your driver application status has been reset to pending review. We will notify you once the review is complete.";
                break;
            default:
                $message = $baseMessage . "Your driver application status has been updated to: {$status}.";
        }

        return $message;
    }

    /**
     * Get document action message
     */
    private function getDocumentActionMessage(string $action, string $documentType, string $driverName, string $notes = null): string
    {
        $baseMessage = "Dear {$driverName}, ";

        switch ($action) {
            case 'approved':
                $message = $baseMessage . "Your {$documentType} has been approved and verified.";
                break;
            case 'rejected':
                $message = $baseMessage . "Your {$documentType} has been rejected.";
                if ($notes) {
                    $message .= " Reason: {$notes}";
                }
                $message .= " Please upload a new document.";
                break;
            default:
                $message = $baseMessage . "Your {$documentType} status has been updated to: {$action}.";
        }

        return $message;
    }

    /**
     * Send KYC notification to driver
     */
    public function sendKycNotification(Driver $driver, string $action, string $notes, bool $canRetry = false): array
    {
        try {
            $message = $this->getKycMessage($action, $driver->full_name, $notes, $canRetry);

            // Log the notification
            Log::info('KYC notification sent', [
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
                'action' => $action,
                'message' => $message,
                'notes' => $notes,
                'can_retry' => $canRetry,
            ]);

            // Send email notification
            $this->sendEmailNotification($driver, 'kyc_status_update', [
                'action' => $action,
                'message' => $message,
                'notes' => $notes,
                'can_retry' => $canRetry,
                'driver_name' => $driver->full_name
            ]);

            // Send SMS if phone is verified
            if ($driver->phone_verified_at) {
                $this->sendSMSNotification($driver, 'kyc_status_update', [
                    'action' => $action,
                    'driver_name' => $driver->full_name,
                    'can_retry' => $canRetry
                ]);
            }

            return ['success' => true, 'message' => 'KYC notification sent'];
        } catch (Exception $e) {
            Log::error('Failed to send KYC notification', [
                'driver_id' => $driver->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Failed to send KYC notification'];
        }
    }

    /**
     * Send KYC info request notification
     */
    public function sendKycInfoRequestNotification(Driver $driver, string $infoRequest): array
    {
        try {
            $message = "Dear {$driver->full_name}, we need additional information for your KYC verification: {$infoRequest}. Please update your profile with the requested details.";

            // Log the notification
            Log::info('KYC info request notification sent', [
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
                'info_request' => $infoRequest,
                'message' => $message,
            ]);

            // Send email notification
            $this->sendEmailNotification($driver, 'kyc_info_request', [
                'info_request' => $infoRequest,
                'message' => $message,
                'driver_name' => $driver->full_name
            ]);

            // Send SMS if phone is verified
            if ($driver->phone_verified_at) {
                $this->sendSMSNotification($driver, 'kyc_info_request', [
                    'info_request' => $infoRequest,
                    'driver_name' => $driver->full_name
                ]);
            }

            return ['success' => true, 'message' => 'KYC info request notification sent'];
        } catch (Exception $e) {
            Log::error('Failed to send KYC info request notification', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Failed to send KYC info request notification'];
        }
    }

    /**
     * Get KYC message based on action
     */
    private function getKycMessage(string $action, string $driverName, string $notes = null, bool $canRetry = false): string
    {
        $baseMessage = "Dear {$driverName}, ";

        switch ($action) {
            case 'approved':
                $message = $baseMessage . "Congratulations! Your KYC verification has been approved. Your driver account is now fully verified and active.";
                if ($notes) {
                    $message .= " Notes: {$notes}";
                }
                break;
            case 'rejected':
                $message = $baseMessage . "We regret to inform you that your KYC verification has been rejected.";
                if ($notes) {
                    $message .= " Reason: {$notes}";
                }
                if ($canRetry) {
                    $message .= " You may retry the KYC process with corrected information.";
                }
                $message .= " Please contact support for assistance.";
                break;
            case 'info_request':
                $message = $baseMessage . "Additional information is required for your KYC verification: {$notes}. Please update your profile with the requested details.";
                break;
            default:
                $message = $baseMessage . "Your KYC verification status has been updated to: {$action}.";
        }

        return $message;
    }

    /**
     * Queue notification for later sending (for performance)
     */
    public function queueNotification(string $type, $recipient, array $data, string $channel = 'email', int $delay = 0): bool
    {
        try {
            // In production, dispatch to a queue
            // NotificationJob::dispatch($type, $recipient, $data, $channel)->delay($delay);

            Log::info("Notification queued", [
                'type' => $type,
                'channel' => $channel,
                'delay' => $delay,
                'recipient' => is_object($recipient) ? $recipient->email : $recipient
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Failed to queue notification: " . $e->getMessage());
            return false;
        }
    }
}

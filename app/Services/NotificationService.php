<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Drivers as Driver;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Send driver verification status notification
     */
    public function sendVerificationNotification(Driver $driver, string $status, string $notes = null)
    {
        try {
            // Log notification attempt
            Log::info("Sending verification notification", [
                'driver_id' => $driver->driver_id,
                'status' => $status,
                'email' => $driver->email
            ]);

            // Create notification data
            $notificationData = [
                'driver' => $driver,
                'status' => $status,
                'notes' => $notes,
                'verification_url' => route('driver.dashboard'), // This would be the driver portal URL
                'admin_contact' => 'support@drivelink.com',
                'company_name' => config('app.name', 'Drivelink')
            ];

            // Send email if driver has email address
            if ($driver->email) {
                $this->sendEmail(
                    $driver->email,
                    'Driver Verification Status Update',
                    'emails.driver-verification-status',
                    $notificationData
                );
            }

            // Send SMS if driver has phone (placeholder for SMS integration)
            if ($driver->phone) {
                $this->sendSMS($driver->phone, $this->generateSMSMessage($status, $driver->full_name));
            }

            // Store notification in database
            $this->storeNotification([
                'recipient_type' => 'driver',
                'recipient_id' => $driver->id,
                'type' => 'verification_status',
                'title' => 'Verification Status Update',
                'message' => "Your verification status has been updated to: {$status}",
                'data' => json_encode($notificationData),
                'sent_at' => now(),
                'sent_via' => $driver->email ? 'email,sms' : 'sms'
            ]);

            return [
                'success' => true,
                'message' => 'Notification sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send verification notification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send password reset notification to admin
     */
    public function sendAdminPasswordResetNotification(AdminUser $admin, string $resetToken)
    {
        try {
            $resetUrl = route('admin.password.reset', $resetToken) . '?email=' . urlencode($admin->email);

            $notificationData = [
                'admin' => $admin,
                'reset_url' => $resetUrl,
                'expires_at' => now()->addHours(24),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];

            if (config('mail.default') !== null) {
                // Send actual email in production
                $this->sendEmail(
                    $admin->email,
                    'Admin Password Reset Request',
                    'emails.admin-password-reset',
                    $notificationData
                );
            } else {
                // Log reset URL for development
                Log::info('Admin Password Reset URL', [
                    'admin_email' => $admin->email,
                    'reset_url' => $resetUrl,
                    'expires_at' => $notificationData['expires_at']
                ]);
            }

            // Store notification
            $this->storeNotification([
                'recipient_type' => 'admin',
                'recipient_id' => $admin->id,
                'type' => 'password_reset',
                'title' => 'Password Reset Request',
                'message' => 'A password reset was requested for your admin account',
                'data' => json_encode($notificationData),
                'sent_at' => now(),
                'sent_via' => 'email'
            ]);

            return [
                'success' => true,
                'message' => 'Password reset notification sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send admin password reset notification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send password reset notification'
            ];
        }
    }

    /**
     * Send bulk notification to multiple recipients
     */
    public function sendBulkNotification(array $recipients, string $title, string $message, array $data = [])
    {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            try {
                $this->sendSingleNotification($recipient, $title, $message, $data);
                $results['sent']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ];
                Log::error('Bulk notification failed', [
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send OCR verification result notification
     */
    public function sendOCRVerificationNotification(Driver $driver, array $ocrResults)
    {
        try {
            $overallStatus = $ocrResults['overall_status'] ?? 'pending';
            $ninScore = $ocrResults['nin_score'] ?? 0;
            $frscScore = $ocrResults['frsc_score'] ?? 0;

            $notificationData = [
                'driver' => $driver,
                'ocr_results' => $ocrResults,
                'overall_status' => $overallStatus,
                'nin_score' => $ninScore,
                'frsc_score' => $frscScore,
                'verification_date' => now(),
                'next_steps' => $this->getOCRNextSteps($overallStatus)
            ];

            // Send email notification
            if ($driver->email) {
                $this->sendEmail(
                    $driver->email,
                    'Document Verification Results',
                    'emails.driver-ocr-results',
                    $notificationData
                );
            }

            // Store notification
            $this->storeNotification([
                'recipient_type' => 'driver',
                'recipient_id' => $driver->id,
                'type' => 'ocr_verification',
                'title' => 'Document Verification Completed',
                'message' => "Your document verification is complete. Status: {$overallStatus}",
                'data' => json_encode($notificationData),
                'sent_at' => now(),
                'sent_via' => $driver->email ? 'email' : 'system'
            ]);

            return [
                'success' => true,
                'message' => 'OCR verification notification sent'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send OCR notification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send OCR notification'
            ];
        }
    }

    /**
     * Send document approval/rejection notification
     */
    public function sendDocumentActionNotification(Driver $driver, string $documentType, string $action, string $notes = null)
    {
        try {
            $notificationData = [
                'driver' => $driver,
                'document_type' => $documentType,
                'action' => $action,
                'notes' => $notes,
                'action_date' => now(),
                'document_type_name' => $this->getDocumentTypeName($documentType)
            ];

            if ($driver->email) {
                $this->sendEmail(
                    $driver->email,
                    "Document {$action} - {$notificationData['document_type_name']}",
                    'emails.driver-document-action',
                    $notificationData
                );
            }

            // Store notification
            $this->storeNotification([
                'recipient_type' => 'driver',
                'recipient_id' => $driver->id,
                'type' => 'document_action',
                'title' => "Document {$action}",
                'message' => "Your {$notificationData['document_type_name']} has been {$action}",
                'data' => json_encode($notificationData),
                'sent_at' => now(),
                'sent_via' => $driver->email ? 'email' : 'system'
            ]);

            return [
                'success' => true,
                'message' => 'Document action notification sent'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send document action notification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send document action notification'
            ];
        }
    }

    /**
     * Send welcome notification to new driver
     */
    public function sendDriverWelcomeNotification(Driver $driver)
    {
        try {
            $notificationData = [
                'driver' => $driver,
                'welcome_message' => 'Welcome to Drivelink! Your driver account has been created successfully.',
                'next_steps' => [
                    'Complete your profile information',
                    'Upload required documents',
                    'Wait for verification approval',
                    'Start receiving job opportunities'
                ],
                'support_contact' => 'support@drivelink.com',
                'mobile_app_url' => '#' // This would be the actual app store URL
            ];

            if ($driver->email) {
                $this->sendEmail(
                    $driver->email,
                    'Welcome to Drivelink!',
                    'emails.driver-welcome',
                    $notificationData
                );
            }

            // Store notification
            $this->storeNotification([
                'recipient_type' => 'driver',
                'recipient_id' => $driver->id,
                'type' => 'welcome',
                'title' => 'Welcome to Drivelink',
                'message' => 'Your driver account has been created successfully',
                'data' => json_encode($notificationData),
                'sent_at' => now(),
                'sent_via' => $driver->email ? 'email' : 'system'
            ]);

            return [
                'success' => true,
                'message' => 'Welcome notification sent'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send welcome notification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send welcome notification'
            ];
        }
    }

    /**
     * Notify admins of KYC submission
     */
    public function notifyAdminsOfKycSubmission(Driver $driver)
    {
        try {
            // Get all admin users
            $admins = AdminUser::where('status', 'active')->get();

            if ($admins->isEmpty()) {
                Log::warning('No active admins found to notify of KYC submission', [
                    'driver_id' => $driver->driver_id
                ]);
                return [
                    'success' => false,
                    'message' => 'No active admins found'
                ];
            }

            $notificationData = [
                'driver' => $driver,
                'kyc_data' => [
                    'driver_id' => $driver->driver_id,
                    'full_name' => $driver->full_name,
                    'email' => $driver->email,
                    'phone' => $driver->phone,
                    'kyc_step' => $driver->kyc_step,
                    'kyc_status' => $driver->kyc_status,
                    'submitted_at' => $driver->kyc_submitted_at,
                ],
                'review_url' => route('admin.drivers.kyc-review', $driver->id),
                'admin_dashboard_url' => route('admin.dashboard'),
                'company_name' => config('app.name', 'Drivelink')
            ];

            $successCount = 0;
            $errors = [];

            foreach ($admins as $admin) {
                try {
                    // Send email notification
                    if ($admin->email) {
                        $this->sendEmail(
                            $admin->email,
                            'New KYC Application Submitted - ' . $driver->full_name,
                            'emails.admin-kyc-submission',
                            array_merge($notificationData, ['admin' => $admin])
                        );
                    }

                    // Store notification in database
                    $this->storeNotification([
                        'recipient_type' => 'admin',
                        'recipient_id' => $admin->id,
                        'type' => 'kyc_submission',
                        'title' => 'New KYC Application Submitted',
                        'message' => "New KYC application submitted by {$driver->full_name} ({$driver->driver_id})",
                        'data' => json_encode($notificationData),
                        'sent_at' => now(),
                        'sent_via' => $admin->email ? 'email' : 'system'
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'error' => $e->getMessage()
                    ];
                    Log::error('Failed to notify admin of KYC submission', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'driver_id' => $driver->driver_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $result = [
                'success' => $successCount > 0,
                'total_admins' => $admins->count(),
                'notified_admins' => $successCount,
                'errors' => $errors
            ];

            if ($successCount > 0) {
                Log::info('KYC submission notification sent to admins', [
                    'driver_id' => $driver->driver_id,
                    'total_admins' => $admins->count(),
                    'notified_admins' => $successCount
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to notify admins of KYC submission: ' . $e->getMessage(), [
                'driver_id' => $driver->driver_id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to notify admins: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email using Laravel Mail
     */
    private function sendEmail(string $to, string $subject, string $view, array $data)
    {
        if (config('mail.default') === null) {
            // Log email content for development
            Log::info('Email notification (no mail configured)', [
                'to' => $to,
                'subject' => $subject,
                'data' => $data
            ]);
            return;
        }

        // In production, send actual email
        try {
            Mail::send($view, $data, function($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
                $message->from(config('mail.from.address'), config('mail.from.name'));
            });
        } catch (\Exception $e) {
            // Fallback to logging if mail fails
            Log::warning('Email sending failed, logging instead', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS (placeholder for SMS integration)
     */
    private function sendSMS(string $phone, string $message)
    {
        // This is a placeholder for SMS integration
        // You would integrate with services like Twilio, Nexmo, etc.
        Log::info('SMS notification (placeholder)', [
            'phone' => $phone,
            'message' => $message
        ]);
    }

    /**
     * Store notification in database
     */
    private function storeNotification(array $data)
    {
        try {
            DB::table('notifications')->insert(array_merge($data, [
                'id' => \Illuminate\Support\Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]));
        } catch (\Exception $e) {
            // Don't fail the whole notification if database storage fails
            Log::warning('Failed to store notification in database: ' . $e->getMessage());
        }
    }

    /**
     * Send single notification
     */
    private function sendSingleNotification($recipient, string $title, string $message, array $data = [])
    {
        // Implementation depends on recipient type
        // This is a simplified version
        if (is_array($recipient) && isset($recipient['email'])) {
            $this->sendEmail($recipient['email'], $title, 'emails.generic-notification', array_merge($data, [
                'title' => $title,
                'message' => $message
            ]));
        }
    }

    /**
     * Generate SMS message for verification status
     */
    private function generateSMSMessage(string $status, string $driverName): string
    {
        $messages = [
            'verified' => "Hi {$driverName}, your Drivelink driver verification has been APPROVED! You can now start receiving job opportunities.",
            'rejected' => "Hi {$driverName}, your Drivelink driver verification was not approved. Please contact support for details.",
            'pending' => "Hi {$driverName}, your Drivelink driver verification is under review. We'll notify you once it's complete.",
        ];

        return $messages[$status] ?? "Hi {$driverName}, your Drivelink verification status has been updated to: {$status}";
    }

    /**
     * Get OCR next steps based on status
     */
    private function getOCRNextSteps(string $status): array
    {
        switch ($status) {
            case 'passed':
                return [
                    'Your documents have been successfully verified',
                    'You can now proceed to the next verification stage',
                    'Check your dashboard for available opportunities'
                ];
            case 'failed':
                return [
                    'Some documents failed verification',
                    'Please upload clearer images of your documents',
                    'Contact support if you need assistance'
                ];
            default:
                return [
                    'Document verification is in progress',
                    'You will be notified once verification is complete',
                    'Ensure all required documents are uploaded'
                ];
        }
    }

    /**
     * Get document type display name
     */
    private function getDocumentTypeName(string $documentType): string
    {
        $names = [
            'nin' => 'NIN Document',
            'license_front' => 'Driver License (Front)',
            'license_back' => 'Driver License (Back)',
            'profile_picture' => 'Profile Picture',
            'passport_photo' => 'Passport Photograph',
            'employment_letter' => 'Employment Letter',
            'service_certificate' => 'Service Certificate'
        ];

        return $names[$documentType] ?? ucfirst(str_replace('_', ' ', $documentType));
    }
}

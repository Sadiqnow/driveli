<?php

namespace App\Listeners;

use App\Events\DriverKycCompleted;
use App\Models\AdminUser;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminsOfKycCompletion implements ShouldQueue
{
    use InteractsWithQueue;

    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(DriverKycCompleted $event): void
    {
        try {
            $driver = $event->driver;
            $completionData = $event->completionData;

            // Log KYC completion
            Log::info('Driver KYC Completed', [
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
                'email' => $driver->email,
                'completion_data' => $completionData,
            ]);

            // Notify all active admin users
            $activeAdmins = AdminUser::where('is_active', true)
                ->where('status', 'active')
                ->get();

            foreach ($activeAdmins as $admin) {
                $this->sendAdminNotification($admin, $driver, $completionData);
            }

            // Send email notifications if configured
            if (config('drivelink.kyc.email_notifications', true)) {
                $this->sendEmailNotifications($driver, $completionData);
            }

            // Update driver status for immediate review
            $this->updateDriverForReview($driver);

        } catch (\Exception $e) {
            Log::error('Error handling KYC completion notification', [
                'driver_id' => $event->driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't fail the entire process if notifications fail
            // Re-queue for retry
            $this->release(300); // Retry in 5 minutes
        }
    }

    /**
     * Send notification to admin user.
     */
    private function sendAdminNotification(AdminUser $admin, $driver, array $completionData): void
    {
        $this->notificationService->create([
            'recipient_type' => 'admin',
            'recipient_id' => $admin->id,
            'type' => 'kyc_completion',
            'title' => 'New KYC Verification Submitted',
            'message' => "Driver {$driver->full_name} has completed KYC verification and requires review.",
            'data' => [
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
                'driver_email' => $driver->email,
                'driver_phone' => $driver->phone,
                'completion_data' => $completionData,
                'action_url' => route('admin.drivers.show', $driver->id),
                'review_url' => route('admin.drivers.verification', $driver->id),
            ],
            'priority' => 'high',
            'category' => 'verification',
        ]);
    }

    /**
     * Send email notifications to relevant parties.
     */
    private function sendEmailNotifications($driver, array $completionData): void
    {
        // Send confirmation email to driver
        try {
            Mail::send('emails.driver-kyc-completed', [
                'driver' => $driver,
                'completionData' => $completionData,
            ], function($message) use ($driver) {
                $message->to($driver->email, $driver->full_name)
                    ->subject('KYC Verification Submitted - DriveLink')
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

        } catch (\Exception $e) {
            Log::error('Failed to send KYC completion email to driver', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send notification email to admin team
        $adminEmails = config('drivelink.kyc.admin_notification_emails', []);
        if (!empty($adminEmails)) {
            try {
                Mail::send('emails.admin-kyc-review-needed', [
                    'driver' => $driver,
                    'completionData' => $completionData,
                    'reviewUrl' => route('admin.drivers.verification', $driver->id),
                ], function($message) use ($adminEmails, $driver) {
                    $message->to($adminEmails)
                        ->subject('KYC Review Required - ' . $driver->full_name)
                        ->from(config('mail.from.address'), config('mail.from.name'));
                });

            } catch (\Exception $e) {
                Log::error('Failed to send KYC review email to admins', [
                    'driver_id' => $driver->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update driver status for immediate review consideration.
     */
    private function updateDriverForReview($driver): void
    {
        // Set verification status to reviewing if it was pending
        if ($driver->verification_status === 'pending') {
            $driver->update([
                'verification_status' => 'reviewing',
                'status' => 'inactive', // Keep inactive until verified
            ]);
        }

        // Log the status change
        Log::info('Driver status updated for KYC review', [
            'driver_id' => $driver->id,
            'verification_status' => $driver->verification_status,
            'kyc_status' => $driver->kyc_status,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(DriverKycCompleted $event, \Throwable $exception): void
    {
        Log::error('KYC completion notification job failed permanently', [
            'driver_id' => $event->driver->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Could send a critical alert to system administrators
        // or store in a failed jobs table for manual processing
    }
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DriverVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $verificationData;

    public function __construct($verificationData)
    {
        $this->verificationData = $verificationData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast']; // Added broadcast for real-time notifications
    }

    public function toMail($notifiable)
    {
        $driver = $this->verificationData['driver'] ?? null;
        $score = $this->verificationData['score'] ?? 'N/A';
        $status = $this->verificationData['status'] ?? 'completed';

        return (new MailMessage)
            ->subject('Driver Verification Completed - ' . ($driver ? $driver->first_name . ' ' . $driver->surname : 'Unknown Driver'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A driver verification process has been completed for your company.')
            ->line('**Verification Summary:**')
            ->line('• Driver: ' . ($driver ? $driver->first_name . ' ' . $driver->surname : 'Unknown'))
            ->line('• License Number: ' . ($driver ? $driver->license_number : 'N/A'))
            ->line('• Verification Score: ' . $score . '%')
            ->line('• Status: ' . ucfirst($status))
            ->line('• Completed At: ' . now()->format('Y-m-d H:i:s'))
            ->action('View Full Report', url('/admin/drivers/' . ($driver ? $driver->id : '')))
            ->line('**Next Steps:**')
            ->line('1. Review the verification report for any discrepancies.')
            ->line('2. Contact the driver if additional information is needed.')
            ->line('3. Update your driver records accordingly.')
            ->salutation('Best regards, Drivelink Team');
    }

    public function toSms($notifiable)
    {
        // SMS stub - queue for SMS service integration
        $driver = $this->verificationData['driver'] ?? null;
        $message = 'Driver verification completed: ' . ($driver ? $driver->first_name . ' ' . $driver->surname : 'Unknown Driver') .
                   '. Score: ' . ($this->verificationData['score'] ?? 'N/A') . '%. Check dashboard for details.';

        // TODO: Integrate with SMS service (e.g., Twilio, AWS SNS)
        // For now, log the SMS content
        \Log::info('SMS Notification Stub', [
            'recipient' => $notifiable->phone ?? 'No phone number',
            'message' => $message,
            'driver_id' => $driver ? $driver->id : null
        ]);

        return $message;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Driver Verification Completed',
            'message' => 'A driver has been successfully verified.',
            'data' => [
                'driver_id' => $this->verificationData['driver']->id ?? null,
                'score' => $this->verificationData['score'] ?? null,
                'status' => $this->verificationData['status'] ?? 'completed',
                'completed_at' => now()->toISOString()
            ],
            'action_url' => '/admin/drivers/' . ($this->verificationData['driver']->id ?? ''),
            'type' => 'driver_verification'
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Driver Verification Completed',
            'message' => 'A driver verification process has been completed for your company.',
            'data' => $this->verificationData,
            'action_url' => '/admin/drivers/' . ($this->verificationData['driver']->id ?? ''),
            'type' => 'driver_verification',
            'created_at' => now()->toISOString()
        ];
    }
}

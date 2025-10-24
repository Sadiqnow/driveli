<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;

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
        $driver = $this->verificationData['driver'] ?? null;
        $message = 'Driver verification completed: ' . ($driver ? $driver->first_name . ' ' . $driver->surname : 'Unknown Driver') .
                   '. Score: ' . ($this->verificationData['score'] ?? 'N/A') . '%. Check dashboard for details.';

        // Integrate with SMS service
        try {
            $this->sendSms($notifiable, $message);
        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage(), [
                'recipient' => $notifiable->phone ?? 'No phone number',
                'driver_id' => $driver ? $driver->id : null
            ]);
        }

        return $message;
    }

    /**
     * Send SMS using configured service
     */
    private function sendSms($notifiable, $message)
    {
        $phone = $notifiable->phone ?? null;
        if (!$phone) {
            \Log::warning('No phone number available for SMS notification');
            return;
        }

        $smsService = config('services.sms.provider', 'twilio');

        switch ($smsService) {
            case 'twilio':
                $this->sendTwilioSms($phone, $message);
                break;
            case 'aws':
                $this->sendAwsSms($phone, $message);
                break;
            default:
                \Log::warning('Unknown SMS provider: ' . $smsService);
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendTwilioSms($phone, $message)
    {
        try {
            $twilioSid = config('services.twilio.sid');
            $twilioToken = config('services.twilio.token');
            $twilioFrom = config('services.twilio.from');

            if (!$twilioSid || !$twilioToken || !$twilioFrom) {
                throw new \Exception('Twilio credentials not configured');
            }

            $client = new \Twilio\Rest\Client($twilioSid, $twilioToken);
            $client->messages->create($phone, [
                'from' => $twilioFrom,
                'body' => $message
            ]);

            \Log::info('SMS sent via Twilio', ['phone' => $phone]);

        } catch (\Exception $e) {
            \Log::error('Twilio SMS failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send SMS via AWS SNS
     */
    private function sendAwsSms($phone, $message)
    {
        try {
            $sns = new \Aws\Sns\SnsClient([
                'version' => 'latest',
                'region' => config('services.aws.region', 'us-east-1'),
                'credentials' => [
                    'key' => config('services.aws.key'),
                    'secret' => config('services.aws.secret'),
                ],
            ]);

            $sns->publish([
                'Message' => $message,
                'PhoneNumber' => $phone,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional'
                    ]
                ]
            ]);

            \Log::info('SMS sent via AWS SNS', ['phone' => $phone]);

        } catch (\Exception $e) {
            \Log::error('AWS SNS SMS failed: ' . $e->getMessage());
            throw $e;
        }
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

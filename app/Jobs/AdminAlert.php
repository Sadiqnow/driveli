<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\AdminUser;
use App\Mail\AdminAlertNotification;

class AdminAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $message;
    protected $severity;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, string $severity = 'error', array $data = [])
    {
        $this->message = $message;
        $this->severity = $severity;
        $this->data = $data;
        $this->queue = 'alerts';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending admin alert', [
                'message' => $this->message,
                'severity' => $this->severity
            ]);

            // Get all super admin users
            $superAdmins = AdminUser::whereHas('roles', function($query) {
                $query->where('name', 'SuperAdmin');
            })->get();

            if ($superAdmins->isEmpty()) {
                Log::warning('No super admin users found for alert notification');
                return;
            }

            // Send email alerts to all super admins
            foreach ($superAdmins as $admin) {
                if ($admin->email) {
                    Mail::to($admin->email)
                        ->send(new AdminAlertNotification($admin, $this->message, $this->severity, $this->data));
                }
            }

            // Send SMS alerts if configured
            $this->sendSmsAlerts($superAdmins);

            // Send push notifications if device tokens available
            $this->sendPushNotifications($superAdmins);

        } catch (\Exception $e) {
            Log::error('Admin alert sending failed: ' . $e->getMessage(), [
                'message' => $this->message,
                'severity' => $this->severity
            ]);

            throw $e;
        }
    }

    /**
     * Send SMS alerts to super admins
     */
    private function sendSmsAlerts($superAdmins)
    {
        foreach ($superAdmins as $admin) {
            if ($admin->phone) {
                try {
                    $this->sendSms($admin, $this->message);
                } catch (\Exception $e) {
                    Log::error('SMS alert failed for admin ' . $admin->id . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Send push notifications to super admins
     */
    private function sendPushNotifications($superAdmins)
    {
        foreach ($superAdmins as $admin) {
            if ($admin->device_token) {
                try {
                    $this->sendPushNotification($admin, $this->message, $this->severity);
                } catch (\Exception $e) {
                    Log::error('Push notification failed for admin ' . $admin->id . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Send SMS using configured service
     */
    private function sendSms($admin, $message)
    {
        $smsService = config('services.sms.provider', 'twilio');

        switch ($smsService) {
            case 'twilio':
                $this->sendTwilioSms($admin->phone, $message);
                break;
            case 'aws':
                $this->sendAwsSms($admin->phone, $message);
                break;
            default:
                Log::warning('Unknown SMS provider: ' . $smsService);
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

            Log::info('SMS alert sent via Twilio', ['phone' => $phone]);

        } catch (\Exception $e) {
            Log::error('Twilio SMS alert failed: ' . $e->getMessage());
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

            Log::info('SMS alert sent via AWS SNS', ['phone' => $phone]);

        } catch (\Exception $e) {
            Log::error('AWS SNS SMS alert failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification($admin, $message, $severity)
    {
        try {
            // Use Firebase Cloud Messaging (FCM) for push notifications
            $fcmServerKey = config('services.fcm.server_key');

            if (!$fcmServerKey) {
                Log::warning('FCM server key not configured for push notifications');
                return;
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Authorization' => 'key=' . $fcmServerKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to' => $admin->device_token,
                    'notification' => [
                        'title' => 'Admin Alert - ' . ucfirst($severity),
                        'body' => $message,
                        'icon' => 'alert',
                        'sound' => 'default'
                    ],
                    'data' => [
                        'severity' => $severity,
                        'type' => 'admin_alert'
                    ]
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                Log::info('Push notification sent successfully', ['admin_id' => $admin->id]);
            } else {
                Log::error('Push notification failed with status: ' . $response->getStatusCode());
            }

        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

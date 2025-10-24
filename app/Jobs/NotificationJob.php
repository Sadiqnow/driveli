<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\DriverMatchNotification;
use App\Mail\CompanyMatchNotification;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $recipient;
    protected $type;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($recipient, string $type, array $data = [])
    {
        $this->recipient = $recipient;
        $this->type = $type;
        $this->data = $data;
        $this->queue = 'notifications';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending notification', [
                'type' => $this->type,
                'recipient_type' => get_class($this->recipient),
                'recipient_id' => $this->recipient->id
            ]);

            switch ($this->type) {
                case 'match_assigned':
                    $this->sendDriverMatchNotification();
                    break;
                case 'driver_assigned':
                    $this->sendCompanyMatchNotification();
                    break;
                default:
                    Log::warning('Unknown notification type: ' . $this->type);
            }

        } catch (\Exception $e) {
            Log::error('Notification sending failed: ' . $e->getMessage(), [
                'type' => $this->type,
                'recipient_id' => $this->recipient->id ?? null
            ]);

            throw $e;
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification($recipient, $data)
    {
        if (!$recipient->phone) {
            return;
        }

        $message = $this->buildSmsMessage($this->type, $data);

        try {
            $smsService = config('services.sms.provider', 'twilio');

            switch ($smsService) {
                case 'twilio':
                    $this->sendTwilioSms($recipient->phone, $message);
                    break;
                case 'aws':
                    $this->sendAwsSms($recipient->phone, $message);
                    break;
                default:
                    Log::warning('Unknown SMS provider: ' . $smsService);
            }
        } catch (\Exception $e) {
            Log::error('SMS notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification($recipient, $data)
    {
        if (!$recipient->device_token) {
            return;
        }

        try {
            $fcmServerKey = config('services.fcm.server_key');

            if (!$fcmServerKey) {
                Log::warning('FCM server key not configured for push notifications');
                return;
            }

            $notificationData = $this->buildPushNotificationData($this->type, $data);

            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Authorization' => 'key=' . $fcmServerKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to' => $recipient->device_token,
                    'notification' => $notificationData['notification'],
                    'data' => $notificationData['data']
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                Log::info('Push notification sent successfully', ['recipient_id' => $recipient->id]);
            } else {
                Log::error('Push notification failed with status: ' . $response->getStatusCode());
            }

        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Build SMS message based on notification type
     */
    private function buildSmsMessage($type, $data)
    {
        switch ($type) {
            case 'match_assigned':
                return "You've been assigned to a new job. Check your dashboard for details.";
            case 'driver_assigned':
                return "A driver has been assigned to your request. Check your dashboard for details.";
            default:
                return "You have a new notification. Check your dashboard.";
        }
    }

    /**
     * Build push notification data
     */
    private function buildPushNotificationData($type, $data)
    {
        switch ($type) {
            case 'match_assigned':
                return [
                    'notification' => [
                        'title' => 'New Job Assignment',
                        'body' => 'You have been assigned to a new job',
                        'sound' => 'default'
                    ],
                    'data' => [
                        'type' => 'match_assigned',
                        'match_id' => $data['match_id'] ?? null
                    ]
                ];
            case 'driver_assigned':
                return [
                    'notification' => [
                        'title' => 'Driver Assigned',
                        'body' => 'A driver has been assigned to your request',
                        'sound' => 'default'
                    ],
                    'data' => [
                        'type' => 'driver_assigned',
                        'request_id' => $data['request_id'] ?? null
                    ]
                ];
            default:
                return [
                    'notification' => [
                        'title' => 'New Notification',
                        'body' => 'You have a new notification',
                        'sound' => 'default'
                    ],
                    'data' => [
                        'type' => $type
                    ]
                ];
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

            Log::info('SMS sent via Twilio', ['phone' => $phone]);

        } catch (\Exception $e) {
            Log::error('Twilio SMS failed: ' . $e->getMessage());
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

            Log::info('SMS sent via AWS SNS', ['phone' => $phone]);

        } catch (\Exception $e) {
            Log::error('AWS SNS SMS failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send notification to driver about match assignment.
     */
    private function sendDriverMatchNotification(): void
    {
        if ($this->recipient->email) {
            Mail::to($this->recipient->email)
                ->send(new DriverMatchNotification($this->recipient, $this->data));
        }

        // Send SMS notification if phone number available
        $this->sendSmsNotification($this->recipient, $this->data);

        // Send push notification if device token available
        $this->sendPushNotification($this->recipient, $this->data);
    }

    /**
     * Send notification to company about driver assignment.
     */
    private function sendCompanyMatchNotification(): void
    {
        if ($this->recipient->email) {
            Mail::to($this->recipient->email)
                ->send(new CompanyMatchNotification($this->recipient, $this->data));
        }

        // Send SMS notification if phone number available
        $this->sendSmsNotification($this->recipient, $this->data);

        // Send push notification if device token available
        $this->sendPushNotification($this->recipient, $this->data);
    }
}

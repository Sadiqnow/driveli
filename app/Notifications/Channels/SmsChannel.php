<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        $phone = $notifiable->phone ?? $notifiable->phone_number;

        if (!$phone) {
            return;
        }

        $provider = config('services.sms.provider', 'twilio');

        switch ($provider) {
            case 'twilio':
                $this->sendViaTwilio($phone, $message);
                break;
            case 'termii':
                $this->sendViaTermii($phone, $message);
                break;
        }
    }

    protected function sendViaTwilio($phone, $message)
    {
        $sid = config('services.sms.twilio.sid');
        $token = config('services.sms.twilio.token');
        $from = config('services.sms.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::warning('Twilio SMS configuration incomplete');
            return;
        }

        try {
            // For now, just log the SMS - Twilio SDK would need to be installed
            Log::info('SMS would be sent via Twilio', [
                'to' => $phone,
                'from' => $from,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Twilio SMS sending failed: ' . $e->getMessage());
        }
    }

    protected function sendViaTermii($phone, $message)
    {
        $apiKey = config('services.sms.termii.api_key');
        $senderId = config('services.sms.termii.sender_id');

        if (!$apiKey || !$senderId) {
            Log::warning('Termii SMS configuration incomplete');
            return;
        }

        try {
            $response = Http::post('https://api.ng.termii.com/api/sms/send', [
                'to' => $phone,
                'from' => $senderId,
                'sms' => $message,
                'type' => 'plain',
                'api_key' => $apiKey,
                'channel' => 'generic',
            ]);

            if ($response->failed()) {
                Log::error('Termii SMS sending failed: ' . $response->body());
            } else {
                Log::info('SMS sent via Termii', ['to' => $phone, 'response' => $response->json()]);
            }
        } catch (\Exception $e) {
            Log::error('Termii SMS sending failed: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class SMSService
{
    /**
     * Send SMS using Twilio
     */
    public function sendViaTwilio(string $to, string $message): array
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            if (!$sid || !$token || !$from) {
                throw new Exception('Twilio credentials not configured');
            }

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info('SMS sent via Twilio', [
                    'to' => $to,
                    'message_sid' => $response->json('sid')
                ]);

                return [
                    'success' => true,
                    'provider' => 'twilio',
                    'message_id' => $response->json('sid'),
                    'cost' => $response->json('price')
                ];
            } else {
                throw new Exception('Twilio API error: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Twilio SMS failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'provider' => 'twilio',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS using Termii (Nigerian SMS provider)
     */
    public function sendViaTermii(string $to, string $message): array
    {
        try {
            $apiKey = config('services.termii.api_key');
            $senderId = config('services.termii.sender_id');

            if (!$apiKey || !$senderId) {
                throw new Exception('Termii credentials not configured');
            }

            // Format Nigerian phone number
            $to = $this->formatNigerianPhoneNumber($to);

            $response = Http::post('https://api.ng.termii.com/api/sms/send', [
                'api_key' => $apiKey,
                'to' => $to,
                'from' => $senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('SMS sent via Termii', [
                    'to' => $to,
                    'message_id' => $data['message_id'] ?? null
                ]);

                return [
                    'success' => true,
                    'provider' => 'termii',
                    'message_id' => $data['message_id'] ?? null,
                    'balance' => $data['balance'] ?? null
                ];
            } else {
                throw new Exception('Termii API error: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Termii SMS failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'provider' => 'termii',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS with fallback providers
     */
    public function sendSMS(string $to, string $message): array
    {
        // Try primary provider first (Termii for Nigerian numbers)
        if ($this->isNigerianNumber($to)) {
            $result = $this->sendViaTermii($to, $message);
            if ($result['success']) {
                return $result;
            }
            
            // Log primary provider failure
            Log::warning('Primary SMS provider (Termii) failed, trying fallback');
        }

        // Fallback to Twilio
        $result = $this->sendViaTwilio($to, $message);
        if ($result['success']) {
            return $result;
        }

        // If all providers fail, log the message
        Log::error('All SMS providers failed', [
            'to' => $to,
            'message' => $message
        ]);

        return [
            'success' => false,
            'provider' => 'none',
            'error' => 'All SMS providers failed'
        ];
    }

    /**
     * Send bulk SMS
     */
    public function sendBulkSMS(array $recipients, string $message): array
    {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($recipients as $phone) {
            $result = $this->sendSMS($phone, $message);
            
            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'phone' => $phone,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Check if phone number is Nigerian
     */
    private function isNigerianNumber(string $phone): bool
    {
        // Remove non-digits
        $phone = preg_replace('/\D/', '', $phone);
        
        // Check for Nigerian country code or local format
        return preg_match('/^(234|0)(70|80|81|90|91|70)[0-9]{8}$/', $phone) === 1;
    }

    /**
     * Format Nigerian phone number for international use
     */
    private function formatNigerianPhoneNumber(string $phone): string
    {
        // Remove non-digits
        $phone = preg_replace('/\D/', '', $phone);
        
        // If starts with 0, replace with 234
        if (substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        }
        
        // If doesn't start with 234, prepend it
        if (substr($phone, 0, 3) !== '234') {
            $phone = '234' . $phone;
        }
        
        return $phone;
    }

    /**
     * Get SMS provider status
     */
    public function getProviderStatus(): array
    {
        return [
            'twilio' => [
                'configured' => !empty(config('services.twilio.sid')),
                'name' => 'Twilio',
                'description' => 'International SMS provider'
            ],
            'termii' => [
                'configured' => !empty(config('services.termii.api_key')),
                'name' => 'Termii',
                'description' => 'Nigerian SMS provider'
            ]
        ];
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phone): array
    {
        $phone = preg_replace('/\D/', '', $phone);
        
        $validFormats = [
            'nigerian' => '/^(234|0)(70|80|81|90|91|70)[0-9]{8}$/',
            'international' => '/^\+?[1-9]\d{1,14}$/'
        ];

        foreach ($validFormats as $type => $pattern) {
            if (preg_match($pattern, $phone)) {
                return [
                    'valid' => true,
                    'type' => $type,
                    'formatted' => $this->formatNigerianPhoneNumber($phone)
                ];
            }
        }

        return [
            'valid' => false,
            'type' => 'unknown',
            'error' => 'Invalid phone number format'
        ];
    }
}
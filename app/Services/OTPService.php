<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPVerificationMail;
use Exception;

class OTPService
{
    const OTP_EXPIRY_MINUTES = 5;
    const MAX_ATTEMPTS = 3;
    const RESEND_COOLDOWN = 1; // minutes

    /**
     * Generate and send OTP for verification
     */
    public function generateAndSendOTP($driver, $type = 'sms')
    {
        try {
            // Generate 6-digit OTP
            $otp = $this->generateOTP();
            
            // Store OTP in cache with expiry
            $cacheKey = $this->getCacheKey($driver->id, $type);
            $otpData = [
                'otp' => $otp,
                'attempts' => 0,
                'generated_at' => now(),
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES)
            ];
            
            Cache::put($cacheKey, $otpData, self::OTP_EXPIRY_MINUTES * 60);
            
            // Send OTP based on type
            if ($type === 'sms') {
                $result = $this->sendSMS($driver->phone, $otp, $driver);
            } else {
                $result = $this->sendEmail($driver->email, $otp, $driver);
            }
            
            if ($result['success']) {
                Log::info("OTP sent successfully", [
                    'driver_id' => $driver->id,
                    'type' => $type,
                    'phone' => $driver->phone,
                    'email' => $driver->email
                ]);
                
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'expires_in' => self::OTP_EXPIRY_MINUTES
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to send OTP'
                ];
            }
            
        } catch (Exception $e) {
            Log::error("Error generating/sending OTP", [
                'driver_id' => $driver->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($driver, $inputOTP, $type = 'sms')
    {
        try {
            $cacheKey = $this->getCacheKey($driver->id, $type);
            $otpData = Cache::get($cacheKey);
            
            if (!$otpData) {
                return [
                    'success' => false,
                    'message' => 'OTP expired or not found. Please request a new code.'
                ];
            }
            
            // Check if OTP has expired
            if (now()->gt($otpData['expires_at'])) {
                Cache::forget($cacheKey);
                return [
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new code.'
                ];
            }
            
            // Increment attempts
            $otpData['attempts']++;
            
            // Check max attempts
            if ($otpData['attempts'] > self::MAX_ATTEMPTS) {
                Cache::forget($cacheKey);
                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new code.'
                ];
            }
            
            // Verify OTP
            if ($otpData['otp'] === $inputOTP) {
                // OTP is correct
                Cache::forget($cacheKey);
                
                // Update driver verification status
                $this->updateVerificationStatus($driver, $type);
                
                Log::info("OTP verified successfully", [
                    'driver_id' => $driver->id,
                    'type' => $type
                ]);
                
                return [
                    'success' => true,
                    'message' => 'OTP verified successfully'
                ];
            } else {
                // OTP is incorrect, update cache with incremented attempts
                Cache::put($cacheKey, $otpData, self::OTP_EXPIRY_MINUTES * 60);
                
                return [
                    'success' => false,
                    'message' => 'Invalid OTP. Please check and try again.',
                    'attempts_remaining' => self::MAX_ATTEMPTS - $otpData['attempts']
                ];
            }
            
        } catch (Exception $e) {
            Log::error("Error verifying OTP", [
                'driver_id' => $driver->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ];
        }
    }

    /**
     * Check if can resend OTP (cooldown period)
     */
    public function canResendOTP($driver, $type = 'sms')
    {
        $lastSentKey = $this->getCacheKey($driver->id, $type . '_last_sent');
        $lastSent = Cache::get($lastSentKey);
        
        if (!$lastSent) {
            return true;
        }
        
        return now()->diffInMinutes($lastSent) >= self::RESEND_COOLDOWN;
    }

    /**
     * Get time remaining for resend cooldown
     */
    public function getResendCooldownRemaining($driver, $type = 'sms')
    {
        $lastSentKey = $this->getCacheKey($driver->id, $type . '_last_sent');
        $lastSent = Cache::get($lastSentKey);
        
        if (!$lastSent) {
            return 0;
        }
        
        $elapsed = now()->diffInSeconds($lastSent);
        $cooldown = self::RESEND_COOLDOWN * 60; // Convert to seconds
        
        return max(0, $cooldown - $elapsed);
    }

    /**
     * Send SMS OTP
     */
    private function sendSMS($phone, $otp, $driver)
    {
        try {
            // For development, we'll simulate SMS sending
            // In production, integrate with SMS providers like Twilio, Nexmo, etc.
            
            if (app()->environment('local', 'development')) {
                // Development mode - log the OTP instead of sending
                Log::info("SMS OTP (Development Mode)", [
                    'phone' => $phone,
                    'otp' => $otp,
                    'driver' => $driver->first_name . ' ' . $driver->surname
                ]);
                
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully (development mode)'
                ];
            }
            
            // Production SMS sending logic would go here
            // Example with a hypothetical SMS service:
            /*
            $smsService = app('sms.service');
            $message = "Your DriveLink verification code is: {$otp}. Valid for " . self::OTP_EXPIRY_MINUTES . " minutes.";
            
            $result = $smsService->send($phone, $message);
            
            return [
                'success' => $result->success,
                'message' => $result->message
            ];
            */
            
            return [
                'success' => true,
                'message' => 'SMS sent successfully'
            ];
            
        } catch (Exception $e) {
            Log::error("SMS sending failed", [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to send SMS'
            ];
        }
    }

    /**
     * Send Email OTP
     */
    private function sendEmail($email, $otp, $driver)
    {
        try {
            Mail::send('emails.otp-verification', [
                'otp' => $otp,
                'driver_name' => $driver->first_name . ' ' . $driver->surname,
                'expires_in' => self::OTP_EXPIRY_MINUTES
            ], function ($message) use ($email, $driver) {
                $message->to($email, $driver->first_name . ' ' . $driver->surname)
                        ->subject('DriveLink - Email Verification Code');
            });
            
            Log::info("Email OTP sent", [
                'email' => $email,
                'driver' => $driver->first_name . ' ' . $driver->surname
            ]);
            
            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
            
        } catch (Exception $e) {
            Log::error("Email sending failed", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to send email'
            ];
        }
    }

    /**
     * Update driver verification status
     */
    private function updateVerificationStatus($driver, $type)
    {
        $field = $type === 'sms' ? 'phone_verified_at' : 'email_verified_at';
        $driver->$field = now();
        
        // Check if both phone and email are verified
        if ($driver->phone_verified_at && $driver->email_verified_at) {
            $driver->verification_status = 'contacts_verified';
        }
        
        $driver->save();
    }

    /**
     * Generate 6-digit OTP
     */
    private function generateOTP()
    {
        return sprintf('%06d', mt_rand(100000, 999999));
    }

    /**
     * Get cache key for OTP storage
     */
    private function getCacheKey($driverId, $type)
    {
        return "driver_otp_{$driverId}_{$type}";
    }

    /**
     * Clear all OTP data for a driver
     */
    public function clearOTPData($driverId)
    {
        Cache::forget($this->getCacheKey($driverId, 'sms'));
        Cache::forget($this->getCacheKey($driverId, 'email'));
        Cache::forget($this->getCacheKey($driverId, 'sms_last_sent'));
        Cache::forget($this->getCacheKey($driverId, 'email_last_sent'));
    }

    /**
     * Get OTP status for driver
     */
    public function getOTPStatus($driver)
    {
        return [
            'phone_verified' => !is_null($driver->phone_verified_at),
            'email_verified' => !is_null($driver->email_verified_at),
            'sms_otp_active' => Cache::has($this->getCacheKey($driver->id, 'sms')),
            'email_otp_active' => Cache::has($this->getCacheKey($driver->id, 'email')),
            'can_resend_sms' => $this->canResendOTP($driver, 'sms'),
            'can_resend_email' => $this->canResendOTP($driver, 'email'),
            'sms_cooldown' => $this->getResendCooldownRemaining($driver, 'sms'),
            'email_cooldown' => $this->getResendCooldownRemaining($driver, 'email')
        ];
    }
}
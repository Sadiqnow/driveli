<?php

namespace App\Services;

use App\Models\OtpNotification;
use App\Models\DriverNormalized;
use App\Models\Company;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class OtpNotificationService
{
    protected $twilio;

    public function __construct()
    {
        if (config('services.twilio.sid') && config('services.twilio.token')) {
            $this->twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
        }
    }

    /**
     * Generate and send OTP
     */
    public function generateAndSendOTP($userType, $userId, $type = 'verification', $expiresInMinutes = 10)
    {
        $otp = $this->generateOTP();

        $otpRecord = OtpNotification::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'otp_code' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes($expiresInMinutes),
        ]);

        $this->sendOTP($otpRecord);

        return $otpRecord;
    }

    /**
     * Send OTP via SMS and/or Email
     */
    public function sendOTP(OtpNotification $otp)
    {
        $recipient = $this->getRecipientContact($otp->user_type, $otp->user_id);

        if (!$recipient) {
            Log::warning("No contact information found for {$otp->user_type} ID: {$otp->user_id}");
            return false;
        }

        $success = false;

        // Send SMS if enabled and phone available
        if (config('services.twilio.sms_enabled', false) && $recipient['phone']) {
            $success = $this->sendSMS($recipient['phone'], $otp->otp_code, $otp->type) || $success;
        }

        // Send Email if email available
        if ($recipient['email']) {
            $success = $this->sendEmail($recipient['email'], $otp->otp_code, $otp->type) || $success;
        }

        if ($success) {
            $otp->markAsSent();
        } else {
            $otp->markAsFailed();
        }

        return $success;
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($userType, $userId, $otpCode, $type = null)
    {
        $query = OtpNotification::where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('otp_code', $otpCode)
            ->where('status', 'sent')
            ->where('expires_at', '>', now());

        if ($type) {
            $query->where('type', $type);
        }

        $otp = $query->first();

        if ($otp && $otp->verify($otpCode)) {
            return $otp;
        }

        return false;
    }

    /**
     * Resend OTP
     */
    public function resendOTP(OtpNotification $otp)
    {
        if ($otp->attempts >= 3) {
            return false;
        }

        $otp->update([
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'status' => 'pending',
        ]);

        return $this->sendOTP($otp);
    }

    /**
     * Generate random OTP
     */
    private function generateOTP($length = 6)
    {
        return str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Get recipient contact information
     */
    private function getRecipientContact($userType, $userId)
    {
        switch ($userType) {
            case 'driver':
                $user = DriverNormalized::find($userId);
                return $user ? [
                    'phone' => $user->phone1,
                    'email' => $user->email,
                    'name' => $user->full_name,
                ] : null;

            case 'company':
                $user = Company::find($userId);
                return $user ? [
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'name' => $user->name,
                ] : null;

            default:
                return null;
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendSMS($phone, $otp, $type)
    {
        if (!$this->twilio) {
            Log::warning('Twilio not configured for SMS sending');
            return false;
        }

        try {
            $message = $this->getSMSMessage($otp, $type);

            $this->twilio->messages->create(
                $phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Email
     */
    private function sendEmail($email, $otp, $type)
    {
        try {
            $subject = $this->getEmailSubject($type);
            $message = $this->getEmailMessage($otp, $type);

            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get SMS message template
     */
    private function getSMSMessage($otp, $type)
    {
        $messages = [
            'verification' => "Your verification code is: {$otp}. Valid for 10 minutes.",
            'security_challenge' => "Security alert: Your OTP is {$otp}. Contact support if this wasn't you.",
            'deactivation' => "Deactivation OTP: {$otp}. This action requires verification.",
        ];

        return $messages[$type] ?? "Your OTP is: {$otp}";
    }

    /**
     * Get Email subject
     */
    private function getEmailSubject($type)
    {
        $subjects = [
            'verification' => 'Your Verification Code',
            'security_challenge' => 'Security Alert - OTP Required',
            'deactivation' => 'Account Deactivation Verification',
        ];

        return $subjects[$type] ?? 'Your OTP Code';
    }

    /**
     * Get Email message template
     */
    private function getEmailMessage($otp, $type)
    {
        $messages = [
            'verification' => "Your verification code is: {$otp}\n\nThis code will expire in 10 minutes.\n\nIf you didn't request this code, please ignore this email.",
            'security_challenge' => "Security Alert: We detected suspicious activity on your account.\n\nYour OTP is: {$otp}\n\nIf this wasn't you, please contact support immediately.",
            'deactivation' => "Account Deactivation Request\n\nYour verification code is: {$otp}\n\nThis code will expire in 10 minutes.",
        ];

        return $messages[$type] ?? "Your OTP is: {$otp}";
    }
}

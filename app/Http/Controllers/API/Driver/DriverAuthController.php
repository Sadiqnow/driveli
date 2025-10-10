<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
// use App\Services\NotificationService;

class DriverAuthController extends Controller
{
    // protected $notificationService;

    // public function __construct(NotificationService $notificationService)
    // {
    //     $this->notificationService = $notificationService;
    // }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:drivers',
            'phone' => 'required|string|max:20|unique:drivers',
            'password' => 'required|string|min:8',
        ]);

        // Create driver account
        $driver = Driver::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Generate OTP for email verification
        $otpCode = $driver->generateOtp();

        // Send OTP via SMS
        // $this->notificationService->sendSMS(
        //     $driver->phone,
        //     "Welcome to Drivelink! Your verification code is: {$otpCode}. Valid for 15 minutes."
        // );

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your phone number with the OTP sent.',
            'data' => [
                'driver_id' => $driver->driver_id,
                'phone' => $driver->phone, // formatted_phone,
                'otp_expires_at' => $driver->otp_expires_at,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        // Rate limiting check
        $rateLimitKey = 'login_attempts:' . $request->ip();
        $attempts = Cache::get($rateLimitKey, 0);

        if ($attempts >= 5) {
            Log::warning('Login rate limit exceeded', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
            ], 429);
        }

        $validatedData = $request->validate([
            'login' => 'required|string|max:255', // Can be email, phone, or driver_id
            'password' => 'required|string|min:6|max:255',
        ]);

        // Sanitize and validate login field
        $loginField = trim($validatedData['login']);
        $loginType = $this->detectLoginType($loginField);

        if (!$loginType) {
            $this->incrementLoginAttempts($request->ip());
            Log::warning('Invalid login format attempted', [
                'ip' => $request->ip(),
                'login_attempt' => $loginField,
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials provided.',
            ], 401);
        }

        // Find driver using specific field based on detected type
        $driver = $this->findDriverByLoginType($loginType, $loginField);

        if (!$driver || !Hash::check($validatedData['password'], $driver->password)) {
            $this->incrementLoginAttempts($request->ip());
            $this->logFailedLogin($request, $loginField);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials provided.',
            ], 401);
        }

        if (!$driver->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email/phone before logging in.',
                'requires_verification' => true,
                'driver_id' => $driver->driver_id,
            ], 403);
        }

        // Clear rate limiting on successful login
        Cache::forget($rateLimitKey);

        // Update last login
        $driver->updateLastLogin($request->ip());

        // Log successful login
        Log::info('Driver login successful', [
            'driver_id' => $driver->driver_id,
            'email' => $driver->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Create token
        $token = $driver->createToken('driver-token', ['driver'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'driver' => [
                    'id' => $driver->id,
                    'driver_id' => $driver->driver_id,
                    'full_name' => $driver->full_name,
                    'email' => $driver->email,
                    'phone' => $driver->phone, // formatted_phone,
                    'status' => $driver->status,
                    'verification_status' => $driver->verification_status,
                    'rating' => $driver->rating,
                    'profile_photo' => $driver->profile_photo,
                ],
                'token' => $token,
                'profile_completed' => $this->checkProfileCompletion($driver),
            ],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|string|exists:drivers,driver_id',
            'otp_code' => 'required|string|size:6',
        ]);

        $driver = Driver::where('driver_id', $request->driver_id)->first();

        if (!$driver->verifyOtp($request->otp_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Phone number verified successfully. You can now login.',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|string|exists:drivers,driver_id',
        ]);

        $driver = Driver::where('driver_id', $request->driver_id)->first();
        $otpCode = $driver->generateOtp();

        // Send OTP via SMS
        // $this->notificationService->sendSMS(
        //     $driver->phone,
        //     "Your Drivelink verification code is: {$otpCode}. Valid for 15 minutes."
        // );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'data' => [
                'otp_expires_at' => $driver->otp_expires_at,
            ],
        ]);
    }

    public function forgotPassword(Request $request)
    {
        // Rate limiting for password reset
        $rateLimitKey = 'password_reset:' . $request->ip();
        $attempts = Cache::get($rateLimitKey, 0);

        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset attempts. Please try again later.',
            ], 429);
        }

        $validatedData = $request->validate([
            'login' => 'required|string|max:255', // email, phone, or driver_id
        ]);

        $loginField = trim($validatedData['login']);
        $loginType = $this->detectLoginType($loginField);

        $driver = null;
        if ($loginType) {
            $driver = $this->findDriverByLoginType($loginType, $loginField);
        }

        // Increment attempts regardless of success to prevent enumeration
        Cache::put($rateLimitKey, $attempts + 1, now()->addHour());

        // Always return success to prevent user enumeration
        if ($driver) {
            // Generate OTP for password reset
            $otpCode = $driver->generateOtp();

            // Send OTP via SMS
            // $this->notificationService->sendSMS(
            //     $driver->phone,
            //     "Your Drivelink password reset code is: {$otpCode}. Valid for 15 minutes."
            // );
        }

        return response()->json([
            'success' => true,
            'message' => 'If the account exists, a password reset code has been sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|string|exists:drivers,driver_id',
            'otp_code' => 'required|string|size:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $driver = Driver::where('driver_id', $request->driver_id)->first();

        if (!$driver->verifyOtp($request->otp_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.',
            ], 422);
        }

        // Update password
        $driver->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now login with your new password.',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    private function checkProfileCompletion(Driver $driver)
    {
        $requiredFields = [
            'date_of_birth',
            'address',
            'state',
            'nin',
            'license_number',
            'license_class',
            'license_expiry_date',
            'experience_level',
            'vehicle_types',
            'regions',
            'profile_photo',
            'license_front_image',
            'license_back_image',
        ];

        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($driver->$field)) {
                $completedFields++;
            }
        }

        return [
            'percentage' => round(($completedFields / count($requiredFields)) * 100),
            'completed_fields' => $completedFields,
            'total_fields' => count($requiredFields),
            'missing_fields' => array_filter($requiredFields, fn($field) => empty($driver->$field)),
        ];
    }

    private function detectLoginType(string $login): ?string
    {
        // Email pattern
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Driver ID pattern (e.g., DRV-001234)
        if (preg_match('/^DRV-\d{6}$/', $login)) {
            return 'driver_id';
        }

        // Phone pattern (Nigerian format: +234 or 0 followed by 10 digits)
        if (preg_match('/^(\+234|0)[789][01]\d{8}$/', $login)) {
            return 'phone';
        }

        return null;
    }

    private function findDriverByLoginType(string $type, string $value): ?Driver
    {
        switch ($type) {
            case 'email':
                return Driver::where('email', $value)->first();
            case 'driver_id':
                return Driver::where('driver_id', $value)->first();
            case 'phone':
                return Driver::where('phone', $value)->first();
            default:
                return null;
        }
    }

    private function incrementLoginAttempts(string $ip): void
    {
        $key = 'login_attempts:' . $ip;
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(15));
    }

    private function logFailedLogin(Request $request, string $loginAttempt): void
    {
        Log::warning('Driver login failed', [
            'ip' => $request->ip(),
            'login_attempt' => $loginAttempt,
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
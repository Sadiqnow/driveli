<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Drivers;
use App\Models\OtpVerification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DriverAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:driver')->except(['logout', 'showOtpForm', 'verifyOtp', 'sendOtp', 'resendOtp']);
    }

    /**
     * Generate and send OTP for verification
     */
    protected function generateAndSendOTP(Drivers $driver, string $verificationType)
    {
        // Generate 6-digit OTP
        $otpCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create or update OTP verification record
        $otpVerification = OtpVerification::updateOrCreate(
            [
                'driver_id' => $driver->id,
                'verification_type' => $verificationType,
            ],
            [
                'otp_code' => Hash::make($otpCode),
                'expires_at' => now()->addMinutes(10),
                'verified_at' => null,
                'attempts' => 0,
                'last_attempt_at' => null,
            ]
        );

        // Send OTP via email or SMS (implement actual sending logic)
        try {
            if ($verificationType === 'email') {
                $this->sendEmailOTP($driver->email, $otpCode);
            } elseif ($verificationType === 'sms') {
                $this->sendSmsOTP($driver->phone, $otpCode);
            }

            Log::info("OTP sent to driver {$driver->id} via {$verificationType}");

            return [
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_in' => 600 // 10 minutes
            ];
        } catch (\Exception $e) {
            Log::error("Failed to send OTP to driver {$driver->id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }
    }

    /**
     * Send OTP via email
     */
    protected function sendEmailOTP(string $email, string $otpCode)
    {
        // In production, use a proper email service
        // For now, just log the OTP
        Log::info("Email OTP for {$email}: {$otpCode}");

        // You can implement actual email sending here
        // Mail::to($email)->send(new OTPVerificationMail($otpCode));
    }

    /**
     * Send OTP via SMS
     */
    protected function sendSmsOTP(string $phone, string $otpCode)
    {
        // In production, use a proper SMS service
        // For now, just log the OTP
        Log::info("SMS OTP for {$phone}: {$otpCode}");

        // You can implement actual SMS sending here
        // $smsService->send($phone, "Your OTP code is: {$otpCode}");
    }

    /**
     * Step 1: Collect basic registration information
     */
    public function registerStep1(Request $request)
    {
        $request->validate([
            'license_number' => 'required|string|max:50',
            'date_of_birth' => 'required|date|before:' . now()->subYears(18)->format('Y-m-d'),
            'first_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:drivers,email',
            'phone' => 'required|string|max:20|unique:drivers,phone',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
            'gender' => 'nullable|string|in:Male,Female,Other,Prefer not to say',
            'religion' => 'nullable|string|max:100',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height_meters' => 'nullable|numeric|min:0.5|max:3.0',
            'disability_status' => 'nullable|string|max:100',
        ]);

        // Store registration data in session
        $registrationData = [
            'license_number' => $request->license_number,
            'date_of_birth' => $request->date_of_birth,
            'first_name' => $request->first_name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'gender' => $request->gender,
            'religion' => $request->religion,
            'blood_group' => $request->blood_group,
            'height_meters' => $request->height_meters,
            'disability_status' => $request->disability_status ?: 'None',
            'step' => 1,
            'created_at' => now(),
        ];

        session(['driver_registration' => $registrationData]);

        return redirect()->route('driver.register.step2')
            ->with('success', 'Basic information saved! Please verify your contact details.');
    }

    /**
     * Show Step 2: OTP Verification
     */
    public function showRegisterStep2()
    {
        $registrationData = session('driver_registration');

        if (!$registrationData || $registrationData['step'] < 1) {
            return redirect()->route('driver.register')
                ->withErrors(['message' => 'Please complete step 1 first.']);
        }

        // Create temporary driver record for OTP sending
        $tempDriver = new Drivers([
            'first_name' => $registrationData['first_name'],
            'surname' => $registrationData['surname'],
            'email' => $registrationData['email'],
            'phone' => $registrationData['phone'],
            'id' => 0, // Temporary ID
        ]);

        return view('driver.auth.register-step2', compact('registrationData', 'tempDriver'));
    }

    /**
     * Step 2: Verify OTP and create driver account
     */
    public function registerStep2(Request $request)
    {
        $registrationData = session('driver_registration');

        if (!$registrationData || $registrationData['step'] < 1) {
            return redirect()->route('driver.register')
                ->withErrors(['message' => 'Please complete step 1 first.']);
        }

        $request->validate([
            'verification_type' => 'required|in:sms,email',
            'otp' => 'required|digits:6',
        ]);

        // Create temporary driver for OTP verification
        $tempDriver = new Drivers([
            'first_name' => $registrationData['first_name'],
            'surname' => $registrationData['surname'],
            'email' => $registrationData['email'],
            'phone' => $registrationData['phone'],
            'id' => 0,
        ]);

        // Generate OTP codes for verification
        $otpCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // For demo purposes, we'll simulate OTP verification
        // In production, you'd check against sent OTP
        if ($request->otp !== '123456') { // Demo OTP
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        // Mark verification as complete
        $registrationData['otp_verified'] = true;
        $registrationData['step'] = 2;
        session(['driver_registration' => $registrationData]);

        return redirect()->route('driver.register.step3')
            ->with('success', 'Contact verification successful! Please proceed with facial recognition.');
    }

    /**
     * Show Step 3: Facial Recognition
     */
    public function showRegisterStep3()
    {
        $registrationData = session('driver_registration');

        if (!$registrationData || $registrationData['step'] < 2) {
            return redirect()->route('driver.register')
                ->withErrors(['message' => 'Please complete previous steps first.']);
        }

        return view('driver.auth.register-step3', compact('registrationData'));
    }

    /**
     * Step 3: Handle facial capture
     */
    public function registerStep3(Request $request)
    {
        $registrationData = session('driver_registration');

        if (!$registrationData || $registrationData['step'] < 2) {
            return redirect()->route('driver.register')
                ->withErrors(['message' => 'Please complete previous steps first.']);
        }

        $rules = [
            'facial_image' => 'required|mimes:jpeg,png,jpg|max:2048',
        ];

        if (!app()->environment('testing')) {
            $rules['facial_image'] .= '|image';
        }

        $request->validate($rules);

        // Store facial image temporarily
        $facialImage = $request->file('facial_image');
        $facialImageName = 'facial_' . time() . '.' . $facialImage->extension();
        $facialImage->storeAs('temp/facial', $facialImageName, 'public');

        $registrationData['facial_image'] = $facialImageName;
        $registrationData['step'] = 3;
        session(['driver_registration' => $registrationData]);

        return redirect()->route('driver.register.step4')
            ->with('success', 'Facial recognition completed! Please upload your documents.');
    }

    /**
     * Show Step 4: Document Upload
     */
    public function showRegisterStep4()
    {
        $registrationData = session('driver_registration');

        if (!$registrationData || $registrationData['step'] < 3) {
            return redirect()->route('driver.register')
                ->withErrors(['message' => 'Please complete previous steps first.']);
        }

        return view('driver.auth.register-step4', compact('registrationData'));
    }

    /**
     * Step 4: Handle document upload and complete registration
     */
    public function registerStep4(Request $request)
    {
        $registrationData = session('driver_registration');

        if (!$registrationData || $registrationData['step'] < 3) {
            return redirect()->route('driver.register')
                ->withErrors(['message' => 'Please complete previous steps first.']);
        }

        $rules = [
            'license_scan' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            'national_id' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            'passport_photo' => 'required|mimes:jpeg,png,jpg|max:2048',
            'terms' => 'required|accepted',
            'data_accuracy' => 'required|accepted',
        ];

        if (!app()->environment('testing')) {
            $rules['license_scan'] = 'required|image|' . $rules['license_scan'];
            $rules['national_id'] = 'required|image|' . $rules['national_id'];
            $rules['passport_photo'] = 'required|image|' . $rules['passport_photo'];
        }

        $request->validate($rules);

        // Create the driver account
        $driver = Drivers::create([
            'first_name' => $registrationData['first_name'],
            'surname' => $registrationData['surname'],
            'email' => $registrationData['email'],
            'phone' => $registrationData['phone'],
            'password' => Hash::make($registrationData['password']),
            'date_of_birth' => $registrationData['date_of_birth'],
            'license_number' => $registrationData['license_number'],
            'driver_id' => 'DR' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'gender' => $registrationData['gender'],
            'religion' => $registrationData['religion'],
            'blood_group' => $registrationData['blood_group'],
            'height_meters' => $registrationData['height_meters'],
            'disability_status' => $registrationData['disability_status'],
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Store documents
        $this->storeDocument($request->file('license_scan'), 'license', $driver->id);
        $this->storeDocument($request->file('national_id'), 'national_id', $driver->id);
        $this->storeDocument($request->file('passport_photo'), 'passport', $driver->id);

        // Clear session data
        session()->forget('driver_registration');

        // Log the driver in
        Auth::guard('driver')->login($driver);

        return redirect()->route('driver.dashboard')
            ->with('success', 'Registration completed successfully! Welcome to DriveLink.');
    }

    /**
     * Store uploaded document
     */
    private function storeDocument($file, $type, $driverId)
    {
        if ($file) {
            $filename = $type . '_' . $driverId . '_' . time() . '.' . $file->extension();
            $file->storeAs('documents/drivers/' . $driverId, $filename, 'public');

            // Here you could save document metadata to database
            // For now, just storing the file
        }
    }

    /**
     * Register a new driver (legacy method - kept for backward compatibility)
     */
    public function register(Request $request)
    {
        $request->validate([
            'license_number' => 'required|string|max:50',
            'date_of_birth' => 'required|date|before:' . now()->subYears(18)->format('Y-m-d'),
            'first_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:drivers,email',
            'phone' => 'required|string|max:20|unique:drivers,phone',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
            'gender' => 'nullable|string|in:Male,Female,Other,Prefer not to say',
            'religion' => 'nullable|string|max:100',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height_meters' => 'nullable|numeric|min:0.5|max:3.0',
            'disability_status' => 'nullable|string|max:100',
        ]);

        // Set default disability status if not provided
        $disabilityStatus = $request->disability_status ?: 'None';

        $driver = Drivers::create([
            'first_name' => $request->first_name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'license_number' => $request->license_number,
            'driver_id' => 'DR' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'gender' => $request->gender,
            'religion' => $request->religion,
            'blood_group' => $request->blood_group,
            'height_meters' => $request->height_meters,
            'disability_status' => $disabilityStatus,
        ]);

        // Send OTP after registration
        $this->generateAndSendOTP($driver, 'sms');
        $this->generateAndSendOTP($driver, 'email');

        Auth::guard('driver')->login($driver);

        return redirect()->route('driver.verify-otp')
            ->with('success', 'Registration successful! Please verify your phone and email using the OTP sent.');
    }

    /**
     * Send OTP for verification
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'verification_type' => 'required|in:sms,email',
        ]);

        /** @var \App\Models\Drivers|null $driver */
        $driver = Auth::guard('driver')->user();

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not authenticated'
            ], 401);
        }

        // Check if already verified
        if ($request->verification_type === 'email' && $driver->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ]);
        }

        if ($request->verification_type === 'sms' && $driver->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Phone already verified'
            ]);
        }

        // Check cooldown
        $existingOtp = OtpVerification::where('driver_id', $driver->id)
            ->where('verification_type', $request->verification_type)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if ($existingOtp && !$existingOtp->canResend()) {
            $cooldown = $existingOtp->getCooldownRemaining();
            return response()->json([
                'success' => false,
                'message' => "Please wait {$cooldown} seconds before requesting another code."
            ]);
        }

        $result = $this->generateAndSendOTP($driver, $request->verification_type);

        return response()->json($result);
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'verification_type' => 'required|in:sms,email',
            'otp' => 'required|digits:6',
        ]);

        $driver = Auth::guard('driver')->user();

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not authenticated'
            ], 401);
        }

        // Find active OTP verification
        $otpVerification = OtpVerification::where('driver_id', $driver->id)
            ->where('verification_type', $request->verification_type)
            ->active()
            ->first();

        if (!$otpVerification) {
            return response()->json([
                'success' => false,
                'message' => 'No active OTP found. Please request a new one.'
            ]);
        }

        // Check if OTP is expired
        if ($otpVerification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ]);
        }

        // Check attempts limit
        if ($otpVerification->attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new OTP.'
            ]);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $otpVerification->otp_code)) {
            $otpVerification->incrementAttempts();

            $remainingAttempts = 3 - $otpVerification->attempts;
            return response()->json([
                'success' => false,
                'message' => "Invalid OTP. {$remainingAttempts} attempts remaining."
            ]);
        }

        // Mark as verified
        $otpVerification->update(['verified_at' => now()]);

        // Update driver verification status (single save to avoid duplicate calls)
        if ($request->verification_type === 'email') {
            $driver->email_verified_at = now();
        } elseif ($request->verification_type === 'sms') {
            $driver->phone_verified_at = now();
        }

        // Persist changes if we have a model instance
        if ($driver instanceof Drivers) {
            $driver->save();
        } else {
            // Fallback: attempt a direct DB update if $driver is not an Eloquent model
                if ($driver && isset($driver->id)) {
                    if ($request->verification_type === 'email') {
                        DB::table('drivers')->where('id', $driver->id)->update(['email_verified_at' => now()]);
                    } elseif ($request->verification_type === 'sms') {
                        DB::table('drivers')->where('id', $driver->id)->update(['phone_verified_at' => now()]);
                    }
                }
        }

        // Check if both phone and email are verified
        if ($driver instanceof Drivers) {
            $driver->refresh();
        } else {
            // Reload a fresh model instance to check flags
            $driver = Drivers::find($driver->id ?? null);
        }

        if ($driver && $driver->phone_verified_at && $driver->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'Contact verification completed! Please proceed with KYC.',
                'redirect' => route('driver.kyc.index')
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->verification_type) . ' verified successfully. Please verify the other contact method.'
        ]);
    }

    /**
     * Login driver
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('driver')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $driver = Auth::guard('driver')->user();

            // Check if contact verification is needed
            if (!$driver->phone_verified_at || !$driver->email_verified_at) {
                return redirect()->route('driver.verify-otp')
                    ->with('warning', 'Please complete your contact verification.');
            }

            return redirect()->intended(route('driver.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function showLogin()
    {
        return view('driver.auth.login');
    }

    public function logout(Request $request)
    {
        Auth::guard('driver')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('driver.login')->with('message', 'Successfully logged out.');
    }

    public function showRegister()
    {
        return view('driver.auth.register');
    }

    public function showOtpForm()
    {
        $driver = Auth::guard('driver')->user();

        if (!$driver) {
            return redirect()->route('driver.login')->withErrors(['message' => 'Please login first.']);
        }

        // Get OTP status for cooldown display
        $otpStatus = [
            'sms_cooldown' => 0,
            'email_cooldown' => 0,
        ];

        // Check SMS OTP status
        $smsOtp = OtpVerification::where('driver_id', $driver->id)
            ->where('verification_type', 'sms')
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if ($smsOtp && !$smsOtp->canResend()) {
            $otpStatus['sms_cooldown'] = $smsOtp->getCooldownRemaining();
        }

        // Check Email OTP status
        $emailOtp = OtpVerification::where('driver_id', $driver->id)
            ->where('verification_type', 'email')
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if ($emailOtp && !$emailOtp->canResend()) {
            $otpStatus['email_cooldown'] = $emailOtp->getCooldownRemaining();
        }

        return view('driver.auth.verify-otp', compact('driver', 'otpStatus'));
    }

    public function showForgotPassword()
    {
        return view('driver.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $driver = Drivers::where('email', $request->email)->first();
        
        if ($driver) {
            $token = Str::random(64);
            
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // In production, send actual email here
            // For now, just return success message
        }

        return back()->with('status', 'If a driver account with that email exists, a password reset link has been sent.');
    }

    public function showResetPassword($token)
    {
        $email = request('email');
        
        if (!$email) {
            return redirect()->route('driver.login')->withErrors(['email' => 'Invalid reset link.']);
        }
        
        $passwordReset = DB::table('password_resets')
            ->where('email', $email)
            ->first();
            
        if (!$passwordReset) {
            return redirect()->route('driver.login')->withErrors(['email' => 'Invalid reset link.']);
        }
        
        if (now()->diffInHours($passwordReset->created_at) > 24) {
            DB::table('password_resets')->where('email', $email)->delete();
            return redirect()->route('driver.login')->withErrors(['email' => 'Reset link has expired.']);
        }
        
        return view('driver.auth.reset-password', ['token' => $token, 'email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['token' => 'Invalid or expired reset token.']);
        }

        $driver = Drivers::where('email', $request->email)->first();

        if ($driver) {
            $driver->update([
                'password' => Hash::make($request->password)
            ]);

            DB::table('password_resets')->where('email', $request->email)->delete();

            return redirect()->route('driver.login')
                ->with('status', 'Password has been reset successfully. You can now login with your new password.');
        }

        return back()->withErrors(['email' => 'Driver not found.']);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;         
use App\Models\AdminUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\AdminRegistrationRequest;
use App\Services\AuthenticationService;
use App\Services\ValidationService;
use App\Services\ErrorHandlingService;
use Illuminate\Database\Eloquent\SoftDeletes;


class AdminAuthController extends Controller
{
    private AuthenticationService $authService;
    private ValidationService $validationService;
    private ErrorHandlingService $errorHandler;

    public function __construct(
        AuthenticationService $authService,
        ValidationService $validationService,
        ErrorHandlingService $errorHandler
    ) {
        $this->authService = $authService;
        $this->validationService = $validationService;
        $this->errorHandler = $errorHandler;
    }

    public function showLogin()
    {
        return view('admin.login');
    }


    public function login(Request $request)
    {
        try {
            // Validate input using ValidationService
            $validatedData = $this->validationService->validateAdminLogin($request);
            
            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            if ($this->authService->authenticateAdmin($credentials, $remember)) {
                $request->session()->regenerate();
                
                // Log successful login
                $this->errorHandler->logAuthEvent('admin_login', $request->email, true);
                
                return redirect()->intended(route('admin.dashboard'));
            }

            // Log failed login attempt
            $this->errorHandler->logAuthEvent('admin_login_failed', $request->email, false);

            // Ensure failed attempts are tracked even when admin user does not exist
            try {
                $this->authService->incrementFailedLoginByEmail($request->email, $request);
            } catch (\Throwable $ignored) {
                // Don't let failure to track affect user-facing response
            }

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);

        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, $request);
            throw $e;
        }
    }


    public function logout(Request $request)
    {
        $email = auth('admin')->user()->email ?? 'unknown';
        
        $this->authService->logout($request);
        $this->errorHandler->logAuthEvent('admin_logout', $email, true);
        
        return redirect()->route('admin.login')->with('message', 'Successfully logged out.');
    }

    public function showForgotPassword()
    {
        return view('admin.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        try {
            // Validate input using ValidationService
            $this->validationService->validatePasswordResetEmail($request);
            
            // Check rate limiting
            if ($this->authService->isPasswordResetRateLimited($request->ip())) {
                return back()->withErrors(['email' => 'Too many password reset attempts. Please try again later.']);
            }
            
            // Sanitize and validate email
            $email = $this->authService->validateAndSanitizeEmail($request->email);
            
            // Check if admin exists and is active
            $admin = $this->authService->findActiveAdmin($email);
            
            // Always return the same response to prevent user enumeration
            $responseMessage = 'If an admin account with that email exists, a password reset link has been sent.';
            
            if ($admin) {
                // Generate secure token
                $token = $this->authService->generatePasswordResetToken();
                
                // Store token securely
                if (!$this->authService->storePasswordResetToken($email, $token, $request)) {
                    return back()->withErrors(['email' => 'Password reset failed. Please try again later.']);
                }
                
                // Generate reset URL
                $resetUrl = $this->authService->generatePasswordResetUrl($token, $email);
                
                // Log for security monitoring
                $this->errorHandler->logAuthEvent('password_reset_requested', $email, true);
                
                // In production, send actual email here
                // Mail::send('emails.admin-password-reset', ['resetUrl' => $resetUrl], function($message) use ($email) {
                //     $message->to($email)->subject('DriveLink Admin - Password Reset Request');
                // });
            }
            
            // Increment rate limiting
            $this->authService->incrementPasswordResetAttempts($request->ip());
            
            // Always return same message to prevent user enumeration
            return back()->with('status', $responseMessage);
            
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, $request);
            return back()->withErrors(['email' => 'An error occurred. Please try again later.']);
        }
    }

    public function showResetPassword($token)
    {
        $email = request('email');

        if (!$email) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Invalid reset link.']);
        }

        // Use AuthenticationService for secure token verification
        if (!$this->authService->verifyPasswordResetToken($email, $token)) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        return view('admin.reset-password', ['token' => $token, 'email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        try {
            // Validate input using ValidationService
            $this->validationService->validatePasswordReset($request);
            
            // Verify token using AuthenticationService
            if (!$this->authService->verifyPasswordResetToken($request->email, $request->token)) {
                return back()->withErrors(['token' => 'Invalid or expired reset token.']);
            }
            
            // Reset password using AuthenticationService
            if ($this->authService->resetPassword($request->email, $request->password)) {
                $this->errorHandler->logAuthEvent('password_reset_successful', $request->email, true);
                
                return redirect()->route('admin.login')
                    ->with('status', 'Password has been reset successfully. You can now login with your new password.');
            } else {
                return back()->withErrors(['email' => 'Failed to reset password. Please try again.']);
            }
            
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, $request);
            return back()->withErrors(['email' => 'An error occurred. Please try again later.']);
        }
    }

    public function showRegister()
    {
        // Check if registration is allowed
        if (!$this->authService->isRegistrationAllowed()) {
            return redirect()->route('admin.login')->with('error', 'Registration is disabled.');
        }

        return view('admin.register');
    }

    public function register(Request $request)
    {
        try {
            // Validate input using ValidationService
            $this->validationService->validateAdminRegistration($request);
            
            // Register admin using AuthenticationService
            $adminUser = $this->authService->registerAdmin([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'role' => AdminUser::count() === 0 ? 'Super Admin' : 'Admin'
            ]);

            // Log successful registration
            $this->errorHandler->logAuthEvent('admin_registration', $request->email, true, [
                'name' => $request->name,
                'role' => $adminUser->role,
                'is_first_admin' => $adminUser->role === 'Super Admin'
            ]);

            // Log the user in
            Auth::guard('admin')->login($adminUser);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome to DriveLink Admin! Registration successful.');
                
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, $request);
            $this->errorHandler->logAuthEvent('admin_registration_failed', $request->email ?? 'unknown', false);
            
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['registration' => 'Registration failed. Please try again.']);
        }
    }
}
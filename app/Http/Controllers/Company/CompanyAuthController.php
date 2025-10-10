<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompanyAuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest:company')->except(['logout', 'dashboard']);
        $this->middleware('auth:company')->only(['logout', 'dashboard']);
    }

    /**
     * Show the company login form.
     */
    public function showLoginForm()
    {
        return view('company.auth.login');
    }

    /**
     * Handle company login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('company')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $company = Auth::guard('company')->user();
            
            // Log the successful login
            \Log::info('Company login successful', [
                'company_id' => $company->company_id,
                'company_name' => $company->name,
                'email' => $company->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->intended(route('company.dashboard'));
        }

        // Log the failed login attempt
        \Log::warning('Company login failed', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Show the company registration form.
     */
    public function showRegistrationForm()
    {
        return view('company.auth.register');
    }

    /**
     * Handle company registration with login capability
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'business_type' => 'required|string|in:logistics,delivery,ride_hailing,freight,other',
            'registration_number' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'company_address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'contact_name' => 'required|string|max:255',
            'contact_title' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:companies,email',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'password' => 'required|string|min:8|confirmed',
            'drivers_needed' => 'nullable|integer|min:1|max:1000',
            'urgency' => 'nullable|string|in:immediate,urgent,normal,planning',
            'driver_requirements' => 'nullable|string|max:1000',
            'terms' => 'required|accepted',
            'marketing_emails' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        try {
            // Generate unique company ID
            $companyId = $this->generateCompanyId();

            // Map business type to industry (required field in database)
            $industryMapping = [
                'logistics' => 'Logistics',
                'delivery' => 'Logistics', 
                'ride_hailing' => 'Logistics',
                'freight' => 'Logistics',
                'other' => 'Other'
            ];

            // Combine address with city if provided
            $fullAddress = $request->company_address;
            if (!empty($request->city)) {
                $fullAddress .= ', ' . $request->city;
            }

            // Create the company record with correct field mapping
            $company = Company::create([
                'company_id' => $companyId,
                'name' => $request->company_name,
                'registration_number' => $request->registration_number,
                'tax_id' => $request->tax_id,
                'address' => $fullAddress,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'contact_person_name' => $request->contact_name,
                'contact_person_title' => $request->contact_title,
                'contact_person_phone' => $request->phone,
                'contact_person_email' => $request->email,
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'password' => $request->password, // Will be hashed by the mutator
                'industry' => $industryMapping[$request->business_type] ?? 'Other',
                'description' => $request->driver_requirements,
                'status' => 'Active',
                'verification_status' => 'Pending',
            ]);

            // Log the successful registration
            \Log::info('Company registration completed', [
                'company_id' => $company->company_id,
                'name' => $company->name,
                'email' => $company->email,
                'ip' => $request->ip()
            ]);

            // Automatically log in the company after registration
            Auth::guard('company')->login($company, true);

            return redirect()->route('company.dashboard')->with('success', 
                'Welcome to Drivelink! Your company registration is complete. ' .
                'Your Company ID is: ' . $company->company_id
            );

        } catch (\Exception $e) {
            \Log::error('Company registration failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->except(['_token', 'password', 'password_confirmation']),
                'ip' => $request->ip()
            ]);

            return back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->withErrors(['general' => 'There was an error processing your registration. Please try again.']);
        }
    }

    /**
     * Show the company dashboard
     */
    public function dashboard()
    {
        $company = Auth::guard('company')->user();
        
        // Get basic statistics
        $stats = [
            'total_requests' => $company->total_requests ?? 0,
            'fulfilled_requests' => $company->fulfilled_requests ?? 0,
            'average_rating' => $company->average_rating ?? 0,
            'fulfillment_rate' => $company->fulfillment_rate ?? 0,
        ];

        return view('company.dashboard', compact('company', 'stats'));
    }

    /**
     * Handle company logout
     */
    public function logout(Request $request)
    {
        $company = Auth::guard('company')->user();
        
        // Log the logout
        \Log::info('Company logged out', [
            'company_id' => $company->company_id,
            'company_name' => $company->name,
            'ip' => $request->ip()
        ]);

        Auth::guard('company')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('company.login')->with('message', 'You have been logged out successfully.');
    }

    /**
     * Generate unique company ID
     */
    private function generateCompanyId()
    {
        do {
            $id = 'CP' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Company::where('company_id', $id)->exists());
        
        return $id;
    }
}
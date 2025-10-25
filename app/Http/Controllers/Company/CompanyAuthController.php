<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use App\Services\CompanyService;

class CompanyAuthController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Show the company login form
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('company')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('company.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Show the company registration form
     */
    public function showRegistrationForm()
    {
        return view('company.auth.register');
    }

    /**
     * Handle company registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:companies',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $company = $this->companyService->register([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'address' => $request->address,
            ]);

            Auth::guard('company')->login($company);

            return redirect()->route('company.dashboard')->with('success', 'Registration successful! Welcome to Drivelink.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    /**
     * Handle company logout
     */
    public function logout(Request $request)
    {
        Auth::guard('company')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('company.login');
    }

    /**
     * Show company dashboard
     */
    public function dashboard()
    {
        $company = Auth::guard('company')->user();

        // Get dashboard statistics
        $stats = [
            'total_requests' => $company->requests()->count(),
            'active_requests' => $company->requests()->where('status', 'active')->count(),
            'total_matches' => $company->matches()->count(),
            'total_fleets' => $company->fleets()->count(),
            'total_vehicles' => $company->vehicles()->count(),
            'pending_invoices' => $company->invoices()->where('status', 'pending')->count(),
            'total_spent' => $company->invoices()->where('status', 'paid')->sum('amount'),
            'completed_requests' => $company->requests()->where('status', 'completed')->count(),
        ];

        // Get recent activity
        $recentRequests = $company->requests()->latest()->take(5)->get();
        $recentMatches = $company->matches()->with('companyRequest')->latest()->take(5)->get();

        return view('company.dashboard', compact('stats', 'recentRequests', 'recentMatches'));
    }
}

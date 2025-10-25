<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CompanyAuthController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'registration_number' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'address' => 'required|string',
            'state' => 'required|string|max:100',
            'lga' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'company_size' => 'nullable|string',
            'description' => 'nullable|string',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_title' => 'nullable|string|max:100',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'default_commission_rate' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string|max:255',
            'preferred_regions' => 'nullable|array',
            'vehicle_types_needed' => 'nullable|array',
            'profile' => 'nullable|array',
        ]);

        try {
            $company = $this->companyService->register($request->all());

            $token = $company->createToken('company-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Company registered successfully. Please check your email for verification.',
                'data' => [
                    'company' => $company,
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $company = Company::where('email', $request->email)->first();

        if (!$company || !Hash::check($request->password, $company->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($company->verification_status !== 'Verified') {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not verified. Please check your email for verification instructions.',
            ], 403);
        }

        if ($company->status !== 'Active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Account is not active.',
            ], 403);
        }

        $token = $company->createToken('company-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'company' => $company,
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'state' => 'sometimes|string|max:100',
            'lga' => 'sometimes|string|max:100',
            'industry' => 'sometimes|string|max:100',
            'company_size' => 'sometimes|string',
            'description' => 'sometimes|string',
            'contact_person_name' => 'sometimes|string|max:255',
            'contact_person_title' => 'sometimes|string|max:100',
            'contact_person_phone' => 'sometimes|string|max:20',
            'contact_person_email' => 'sometimes|email|max:255',
            'website' => 'sometimes|url|max:255',
            'default_commission_rate' => 'sometimes|numeric|min:0|max:100',
            'payment_terms' => 'sometimes|string|max:255',
            'preferred_regions' => 'sometimes|array',
            'vehicle_types_needed' => 'sometimes|array',
        ]);

        $company = $request->user();
        $company->update($request->only([
            'name', 'phone', 'address', 'state', 'lga', 'industry',
            'company_size', 'description', 'contact_person_name',
            'contact_person_title', 'contact_person_phone', 'contact_person_email',
            'website', 'default_commission_rate', 'payment_terms',
            'preferred_regions', 'vehicle_types_needed',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $company,
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $company = Company::where('email_verification_token', $request->token)->first();

        if (!$company) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification token',
            ], 400);
        }

        $company->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully',
        ]);
    }
}

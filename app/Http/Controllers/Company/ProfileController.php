<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\CompanyService;

class ProfileController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Show company profile edit form
     */
    public function edit()
    {
        $company = Auth::guard('company')->user();

        return view('company.settings', compact('company'));
    }

    /**
     * Update company profile
     */
    public function update(Request $request)
    {
        $company = Auth::guard('company')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $updateData = $request->only([
                'name', 'email', 'phone', 'website', 'address', 'description'
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($company->logo && Storage::exists('public/' . $company->logo)) {
                    Storage::delete('public/' . $company->logo);
                }

                $logoPath = $request->file('logo')->store('logos', 'public');
                $updateData['logo'] = $logoPath;
            }

            $this->companyService->updateProfile($company, $updateData);

            return back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update profile. Please try again.'])->withInput();
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        $company = Auth::guard('company')->user();

        $validator = Validator::make($request->all(), [
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'match_notifications' => 'boolean',
            'invoice_notifications' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $preferences = $request->only([
                'email_notifications',
                'sms_notifications',
                'match_notifications',
                'invoice_notifications'
            ]);

            $this->companyService->updateNotificationPreferences($company, $preferences);

            return back()->with('success', 'Notification preferences updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update preferences. Please try again.']);
        }
    }

    /**
     * Change company password
     */
    public function updatePassword(Request $request)
    {
        $company = Auth::guard('company')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validator->after(function ($validator) use ($company) {
            if (!Hash::check($validator->getData()['current_password'], $company->password)) {
                $validator->errors()->add('current_password', 'Current password is incorrect.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $this->companyService->updatePassword($company, $request->password);

            return back()->with('success', 'Password changed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to change password. Please try again.']);
        }
    }

    /**
     * Enable 2FA for company
     */
    public function enable2FA(Request $request)
    {
        $company = Auth::guard('company')->user();

        try {
            $result = $this->companyService->enable2FA($company);

            return response()->json([
                'success' => true,
                'qr_code_url' => $result['qr_code_url'],
                'secret' => $result['secret']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to setup 2FA'
            ], 500);
        }
    }

    /**
     * Verify 2FA setup
     */
    public function verify2FA(Request $request)
    {
        $company = Auth::guard('company')->user();

        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        try {
            $this->companyService->verify2FA($company, $request->code);

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication enabled successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }
    }

    /**
     * Get login history
     */
    public function getLoginHistory()
    {
        $company = Auth::guard('company')->user();

        try {
            $history = $this->companyService->getLoginHistory($company);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load login history'
            ], 500);
        }
    }
}

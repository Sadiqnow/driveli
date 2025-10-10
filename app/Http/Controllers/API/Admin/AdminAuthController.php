<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|max:255',
        ]);

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials provided.',
            ], 401);
        }

        if (!$admin->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please contact support.',
            ], 403);
        }

        // Update last login
        $admin->updateLastLogin($request->ip());

        // Create token
        $token = $admin->createToken('admin-token', ['admin', 'api-access'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => $admin->role,
                    'initials' => $admin->initials,
                    'avatar' => $admin->avatar,
                    'last_login_at' => $admin->last_login_at,
                ],
                'token' => $token,
                'permissions' => $admin->permissions ?? [],
            ],
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

    public function profile(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->formatted_phone,
                    'role' => $admin->role,
                    'status' => $admin->status,
                    'initials' => $admin->initials,
                    'avatar' => $admin->avatar,
                    'last_login_at' => $admin->last_login_at,
                    'created_at' => $admin->created_at,
                ],
                'permissions' => $admin->permissions ?? [],
                'statistics' => [
                    'drivers_verified' => $admin->verifiedDrivers()->count(),
                    'requests_created' => $admin->createdRequests()->count(),
                    'notifications_sent' => $admin->sentNotifications()->count(),
                ],
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->new_password) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }
        }

        // Update profile
        $admin->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => $request->new_password ? Hash::make($request->new_password) : $admin->password,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->formatted_phone,
                    'role' => $admin->role,
                    'initials' => $admin->initials,
                ],
            ],
        ]);
    }
}
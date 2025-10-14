<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Drivers;

class DriverProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:driver');
    }

    /**
     * Show the authenticated driver's profile.
     */
    public function show()
    {
        $driver = Auth::user();

        return view('driver.profile.show', compact('driver'));
    }

    /**
     * Show the edit form for the authenticated driver's profile.
     */
    public function edit()
    {
        $driver = Auth::user();

        return view('driver.profile.edit', compact('driver'));
    }

    /**
     * Update the authenticated driver's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::guard('driver')->user();

        $data = $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'surname'       => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('drivers')->ignore($user->id)],
            'phone'         => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date'],
            'gender'        => ['nullable', 'in:Male,Female,Other'],
            'license_class' => ['nullable', 'in:C,D,E'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // If password provided, hash it
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->first_name    = $data['first_name'];
        $user->surname       = $data['surname'];
        $user->email         = $data['email'];
        $user->phone         = $data['phone'] ?? $user->phone;
        $user->date_of_birth = $data['date_of_birth'] ?? $user->date_of_birth;
        $user->gender        = $data['gender'] ?? $user->gender;
        $user->license_class = $data['license_class'] ?? $user->license_class;

        $user->save();

        return redirect()->route('driver.profile.show')->with('status', 'Profile updated successfully.');
    }

    /**
     * Update avatar/profile photo for the authenticated driver.
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        $file = $request->file('avatar');

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar in storage/app/public/avatars
        $path = $file->store('avatars', 'public');

        // Save relative path (e.g. avatars/xxx.jpg). Adjust according to your DB column.
        $user->avatar = $path;
        $user->save();

        return redirect()->route('driver.profile.show')->with('status', 'Avatar updated successfully.');
    }

    /**
     * Show the authenticated driver's documents.
     */
    public function showDocuments()
    {
        $driver = Auth::guard('driver')->user();
        $documents = $driver->documents ?? collect();

        return view('driver.profile.documents', compact('driver', 'documents'));
    }

    /**
     * Upload a document for the authenticated driver.
     */
    public function uploadDocument(Request $request)
    {
        $driver = Auth::guard('driver')->user();

        $request->validate([
            'document_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'document_type' => 'required|string|in:nin,license_front,license_back,profile_picture,passport_photo,employment_letter,service_certificate,vehicle_papers,insurance,other',
            'description' => 'nullable|string|max:255'
        ]);

        $file = $request->file('document_file');
        $path = $file->store('driver_documents', 'public');

        $driver->documents()->create([
            'document_type' => $request->document_type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Show the change password form.
     */
    public function showChangePassword()
    {
        return view('driver.profile.change-password');
    }

    /**
     * Change the authenticated driver's password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::guard('driver')->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('driver.profile.show')->with('status', 'Password changed successfully.');
    }

    /**
     * Delete the authenticated driver's account.
     */
    public function destroy(Request $request)
    {
        $user = Auth::guard('driver')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
        }

        // Optionally remove avatar file
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Log out and delete user
        Auth::guard('driver')->logout();

        $user->delete();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been deleted.');
    }
}

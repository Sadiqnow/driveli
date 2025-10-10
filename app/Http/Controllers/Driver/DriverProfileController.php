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
        $this->middleware('auth');
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
        $user = Auth::user();

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'   => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // If password provided, hash it
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->name    = $data['name'];
        $user->email   = $data['email'];
        $user->phone   = $data['phone'] ?? $user->phone;
        $user->address = $data['address'] ?? $user->address;

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
            'document_type' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $file = $request->file('document');
        $path = $file->store('driver_documents', 'public');

        $driver->documents()->create([
            'document_type' => $request->document_type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
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

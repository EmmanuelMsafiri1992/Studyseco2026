<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Debug: Log what the server receives
        \Log::debug('Profile update request received:', [
            'all_data' => $request->all(),
            'files' => $request->allFiles(),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->has('password') ? 'present' : 'missing',
            'profile_photo' => $request->hasFile('profile_photo') ? [
                'original_name' => $request->file('profile_photo')->getClientOriginalName(),
                'mime_type' => $request->file('profile_photo')->getMimeType(),
                'size' => $request->file('profile_photo')->getSize(),
                'is_valid' => $request->file('profile_photo')->isValid()
            ] : 'no_file',
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        $user = $request->user();
        $validated = $request->validated();
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            \Log::debug('Processing profile photo upload...');
            
            // Delete old profile photo if exists
            if ($user->profile_photo_url && Storage::exists($user->profile_photo_url)) {
                \Log::debug('Deleting old profile photo: ' . $user->profile_photo_url);
                Storage::delete($user->profile_photo_url);
            }
            
            // Store new profile photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_url'] = $path;
            
            \Log::debug('New profile photo stored at: ' . $path);
        } else {
            \Log::debug('No profile photo file found in request');
        }
        
        // Handle password update
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        // Remove profile_photo from validated data as it's handled above
        unset($validated['profile_photo']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's profile photo.
     */
    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_url && Storage::exists('public/' . $user->profile_photo_url)) {
            Storage::delete('public/' . $user->profile_photo_url);
        }

        $user->forceFill([
            'profile_photo_url' => null,
        ])->save();

        return Redirect::back()->with('success', 'Profile photo removed successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

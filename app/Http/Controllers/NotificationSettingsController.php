<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationSettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $settings = $user->notification_settings ?? [];

        // Default notification settings
        $defaultSettings = [
            'sounds_enabled' => true,
            'sound_types' => [
                'payment' => true,
                'message' => true,
                'achievement' => true,
                'enrollment' => true,
                'assignment' => true,
                'system' => true
            ]
        ];

        $currentSettings = array_merge($defaultSettings, $settings);

        return Inertia::render('Profile/NotificationSettings', [
            'settings' => $currentSettings
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'sounds_enabled' => 'boolean',
            'sound_types' => 'array',
            'sound_types.payment' => 'boolean',
            'sound_types.message' => 'boolean',
            'sound_types.achievement' => 'boolean',
            'sound_types.enrollment' => 'boolean',
            'sound_types.assignment' => 'boolean',
            'sound_types.system' => 'boolean',
        ]);

        $user = auth()->user();
        $user->updateNotificationSettings($request->only(['sounds_enabled', 'sound_types']));

        return back()->with('success', 'Notification settings updated successfully.');
    }

    public function getSettings()
    {
        $user = auth()->user();
        $settings = $user->notification_settings ?? [];

        // Return settings compatible with DashboardLayout expectations
        return response()->json([
            'sound_enabled' => $settings['sounds_enabled'] ?? true,
            'desktop_enabled' => $settings['desktop_enabled'] ?? true,
            'sounds_enabled' => $settings['sounds_enabled'] ?? true,
            'sound_types' => $settings['sound_types'] ?? [
                'payment' => true,
                'message' => true,
                'achievement' => true,
                'enrollment' => true,
                'assignment' => true,
                'system' => true
            ]
        ]);
    }
}
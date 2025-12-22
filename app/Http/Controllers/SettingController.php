<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'settings' => []
        ]);
    }

    public function update(Request $request)
    {
        // Handle settings update
        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}

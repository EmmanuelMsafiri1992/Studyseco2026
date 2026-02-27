<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\CloudStorageService;
use App\Services\HeyGenService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemSettingsController extends Controller
{
    public function index()
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can manage system settings.');
        }

        $settings = SystemSetting::getAllGrouped();

        // Ensure default settings exist
        $this->ensureDefaultSettings();

        return Inertia::render('Admin/SystemSettings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can manage system settings.');
        }

        $validated = $request->validate([
            'settings' => 'required',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg|max:2048',
        ]);

        // Handle different settings format (JSON string or array)
        $settings = $validated['settings'];
        if (is_string($settings)) {
            $settings = json_decode($settings, true);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            SystemSetting::set('logo_url', '/storage/' . $logoPath);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('favicons', 'public');
            SystemSetting::set('favicon_url', '/storage/' . $faviconPath);
        }

        if (is_array($settings)) {
            foreach ($settings as $settingData) {
                if (isset($settingData['key']) && array_key_exists('value', $settingData)) {
                    $setting = SystemSetting::where('key', $settingData['key'])->first();
                    if ($setting) {
                        // Update existing setting
                        $setting->value = $settingData['value'];
                        $setting->save();
                    } else {
                        // Create new setting (shouldn't happen with defaults, but just in case)
                        SystemSetting::create([
                            'key' => $settingData['key'],
                            'value' => $settingData['value'],
                            'name' => $settingData['key'],
                            'type' => 'text',
                            'group' => 'general',
                            'description' => ''
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'System settings updated successfully.');
    }

    private function ensureDefaultSettings()
    {
        $defaultSettings = [
            // General Settings
            [
                'key' => 'app_name',
                'name' => 'Application Name',
                'value' => 'StudySeco',
                'type' => 'text',
                'group' => 'general',
                'description' => 'The name of your application'
            ],
            [
                'key' => 'app_description', 
                'name' => 'Application Description',
                'value' => 'Excellence in Malawian Secondary Education - Comprehensive online learning platform',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Short description of your application'
            ],
            [
                'key' => 'app_tagline',
                'name' => 'Application Tagline',
                'value' => 'Unlock Your Potential with StudySeco',
                'type' => 'text', 
                'group' => 'general',
                'description' => 'Catchy tagline for your application'
            ],
            
            // Contact Information
            [
                'key' => 'contact_email',
                'name' => 'Contact Email',
                'value' => 'info@studyseco.com',
                'type' => 'email',
                'group' => 'contact',
                'description' => 'Main contact email address'
            ],
            [
                'key' => 'contact_phone',
                'name' => 'Contact Phone',
                'value' => '+265 123 456 789',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Main contact phone number'
            ],
            [
                'key' => 'contact_address',
                'name' => 'Contact Address',
                'value' => 'Lilongwe, Malawi',
                'type' => 'textarea',
                'group' => 'contact',
                'description' => 'Physical address'
            ],
            [
                'key' => 'social_facebook',
                'name' => 'Facebook URL',
                'value' => 'https://facebook.com/studyseco',
                'type' => 'url',
                'group' => 'contact',
                'description' => 'Facebook page URL'
            ],
            [
                'key' => 'social_twitter',
                'name' => 'Twitter URL',
                'value' => 'https://twitter.com/studyseco',
                'type' => 'url',
                'group' => 'contact',
                'description' => 'Twitter profile URL'
            ],
            
            // Appearance
            [
                'key' => 'logo_url',
                'name' => 'Logo URL',
                'value' => '/images/logo.png',
                'type' => 'image',
                'group' => 'appearance',
                'description' => 'Main application logo'
            ],
            [
                'key' => 'favicon_url',
                'name' => 'Favicon URL', 
                'value' => '/images/favicon.ico',
                'type' => 'image',
                'group' => 'appearance',
                'description' => 'Browser favicon'
            ],
            [
                'key' => 'primary_color',
                'name' => 'Primary Color',
                'value' => '#3B82F6',
                'type' => 'color',
                'group' => 'appearance',
                'description' => 'Primary brand color'
            ],
            
            // Footer
            [
                'key' => 'footer_text',
                'name' => 'Footer Text',
                'value' => '© 2024 StudySeco. All rights reserved. Excellence in Malawian Secondary Education.',
                'type' => 'textarea',
                'group' => 'footer',
                'description' => 'Footer copyright text'
            ],
            [
                'key' => 'footer_links',
                'name' => 'Footer Links',
                'value' => json_encode([
                    ['name' => 'Privacy Policy', 'url' => '/privacy'],
                    ['name' => 'Terms of Service', 'url' => '/terms'],
                    ['name' => 'Contact Us', 'url' => '/contact'],
                ]),
                'type' => 'json',
                'group' => 'footer',
                'description' => 'Footer navigation links'
            ],

            // Verification Settings
            [
                'key' => 'email_verification_required',
                'name' => 'Email Verification Required',
                'value' => true,
                'type' => 'boolean',
                'group' => 'verification',
                'description' => 'Require email verification for student enrollment'
            ],
            [
                'key' => 'phone_verification_required',
                'name' => 'Phone Verification Required',
                'value' => true,
                'type' => 'boolean',
                'group' => 'verification',
                'description' => 'Require phone verification for student enrollment'
            ],
            [
                'key' => 'verification_for_trial',
                'name' => 'Verification Required for Trial',
                'value' => false,
                'type' => 'boolean',
                'group' => 'verification',
                'description' => 'Require verification even for free trials'
            ],
            [
                'key' => 'verification_for_paid',
                'name' => 'Verification Required for Paid',
                'value' => true,
                'type' => 'boolean',
                'group' => 'verification',
                'description' => 'Require verification for paid enrollments'
            ],

            // Academic Settings
            [
                'key' => 'academic_year',
                'name' => 'Current Academic Year',
                'value' => '2024/2025',
                'type' => 'text',
                'group' => 'academic',
                'description' => 'Current academic year'
            ],
            [
                'key' => 'grade_levels',
                'name' => 'Grade Levels',
                'value' => json_encode(['Form 1', 'Form 2', 'Form 3', 'Form 4']),
                'type' => 'json',
                'group' => 'academic',
                'description' => 'Available grade levels'
            ],

            // Cloud Storage Settings
            [
                'key' => 'cloud_storage_provider',
                'name' => 'Cloud Storage Provider',
                'value' => 'local',
                'type' => 'text',
                'group' => 'storage',
                'description' => 'Select cloud storage provider: local, s3, or gcs'
            ],

            // S3 Storage Settings
            [
                'key' => 's3_enabled',
                'name' => 'Enable S3 Storage',
                'value' => false,
                'type' => 'boolean',
                'group' => 'storage',
                'description' => 'Enable Amazon S3 for video storage and streaming'
            ],
            [
                'key' => 's3_access_key',
                'name' => 'AWS Access Key ID',
                'value' => '',
                'type' => 'text',
                'group' => 'storage',
                'description' => 'Your AWS Access Key ID'
            ],
            [
                'key' => 's3_secret_key',
                'name' => 'AWS Secret Access Key',
                'value' => '',
                'type' => 'password',
                'group' => 'storage',
                'description' => 'Your AWS Secret Access Key (stored securely)'
            ],
            [
                'key' => 's3_bucket',
                'name' => 'S3 Bucket Name',
                'value' => '',
                'type' => 'text',
                'group' => 'storage',
                'description' => 'The name of your S3 bucket'
            ],
            [
                'key' => 's3_region',
                'name' => 'AWS Region',
                'value' => 'us-east-1',
                'type' => 'text',
                'group' => 'storage',
                'description' => 'AWS region (e.g., us-east-1, eu-west-1)'
            ],
            [
                'key' => 's3_url',
                'name' => 'S3 URL / CloudFront URL',
                'value' => '',
                'type' => 'url',
                'group' => 'storage',
                'description' => 'Custom URL for S3 or CloudFront distribution (optional)'
            ],
            [
                'key' => 's3_endpoint',
                'name' => 'Custom S3 Endpoint',
                'value' => '',
                'type' => 'url',
                'group' => 'storage',
                'description' => 'Custom endpoint for S3-compatible services (leave empty for AWS S3)'
            ],

            // Google Cloud Storage Settings
            [
                'key' => 'gcs_enabled',
                'name' => 'Enable Google Cloud Storage',
                'value' => false,
                'type' => 'boolean',
                'group' => 'storage',
                'description' => 'Enable Google Cloud Storage for video storage and streaming'
            ],
            [
                'key' => 'gcs_project_id',
                'name' => 'GCS Project ID',
                'value' => '',
                'type' => 'text',
                'group' => 'storage',
                'description' => 'Your Google Cloud project ID'
            ],
            [
                'key' => 'gcs_bucket',
                'name' => 'GCS Bucket Name',
                'value' => '',
                'type' => 'text',
                'group' => 'storage',
                'description' => 'The name of your Google Cloud Storage bucket'
            ],
            [
                'key' => 'gcs_key_file',
                'name' => 'GCS Service Account Key (JSON)',
                'value' => '',
                'type' => 'textarea',
                'group' => 'storage',
                'description' => 'Paste your service account JSON key file contents'
            ],
            [
                'key' => 'gcs_path_prefix',
                'name' => 'GCS Path Prefix',
                'value' => '',
                'type' => 'text',
                'group' => 'storage',
                'description' => 'Optional path prefix for files in the bucket'
            ],
            [
                'key' => 'gcs_url',
                'name' => 'GCS Custom URL / CDN URL',
                'value' => '',
                'type' => 'url',
                'group' => 'storage',
                'description' => 'Custom URL or Cloud CDN URL for faster delivery (optional)'
            ],

            // HeyGen AI Video Settings
            [
                'key' => 'heygen_enabled',
                'name' => 'Enable HeyGen AI Videos',
                'value' => false,
                'type' => 'boolean',
                'group' => 'heygen',
                'description' => 'Enable HeyGen AI avatar video generation for lessons'
            ],
            [
                'key' => 'heygen_api_key',
                'name' => 'HeyGen API Key',
                'value' => '',
                'type' => 'password',
                'group' => 'heygen',
                'description' => 'Your HeyGen API key (get it from app.heygen.com/settings/api)'
            ],
            [
                'key' => 'heygen_default_test_mode',
                'name' => 'Default to Test Mode',
                'value' => true,
                'type' => 'boolean',
                'group' => 'heygen',
                'description' => 'Default to test mode (watermarked videos, no credit usage) for safety'
            ]
        ];

        foreach ($defaultSettings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Test S3 connection with provided credentials
     */
    public function testS3Connection(Request $request)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can test S3 connection.'
            ], 403);
        }

        $validated = $request->validate([
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'bucket' => 'required|string',
            'region' => 'required|string',
            'endpoint' => 'nullable|string',
        ]);

        $result = CloudStorageService::testS3Connection($validated);

        return response()->json($result);
    }

    /**
     * Test GCS connection with provided credentials
     */
    public function testGcsConnection(Request $request)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can test GCS connection.'
            ], 403);
        }

        $validated = $request->validate([
            'project_id' => 'required|string',
            'bucket' => 'required|string',
            'key_file' => 'required|string',
        ]);

        $result = CloudStorageService::testGcsConnection($validated);

        return response()->json($result);
    }

    /**
     * Test HeyGen connection with provided API key
     */
    public function testHeyGenConnection(Request $request)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can test HeyGen connection.'
            ], 403);
        }

        $validated = $request->validate([
            'api_key' => 'required|string',
        ]);

        try {
            // Temporarily set the API key for testing
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-Api-Key' => $validated['api_key'],
                'Content-Type' => 'application/json',
            ])->timeout(30)->get('https://api.heygen.com/v1/user/remaining_quota');

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'HeyGen connection successful!',
                    'credits' => $data['data'] ?? null,
                ]);
            } else {
                $error = $response->json();
                return response()->json([
                    'success' => false,
                    'message' => $error['error'] ?? $error['message'] ?? 'Invalid API key or connection failed',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }
}
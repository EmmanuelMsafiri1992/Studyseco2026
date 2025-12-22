<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Notifications\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function show($enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        // Check if already verified
        if ($enrollment->is_verified) {
            return redirect()->route('enrollment.success')->with('enrollment_id', $enrollment->id);
        }

        return Inertia::render('Verification', [
            'enrollment' => $enrollment,
            'verificationStatus' => $enrollment->verification_status
        ]);
    }

    public function sendEmailVerification(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        if ($enrollment->email_verified_at) {
            return response()->json(['message' => 'Email already verified'], 422);
        }

        // Generate verification code
        $token = $enrollment->generateEmailVerificationCode();
        
        // Send verification email
        try {
            Notification::route('mail', $enrollment->email)
                ->notify(new VerificationCode($token, 'email'));

            return response()->json([
                'message' => 'Verification email sent successfully',
                'expires_at' => $enrollment->verification_expires_at
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send verification email'], 500);
        }
    }

    public function sendPhoneVerification(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        if ($enrollment->phone_verified_at) {
            return response()->json(['message' => 'Phone already verified'], 422);
        }

        // Generate verification code
        $code = $enrollment->generatePhoneVerificationCode();
        
        // In a real implementation, you would send SMS here
        // For now, we'll just log it or send via email for testing
        try {
            // For testing, send the phone code via email too
            Notification::route('mail', $enrollment->email)
                ->notify(new VerificationCode($code, 'phone'));

            return response()->json([
                'message' => 'Verification code sent to your phone (and email for testing)',
                'code' => env('APP_DEBUG') ? $code : null, // Only show in debug mode
                'expires_at' => $enrollment->verification_expires_at
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send verification code'], 500);
        }
    }

    public function verifyEmail(Request $request, $enrollmentId)
    {
        $request->validate([
            'token' => 'required|string|max:60'
        ]);

        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        if ($enrollment->verifyEmailCode($request->token)) {
            $response = [
                'message' => 'Email verified successfully',
                'verification_status' => $enrollment->refresh()->verification_status
            ];

            if ($enrollment->is_verified) {
                $response['redirect'] = route('enrollment.success') . '?enrollment_id=' . $enrollment->id;
            }

            return response()->json($response);
        }

        return response()->json(['message' => 'Invalid or expired verification code'], 422);
    }

    public function verifyPhone(Request $request, $enrollmentId)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        if ($enrollment->verifyPhoneCode($request->code)) {
            $response = [
                'message' => 'Phone verified successfully',
                'verification_status' => $enrollment->refresh()->verification_status
            ];

            if ($enrollment->is_verified) {
                $response['redirect'] = route('enrollment.success') . '?enrollment_id=' . $enrollment->id;
            }

            return response()->json($response);
        }

        return response()->json(['message' => 'Invalid or expired verification code'], 422);
    }

    public function status($enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        return response()->json([
            'is_verified' => $enrollment->is_verified,
            'email_verified' => (bool) $enrollment->email_verified_at,
            'phone_verified' => (bool) $enrollment->phone_verified_at,
            'verification_status' => $enrollment->verification_status,
            'needs_verification' => $enrollment->needsVerification()
        ]);
    }
}
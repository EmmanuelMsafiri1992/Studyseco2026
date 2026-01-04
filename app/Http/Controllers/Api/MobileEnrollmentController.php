<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\PaymentMethod;
use App\Models\AccessDuration;
use App\Models\Enrollment;
use App\Models\EnrollmentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MobileEnrollmentController extends Controller
{
    // Get payment methods
    public function paymentMethods(Request $request)
    {
        $region = $request->get('region', 'malawi');

        $methods = PaymentMethod::active()
            ->where('region', $region)
            ->get()
            ->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'code' => $method->code,
                    'type' => $method->type,
                    'currency' => $method->currency,
                    'instructions' => $method->instructions,
                    'icon' => $method->icon,
                    'description' => $method->description,
                    'account_details' => $method->account_details,
                ];
            });

        return response()->json(['data' => $methods]);
    }

    // Get access durations
    public function accessDurations()
    {
        $durations = AccessDuration::active()
            ->get()
            ->map(function ($duration) {
                return [
                    'id' => $duration->id,
                    'name' => $duration->name,
                    'description' => $duration->description,
                    'days' => $duration->days,
                    'months' => $duration->months,
                    'price' => $duration->price,
                    'display' => $duration->duration_display,
                ];
            });

        return response()->json(['data' => $durations]);
    }

    // Get subjects available for enrollment
    public function availableSubjects(Request $request)
    {
        $gradeLevel = $request->get('grade_level');

        $query = Subject::where('is_active', true);

        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }

        $subjects = $query->orderBy('name')->get()->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'description' => $subject->description,
                'grade_level' => $subject->grade_level,
                'department' => $subject->department,
                'is_compulsory' => $subject->is_compulsory,
                'cover_image' => $subject->cover_image ? asset('storage/' . $subject->cover_image) : null,
            ];
        });

        return response()->json(['data' => $subjects]);
    }

    // Calculate pricing
    public function calculatePricing(Request $request)
    {
        $request->validate([
            'subject_ids' => 'required|array|min:1',
            'duration_id' => 'required|exists:access_durations,id',
            'currency' => 'nullable|string|in:MWK,ZAR,USD',
        ]);

        $subjectCount = count($request->subject_ids);
        $duration = AccessDuration::findOrFail($request->duration_id);
        $currency = $request->get('currency', 'MWK');

        // Price per subject based on currency
        $pricePerSubject = match ($currency) {
            'MWK' => 50000,
            'ZAR' => 350,
            'USD' => 25,
            default => 50000,
        };

        $subtotal = $pricePerSubject * $subjectCount;
        $durationMultiplier = $duration->days / 30; // Per month pricing
        $total = $subtotal * $durationMultiplier;

        // Apply discount for more subjects
        $discount = 0;
        if ($subjectCount >= 5) {
            $discount = $total * 0.1; // 10% off
        } elseif ($subjectCount >= 3) {
            $discount = $total * 0.05; // 5% off
        }

        $finalTotal = $total - $discount;

        return response()->json([
            'data' => [
                'subject_count' => $subjectCount,
                'price_per_subject' => $pricePerSubject,
                'duration_name' => $duration->name,
                'duration_days' => $duration->days,
                'currency' => $currency,
                'subtotal' => round($subtotal, 2),
                'duration_multiplier' => $durationMultiplier,
                'discount' => round($discount, 2),
                'discount_percent' => $subjectCount >= 5 ? 10 : ($subjectCount >= 3 ? 5 : 0),
                'total' => round($finalTotal, 2),
                'formatted_total' => $currency . ' ' . number_format($finalTotal, 2),
            ],
        ]);
    }

    // Submit new enrollment
    public function submitEnrollment(Request $request)
    {
        $request->validate([
            'subject_ids' => 'required|array|min:1',
            'duration_id' => 'required|exists:access_durations,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_proof' => 'required|image|max:5120', // 5MB max
            'reference_number' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $duration = AccessDuration::findOrFail($request->duration_id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // Calculate pricing
        $subjectCount = count($request->subject_ids);
        $pricePerSubject = $paymentMethod->subject_price;
        $durationMultiplier = $duration->days / 30;
        $total = $pricePerSubject * $subjectCount * $durationMultiplier;

        // Apply discount
        if ($subjectCount >= 5) {
            $total *= 0.9;
        } elseif ($subjectCount >= 3) {
            $total *= 0.95;
        }

        // Store payment proof
        $proofPath = $request->file('payment_proof')->store('enrollment-proofs', 'public');

        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'selected_subjects' => $request->subject_ids,
            'subject_count' => $subjectCount,
            'currency' => $paymentMethod->currency,
            'price_per_subject' => $pricePerSubject,
            'total_amount' => $total,
            'payment_method' => $paymentMethod->name,
            'payment_reference' => $request->reference_number,
            'payment_proof_path' => $proofPath,
            'status' => 'pending',
            'is_trial' => false,
        ]);

        // Create payment record
        EnrollmentPayment::create([
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => $total,
            'currency' => $paymentMethod->currency,
            'reference_number' => $request->reference_number,
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Enrollment submitted successfully. Please wait for approval.',
            'enrollment' => [
                'id' => $enrollment->id,
                'enrollment_number' => $enrollment->enrollment_number,
                'status' => $enrollment->status,
                'total_amount' => $total,
                'currency' => $paymentMethod->currency,
            ],
        ], 201);
    }

    // Start free trial
    public function startTrial(Request $request)
    {
        $request->validate([
            'subject_ids' => 'required|array|min:1|max:3',
        ]);

        $user = $request->user();

        // Check if user already had a trial
        $existingTrial = Enrollment::where('user_id', $user->id)
            ->where('is_trial', true)
            ->exists();

        if ($existingTrial) {
            return response()->json([
                'message' => 'You have already used your free trial.',
            ], 422);
        }

        // Create trial enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'selected_subjects' => $request->subject_ids,
            'subject_count' => count($request->subject_ids),
            'currency' => 'MWK',
            'price_per_subject' => 0,
            'total_amount' => 0,
            'status' => 'approved',
            'is_trial' => true,
            'trial_started_at' => now(),
            'trial_expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'message' => 'Your 7-day free trial has started!',
            'enrollment' => [
                'id' => $enrollment->id,
                'enrollment_number' => $enrollment->enrollment_number,
                'status' => 'approved',
                'is_trial' => true,
                'trial_ends_at' => $enrollment->trial_expires_at->toIso8601String(),
                'days_remaining' => 7,
            ],
        ], 201);
    }

    // Extend existing enrollment
    public function extendEnrollment(Request $request)
    {
        $request->validate([
            'duration_id' => 'required|exists:access_durations,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_proof' => 'required|image|max:5120',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $duration = AccessDuration::findOrFail($request->duration_id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // Get current enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'No active enrollment found.',
            ], 404);
        }

        $subjectCount = $enrollment->subject_count;
        $pricePerSubject = $paymentMethod->subject_price;
        $durationMultiplier = $duration->days / 30;
        $total = $pricePerSubject * $subjectCount * $durationMultiplier;

        // Store payment proof
        $proofPath = $request->file('payment_proof')->store('extension-proofs', 'public');

        // Create extension payment
        EnrollmentPayment::create([
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => $total,
            'currency' => $paymentMethod->currency,
            'reference_number' => $request->reference_number,
            'proof_path' => $proofPath,
            'status' => 'pending',
            'is_extension' => true,
            'extension_days' => $duration->days,
        ]);

        return response()->json([
            'message' => 'Extension request submitted. Please wait for approval.',
            'amount' => $total,
            'currency' => $paymentMethod->currency,
        ], 201);
    }

    // Get payment history
    public function paymentHistory(Request $request)
    {
        $user = $request->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        $payments = [];
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->payments as $payment) {
                $payments[] = [
                    'id' => $payment->id,
                    'enrollment_number' => $enrollment->enrollment_number,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'is_extension' => $payment->is_extension ?? false,
                    'reference_number' => $payment->reference_number,
                    'created_at' => $payment->created_at->toIso8601String(),
                    'verified_at' => $payment->verified_at?->toIso8601String(),
                ];
            }
        }

        // Sort by date
        usort($payments, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return response()->json(['data' => $payments]);
    }

    // Get current enrollment status
    public function enrollmentStatus(Request $request)
    {
        $user = $request->user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'approved')
                    ->where(function ($q) {
                        $q->where('access_expires_at', '>', now())
                            ->orWhere(function ($q2) {
                                $q2->where('is_trial', true)
                                    ->where('trial_expires_at', '>', now());
                            });
                    });
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'pending');
            })
            ->latest()
            ->first();

        if (!$enrollment) {
            return response()->json([
                'data' => [
                    'has_enrollment' => false,
                    'can_start_trial' => !Enrollment::where('user_id', $user->id)->where('is_trial', true)->exists(),
                ],
            ]);
        }

        $subjects = Subject::whereIn('id', $enrollment->selected_subjects ?? [])
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => [
                'has_enrollment' => true,
                'enrollment' => [
                    'id' => $enrollment->id,
                    'enrollment_number' => $enrollment->enrollment_number,
                    'status' => $enrollment->status,
                    'is_trial' => $enrollment->is_trial,
                    'subjects' => $subjects,
                    'subjects_count' => count($enrollment->selected_subjects ?? []),
                    'expires_at' => $enrollment->is_trial
                        ? $enrollment->trial_expires_at?->toIso8601String()
                        : $enrollment->access_expires_at?->toIso8601String(),
                    'days_remaining' => $enrollment->is_trial
                        ? $enrollment->trial_days_remaining
                        : $enrollment->access_days_remaining,
                    'created_at' => $enrollment->created_at->toIso8601String(),
                ],
                'can_start_trial' => false,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\EnrollmentPayment;
use App\Models\PaymentMethod;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\PaymentApproved;
use App\Notifications\PaymentRejected;
use App\Notifications\WelcomeEmail;
use App\Notifications\TrialWelcomeEmail;
use App\Notifications\EnrollmentPending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255', // Removed unique constraint - users can enroll before registering
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'country' => 'required|string|max:100',
            'selected_subjects' => 'required|array|min:1',
            'selected_subjects.*' => 'integer|exists:subjects,id',
            'enrollment_type' => 'required|in:trial,paid',
            'payment_method_id' => 'required_if:enrollment_type,paid|nullable|integer|exists:payment_methods,id',
            'payment_reference' => 'required_if:enrollment_type,paid|nullable|string|max:255',
            'payment_proof' => 'required_if:enrollment_type,paid|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'terms_accepted' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $isTrial = $validated['enrollment_type'] === 'trial';
        
        // Determine region based on country
        $region = Enrollment::getRegionForCountry($validated['country']);
        
        $totalAmount = 0;
        $currency = 'MWK';
        $paymentProofPath = null;
        
        $paymentMethodKey = null;
        if (!$isTrial) {
            // Get payment method to determine currency and pricing
            $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);
            $paymentMethodKey = $paymentMethod->code;
            $currency = $paymentMethod->currency;
            
            // Calculate pricing based on region/currency
            $subjectPrice = match($paymentMethod->currency) {
                'MWK' => 50000,
                'ZAR' => 350,
                'USD' => 25,
                default => 50000
            };
            
            $totalAmount = count($validated['selected_subjects']) * $subjectPrice;

            // Handle payment proof upload
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            }
        } else {
            // For trial, determine currency based on region
            $currency = match($region) {
                'malawi' => 'MWK',
                'south_africa' => 'ZAR',
                default => 'USD'
            };
        }

        // Calculate subject price based on region/currency
        $subjectPrice = match($currency) {
            'MWK' => 50000,
            'ZAR' => 350,
            'USD' => 25,
            default => 50000
        };

        // Create enrollment record
        $enrollment = Enrollment::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'country' => $validated['country'],
            'region' => $region,
            'selected_subjects' => $validated['selected_subjects'],
            'subject_count' => count($validated['selected_subjects']),
            'price_per_subject' => $subjectPrice,
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'is_trial' => $isTrial,
            'trial_started_at' => $isTrial ? now() : null,
            'trial_expires_at' => $isTrial ? now()->addDays(7) : null,
            'access_expires_at' => $isTrial ? now()->addDays(7) : now()->addDays(120), // 4 months
            'status' => $isTrial ? 'approved' : 'pending',
            'payment_method' => $isTrial ? 'trial' : $paymentMethodKey,
            'payment_reference' => $isTrial ? 'TRIAL_' . strtoupper(uniqid()) : ($validated['payment_reference'] ?? null),
            'payment_proof_path' => $paymentProofPath
        ]);

        if (!$isTrial) {
            // Create payment record
            EnrollmentPayment::create([
                'enrollment_id' => $enrollment->id,
                'payment_method_id' => $validated['payment_method_id'],
                'reference_number' => $validated['payment_reference'],
                'amount' => $totalAmount,
                'currency' => $currency,
                'payment_proof_path' => $paymentProofPath,
                'status' => 'pending'
            ]);
        }
        
        // Create user account immediately for trial, or pending verification for paid
        if ($isTrial) {
            $tempPassword = 'StudySeco' . rand(1000, 9999);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($tempPassword),
                'role' => 'student',
                'email_verified_at' => now()
            ]);
            
            // Link user to enrollment
            $enrollment->update(['user_id' => $user->id]);
            
            // Send welcome email with login details
            try {
                $user->notify(new TrialWelcomeEmail($user, $enrollment, $tempPassword));
            } catch (\Exception $e) {
                // Log error but don't fail the enrollment
                logger('Failed to send trial welcome email: ' . $e->getMessage());
            }
        } else {
            // For paid enrollments, send pending notification
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $enrollment->email)
                    ->notify(new EnrollmentPending($enrollment));
            } catch (\Exception $e) {
                // Log error but don't fail the enrollment
                logger('Failed to send enrollment pending email: ' . $e->getMessage());
            }
        }

        // Redirect to verification if verification is required, otherwise to success page
        if ($enrollment->needsVerification()) {
            return redirect()->route('verification.show', $enrollment->id);
        }
        
        return redirect()->route('enrollment.success')->with('enrollment_id', $enrollment->id);
    }

    public function success(Request $request)
    {
        $enrollmentId = session('enrollment_id');
        if (!$enrollmentId) {
            return redirect()->route('welcome');
        }

        $enrollment = Enrollment::find($enrollmentId);
        if (!$enrollment) {
            return redirect()->route('welcome');
        }

        return Inertia::render('EnrollmentSuccess', [
            'enrollment' => $enrollment,
            'subjectNames' => $enrollment->selected_subjects_names
        ]);
    }

    // Admin methods
    public function index()
    {
        $enrollments = Enrollment::with(['payments.paymentMethod', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $enrollmentStats = [
            'total' => Enrollment::count(),
            'pending' => Enrollment::where('status', 'pending')->count(),
            'approved' => Enrollment::where('status', 'approved')->count(),
            'rejected' => Enrollment::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Enrollments/Index', [
            'enrollments' => $enrollments,
            'enrollmentStats' => $enrollmentStats
        ]);
    }

    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['user', 'payments.paymentMethod']);
        
        return Inertia::render('Admin/Enrollments/Show', [
            'enrollment' => $enrollment,
            'subjectNames' => $enrollment->selected_subjects_names
        ]);
    }

    public function approve(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'verification_expires_at' => null
        ]);

        // Update payment status
        $enrollment->payments()->update(['status' => 'verified']);

        // Create user account if doesn't exist
        $user = User::where('email', $enrollment->email)->first();
        $tempPassword = null;
        $isNewUser = false;
        
        if (!$user) {
            $tempPassword = 'StudySeco' . rand(1000, 9999);
            $user = User::create([
                'name' => $enrollment->name,
                'email' => $enrollment->email,
                'phone' => $enrollment->phone,
                'password' => bcrypt($tempPassword),
                'role' => 'student',
                'email_verified_at' => now()
            ]);
            $isNewUser = true;
        } else {
            // For existing users, generate new temporary password
            $tempPassword = 'StudySeco' . rand(1000, 9999);
            $user->update([
                'password' => bcrypt($tempPassword),
                'email_verified_at' => now()
            ]);
        }

        // Link user to enrollment
        $enrollment->update(['user_id' => $user->id]);

        // Send welcome email with login credentials
        try {
            $user->notify(new WelcomeEmail($user, $enrollment, $tempPassword));
        } catch (\Exception $e) {
            // Log error but don't fail the approval
            logger('Failed to send welcome email: ' . $e->getMessage());
        }

        return redirect()->back()->with('message', 'Enrollment approved successfully! Student has been notified.');
    }

    public function reject(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'rejection_reason' => 'required|string|max:500'
        ]);

        $enrollment->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'rejection_reason' => $request->rejection_reason
        ]);

        // Send rejection notification if user exists or via email
        if ($enrollment->email) {
            try {
                // Create temporary user object for notification (if user doesn't exist)
                $user = User::where('email', $enrollment->email)->first();
                if (!$user) {
                    // Send notification via email directly
                    Notification::route('mail', $enrollment->email)
                        ->notify(new PaymentRejected($enrollment, null, $request->rejection_reason));
                } else {
                    // Send to existing user
                    $user->notify(new PaymentRejected($enrollment, null, $request->rejection_reason));
                }
            } catch (\Exception $e) {
                // Log error but don't fail the rejection
                logger('Failed to send enrollment rejection notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('message', 'Enrollment rejected and student notified.');
    }

    public function extend(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'extension_months' => 'required|integer|min:1|max:12',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'payment_reference' => 'required|string|max:255',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        // Calculate extension price
        $extensionPrice = match($enrollment->currency) {
            'MWK' => 25000 * $request->extension_months, // Half price for extension
            'ZAR' => 175 * $request->extension_months,
            'USD' => 12.50 * $request->extension_months,
            default => 25000 * $request->extension_months
        };

        // Handle payment proof upload
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Create extension payment record
        EnrollmentPayment::create([
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => $request->payment_method_id,
            'reference_number' => $request->payment_reference,
            'amount' => $extensionPrice,
            'currency' => $enrollment->currency,
            'payment_proof_path' => $paymentProofPath,
            'payment_type' => 'extension',
            'extension_months' => $request->extension_months,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('message', 'Extension request submitted successfully. Please wait for approval.');
    }

    public function checkExtension(Request $request)
    {
        $enrollment = auth()->user()->enrollments()->first();
        
        if (!$enrollment) {
            return response()->json(['needs_extension' => false]);
        }

        $daysRemaining = now()->diffInDays($enrollment->access_expires_at, false);
        
        return response()->json([
            'needs_extension' => $daysRemaining <= 0,
            'days_remaining' => max(0, $daysRemaining),
            'access_expires_at' => $enrollment->access_expires_at,
            'can_extend' => $enrollment->status === 'approved'
        ]);
    }

    public function viewPaymentProof(Enrollment $enrollment)
    {
        $paymentProofPath = null;
        
        // First check enrollment's payment proof
        if ($enrollment->payment_proof_path) {
            $paymentProofPath = $enrollment->payment_proof_path;
        } 
        // Then check if any of the payments has proof
        elseif ($enrollment->payments()->exists()) {
            $payment = $enrollment->payments()->whereNotNull('payment_proof_path')->first();
            if ($payment) {
                $paymentProofPath = $payment->payment_proof_path;
            }
        }

        if (!$paymentProofPath) {
            abort(404, 'Payment proof not found');
        }

        $filePath = storage_path('app/public/' . $paymentProofPath);
        
        if (!file_exists($filePath)) {
            abort(404, 'Payment proof file not found');
        }

        $mimeType = mime_content_type($filePath);
        $fileName = basename($paymentProofPath);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ]);
    }
}

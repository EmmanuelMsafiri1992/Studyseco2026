<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentPayment;
use App\Models\PaymentMethod;
use App\Models\AccessDuration;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Services\AuditService;
use App\Notifications\PaymentApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments for admin (pending verification)
     */
    public function index()
    {
        $user = auth()->user();

        // Admin sees all payments for verification
        if ($user->role === 'admin') {
            $payments = EnrollmentPayment::with(['enrollment.user', 'verifiedBy', 'paymentMethod'])
                ->latest()
                ->paginate(20);
                
            // Transform the payment data for admin interface
            $payments->getCollection()->transform(function ($payment) {
                $payment->calculated_amount = $payment->calculated_amount; // Use accessor
                $payment->access_duration_days = $payment->access_duration_days; // Use accessor
                $payment->payment_method_name = $payment->paymentMethod->name ?? 'Unknown Method';
                $payment->payment_method_code = $payment->paymentMethod->code ?? $payment->payment_method;
                return $payment;
            });

            // Calculate stats
            $stats = [
                'total_payments' => EnrollmentPayment::count(),
                'pending_payments' => EnrollmentPayment::where('status', 'pending')->count(),
                'verified_payments' => EnrollmentPayment::where('status', 'verified')->count(),
                'rejected_payments' => EnrollmentPayment::where('status', 'rejected')->count(),
                'total_revenue' => EnrollmentPayment::where('status', 'verified')->sum('amount'),
                'pending_revenue' => EnrollmentPayment::where('status', 'pending')->sum('amount'),
            ];

            return Inertia::render('Payments/Admin/Index', [
                'payments' => $payments,
                'stats' => $stats
            ]);
        }

        // Students see their own payment history
        $enrollment = Enrollment::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        $payments = collect([]);
        
        if ($enrollment) {
            $payments = EnrollmentPayment::where('enrollment_id', $enrollment->id)
                ->with(['paymentMethod'])
                ->latest()
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->calculated_amount, // Use calculated amount for display
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'payment_method' => $payment->paymentMethod->code ?? $payment->payment_method,
                        'payment_method_name' => $payment->paymentMethod->name ?? 'Unknown',
                        'reference_number' => $payment->reference_number,
                        'access_duration_days' => $payment->access_duration_days, // Use accessor
                        'access_expires_at' => $payment->status === 'verified' ? $payment->enrollment->access_expires_at : null,
                        'created_at' => $payment->created_at,
                        'verified_at' => $payment->verified_at,
                        'admin_notes' => $payment->admin_notes,
                        'rejection_reason' => $payment->admin_notes && $payment->status === 'rejected' ? $payment->admin_notes : null,
                        'payment_type' => $payment->payment_type, // Add payment type for display
                    ];
                });
        }

        // Create pagination-like structure for the frontend
        $paginatedPayments = (object) [
            'data' => $payments,
            'total' => $payments->count(),
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $payments->count(),
            'links' => []
        ];

        $hasValidAccess = $enrollment && $enrollment->access_expires_at && $enrollment->access_expires_at->greaterThan(Carbon::now());

        // Get stats for sidebar (if admin)
        $stats = [];
        if ($user->role === 'admin') {
            try {
                $stats = [
                    'total_students' => \App\Models\User::where('role', 'student')->count(),
                    'total_teachers' => \App\Models\User::where('role', 'teacher')->count(),
                    'total_subjects' => \App\Models\Subject::where('is_active', true)->count(),
                    'pending_enrollments' => \App\Models\Enrollment::where('status', 'pending')->count(),
                ];
            } catch (\Exception $e) {
                // Fallback if queries fail
                $stats = [
                    'total_students' => 0,
                    'total_teachers' => 0,
                    'total_subjects' => 0,
                    'pending_enrollments' => 0,
                ];
            }
        }

        return Inertia::render('Payments/Index', [
            'payments' => $paginatedPayments,
            'hasValidAccess' => $hasValidAccess,
            'stats' => $stats
        ]);
    }

    /**
     * Show the payment submission form
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        // Check if user has pending payment through enrollment
        $enrollment = Enrollment::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        $hasPendingPayment = $enrollment && EnrollmentPayment::where('enrollment_id', $enrollment->id)
            ->where('status', 'pending')
            ->exists();

        // Get payment methods filtered by user's country/region
        $userRegion = 'malawi'; // Default region
        if ($enrollment && $enrollment->country) {
            $userRegion = $this->getRegionFromCountry($enrollment->country);
        } elseif ($enrollment && $enrollment->region) {
            $userRegion = $enrollment->region;
        }
        
        // Fix missing country data for old enrollments
        if ($enrollment && !$enrollment->country) {
            $this->fixMissingCountryData($enrollment);
            // Refresh the enrollment data
            $enrollment = $enrollment->fresh();
            if ($enrollment->country) {
                $userRegion = $this->getRegionFromCountry($enrollment->country);
            }
        }
        
        // Get payment methods based on user region
        // Only show international methods for regions that don't have specific methods
        if (in_array($userRegion, ['malawi', 'south_africa'])) {
            // For Malawi and South Africa, only show their specific regional methods
            $paymentMethods = PaymentMethod::active()
                ->where('region', $userRegion)
                ->get();
        } else {
            // For other countries, show international methods
            $paymentMethods = PaymentMethod::active()
                ->where('region', 'international')
                ->get();
        }
        $accessDurations = AccessDuration::active()->get();

        // Ensure we have some subjects in the database
        $this->ensureSubjectsExist();
        
        // Get available subjects
        $availableSubjects = Subject::where('is_active', true)
            ->orderBy('department')
            ->orderBy('name')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => (int) $subject->id, // Ensure it's an integer
                    'name' => $subject->name,
                    'description' => $subject->description,
                    'department' => $subject->department,
                    'grade_level' => $subject->grade_level,
                    'icon' => match($subject->name) {
                        'English' => '📚',
                        'Chichewa' => '📖',
                        'Mathematics' => '📐',
                        'Life Skills' => '🧠',
                        'Biology' => '🧬',
                        'Physical Science' => '⚗️',
                        'Chemistry' => '🧪',
                        'Physics' => '⚡',
                        'Geography' => '🗺️',
                        'History' => '🏛️',
                        'Social Studies' => '🌍',
                        'Bible Knowledge' => '✝️',
                        'Agriculture' => '🌾',
                        'Home Economics' => '🏠',
                        'Technical Drawing' => '📏',
                        'Business Studies' => '💼',
                        'French' => '🇫🇷',
                        'Computer Studies' => '💻',
                        default => '📚'
                    },
                ];
            });

        // Add user country to user object for frontend filtering
        // Get country from enrollment if user doesn't have it
        $userWithCountry = $user->toArray();
        $userCountry = 'Malawi'; // Default
        if ($enrollment) {
            $userCountry = $enrollment->country ?? 'Malawi';
        }
        $userWithCountry['country'] = $userCountry;

        // Check if this is an upgrade request
        $isUpgrade = $request->has('upgrade') && $request->get('upgrade') === 'true';

        // Debug: Log what subjects we're sending to frontend
        \Log::info('Subjects being sent to frontend:', [
            'count' => $availableSubjects->count(),
            'ids' => $availableSubjects->pluck('id')->toArray()
        ]);

        return Inertia::render('Payments/Create', [
            'auth' => ['user' => $userWithCountry],
            'hasPendingPayment' => $hasPendingPayment,
            'paymentMethods' => $paymentMethods,
            'accessDurations' => $accessDurations,
            'availableSubjects' => $availableSubjects,
            'enrollment' => $enrollment,
            'isUpgrade' => $isUpgrade,
        ]);
    }

    /**
     * Store a newly created payment submission
     */
    public function store(Request $request)
    {
        // Get valid payment method codes and access duration IDs
        $validPaymentMethodCodes = PaymentMethod::active()->pluck('code')->toArray();
        $validAccessDurationIds = AccessDuration::active()->pluck('id')->toArray();

        // Debug: Log the request data
        \Log::info('Payment request data:', $request->all());
        \Log::info('Available subjects:', Subject::where('is_active', true)->pluck('id')->toArray());
        
        // Check if this is an upgrade request
        $isUpgrade = $request->boolean('upgrade') || 
                     $request->has('selected_subjects') || 
                     $request->get('upgrade') === 'true';
        
        $rules = [
            'payment_method' => ['required', Rule::in($validPaymentMethodCodes)],
            'amount' => 'required|numeric|min:1|max:999999.99',
            'reference_number' => 'required|string|max:255',
            'proof_screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'access_duration_id' => ['required', Rule::in($validAccessDurationIds)],
        ];
        
        // Get valid subject IDs for validation
        $validSubjectIds = Subject::where('is_active', true)->pluck('id')->toArray();
        
        // Only require subject selection for upgrades
        if ($isUpgrade) {
            $rules['selected_subjects'] = 'required|array|min:1';
            $rules['selected_subjects.*'] = ['integer', Rule::in($validSubjectIds)];
        } else {
            $rules['selected_subjects'] = 'nullable|array';
            $rules['selected_subjects.*'] = ['integer', Rule::in($validSubjectIds)];
        }
        
        // Check if subjects table has data
        $subjectCount = Subject::where('is_active', true)->count();
        if ($isUpgrade && $subjectCount === 0) {
            return back()->withErrors(['error' => 'No active subjects available. Please contact support.']);
        }
        
        // Debug: Check what subjects are being sent vs what exists
        if ($request->has('selected_subjects') && is_array($request->get('selected_subjects'))) {
            $requestedSubjects = $request->get('selected_subjects');
            $validSubjects = Subject::where('is_active', true)->pluck('id')->toArray();
            $invalidSubjects = array_diff($requestedSubjects, $validSubjects);
            
            if (!empty($invalidSubjects)) {
                \Log::error('Invalid subjects requested:', [
                    'requested' => $requestedSubjects,
                    'valid_subjects' => $validSubjects,
                    'invalid' => $invalidSubjects
                ]);
            }
        }
        
        $validated = $request->validate($rules);

        $user = auth()->user();

        // Get or create enrollment
        $enrollment = Enrollment::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        if (!$enrollment) {
            return back()->withErrors(['error' => 'Enrollment not found. Please enroll first.']);
        }

        // Store selected subjects for later upgrade (but don't grant access yet)
        // Premium access should only be granted AFTER payment approval
        if (!empty($validated['selected_subjects'])) {
            // Store the pending upgrade subjects but keep trial status until payment approved
            $enrollment->update([
                'selected_subjects' => $validated['selected_subjects'],
                // DON'T change is_trial here - only change it when payment is approved
                'status' => 'pending', // Mark as pending upgrade
            ]);
        }

        // Check if user already has pending payment
        if (EnrollmentPayment::where('enrollment_id', $enrollment->id)->where('status', 'pending')->exists()) {
            return back()->withErrors(['error' => 'You already have a pending payment. Please wait for verification.']);
        }

        // Handle screenshot upload
        $paymentProofPath = null;
        if ($request->hasFile('proof_screenshot')) {
            $paymentProofPath = $request->file('proof_screenshot')->store('payment-proofs', 'public');
        }

        // Get access duration details
        $accessDuration = AccessDuration::find($validated['access_duration_id']);

        // Create enrollment payment record
        $payment = EnrollmentPayment::create([
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => PaymentMethod::where('code', $validated['payment_method'])->first()?->id,
            'reference_number' => $validated['reference_number'],
            'amount' => $validated['amount'],
            'currency' => 'MWK',
            'payment_proof_path' => $paymentProofPath,
            'extension_months' => $accessDuration->months ?? ($accessDuration->days / 30),
            'status' => 'pending'
        ]);

        // Log the payment creation
        AuditService::logPayment($payment, 'created');

        return redirect()->route('payments.index')->with('success', 'Payment submitted successfully! Your payment is being verified. You will receive access once approved.');
    }


    /**
     * Display the specified payment
     */
    public function show(EnrollmentPayment $payment)
    {
        $user = auth()->user();

        // Only admin or payment owner can view
        if ($user->role !== 'admin' && $payment->enrollment->user_id !== $user->id && $payment->enrollment->email !== $user->email) {
            abort(403);
        }

        $payment->load(['enrollment.user', 'verifiedBy', 'paymentMethod']);
        
        // Add calculated fields for display
        $payment->calculated_amount = $payment->calculated_amount;
        $payment->access_duration_days = $payment->access_duration_days;

        return Inertia::render('Payments/Show', [
            'payment' => $payment
        ]);
    }

    /**
     * Verify/Approve a payment (admin only)
     */
    public function verify(Request $request, EnrollmentPayment $payment)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can verify payments.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
            'rejection_reason' => 'required_if:action,reject|string|max:500'
        ]);

        if ($request->action === 'approve') {
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'admin_notes' => $request->notes
            ]);

            // Update enrollment access
            $enrollment = $payment->enrollment;
            if ($enrollment) {
                // Check if this is a subject increase payment
                if ($payment->payment_type === 'subject_increase' && $payment->additional_data) {
                    $additionalData = json_decode($payment->additional_data, true);
                    $additionalSubjects = $additionalData['additional_subjects'] ?? [];
                    
                    if (!empty($additionalSubjects)) {
                        $currentSubjects = $enrollment->selected_subjects ?: [];
                        $newSubjects = array_merge($currentSubjects, $additionalSubjects);
                        $newSubjects = array_unique($newSubjects); // Remove duplicates
                        
                        // Extend access time for subject increase
                        $currentExpiry = $enrollment->access_expires_at;
                        $newExpiry = $currentExpiry && $currentExpiry->greaterThan(now()) 
                            ? $currentExpiry->addDays($payment->access_duration_days ?? 30)
                            : now()->addDays($payment->access_duration_days ?? 30);

                        $enrollment->update([
                            'selected_subjects' => $newSubjects,
                            'subject_count' => count($newSubjects),
                            'total_amount' => $enrollment->total_amount + $payment->amount,
                            'access_expires_at' => $newExpiry,
                            'status' => 'approved',
                            'is_trial' => false,
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                        
                        // Send subject increase notification
                        $enrollment->user->notify(new \App\Notifications\SubjectsAdded($enrollment, $additionalSubjects));
                    }
                } else {
                    // Regular payment - grant premium access and approve enrollment
                    // Calculate access duration based on extension_months
                    $accessDays = $payment->extension_months ? ($payment->extension_months * 30) : 30;

                    $enrollment->update([
                        'status' => 'approved',
                        'is_trial' => false, // NOW grant premium access
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'access_expires_at' => now()->addDays($accessDays)
                    ]);

                    // Send congratulatory notification
                    if ($enrollment->user) {
                        $enrollment->user->notify(new PaymentApproved($enrollment, $payment));
                    }
                }
                
                // Log access granted
                AuditService::logAccess($enrollment->user, true);
            }

            // Log payment verification
            AuditService::logPayment($payment, 'approved');

            return redirect()->back()->with('success', 'Payment verified and access granted successfully.');
        } else {
            $payment->update([
                'status' => 'rejected',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'admin_notes' => $request->rejection_reason
            ]);

            // Log payment rejection
            AuditService::logPayment($payment, 'rejected');

            return redirect()->back()->with('success', 'Payment rejected successfully.');
        }
    }

    /**
     * Get payment statistics for admin dashboard
     */
    public function statistics()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can view payment statistics.');
        }

        $stats = [
            'pending_count' => EnrollmentPayment::where('status', 'pending')->count(),
            'approved_today' => EnrollmentPayment::where('status', 'verified')->whereDate('verified_at', today())->count(),
            'total_revenue' => EnrollmentPayment::where('status', 'verified')->sum('amount'),
            'pending_revenue' => EnrollmentPayment::where('status', 'pending')->sum('amount'),
            'active_subscriptions' => Enrollment::where('status', 'approved')
                ->where('access_expires_at', '>', Carbon::now())
                ->count()
        ];

        return response()->json($stats);
    }

    /**
     * Check if user has valid access
     */
    public function checkAccess()
    {
        $user = auth()->user();
        $enrollment = Enrollment::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        
        $hasAccess = $enrollment && $enrollment->access_expires_at && $enrollment->access_expires_at->greaterThan(Carbon::now());

        return response()->json([
            'hasAccess' => $hasAccess,
            'expiresAt' => $enrollment?->access_expires_at,
            'daysRemaining' => $enrollment?->access_days_remaining ?? 0,
            'isTrialExpired' => $enrollment?->is_trial_expired ?? false
        ]);
    }
    
    /**
     * Ensure basic subjects exist in the database
     */
    private function ensureSubjectsExist()
    {
        // Check if subjects table is empty
        if (Subject::count() === 0) {
            $subjects = [
                ['name' => 'English', 'code' => 'ENG', 'department' => 'Languages', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Chichewa', 'code' => 'CHI', 'department' => 'Languages', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Mathematics', 'code' => 'MATH', 'department' => 'Sciences', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Life Skills', 'code' => 'LIFE', 'department' => 'General', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Biology', 'code' => 'BIO', 'department' => 'Sciences', 'grade_level' => 'Form 3-4', 'is_active' => true],
                ['name' => 'Physical Science', 'code' => 'PHYS', 'department' => 'Sciences', 'grade_level' => 'Form 1-2', 'is_active' => true],
                ['name' => 'Chemistry', 'code' => 'CHEM', 'department' => 'Sciences', 'grade_level' => 'Form 3-4', 'is_active' => true],
                ['name' => 'Physics', 'code' => 'PHY', 'department' => 'Sciences', 'grade_level' => 'Form 3-4', 'is_active' => true],
                ['name' => 'Geography', 'code' => 'GEO', 'department' => 'Humanities', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'History', 'code' => 'HIST', 'department' => 'Humanities', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Social Studies', 'code' => 'SOC', 'department' => 'Humanities', 'grade_level' => 'Form 1-2', 'is_active' => true],
                ['name' => 'Bible Knowledge', 'code' => 'BK', 'department' => 'Religious', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Agriculture', 'code' => 'AGR', 'department' => 'Practical', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Home Economics', 'code' => 'HE', 'department' => 'Practical', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Technical Drawing', 'code' => 'TD', 'department' => 'Technical', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Business Studies', 'code' => 'BUS', 'department' => 'Commercial', 'grade_level' => 'Form 1-4', 'is_active' => true],
                ['name' => 'Computer Studies', 'code' => 'CS', 'department' => 'Technical', 'grade_level' => 'Form 1-4', 'is_active' => true],
            ];

            foreach ($subjects as $subject) {
                Subject::create($subject);
            }
        }
    }

    public function downloadReceipt(EnrollmentPayment $payment)
    {
        $user = auth()->user();
        
        // Check if user can download this receipt
        if ($payment->enrollment->user_id !== $user->id && $payment->enrollment->email !== $user->email && !in_array($user->role, ['admin'])) {
            abort(403, 'You do not have permission to download this receipt.');
        }

        // Only allow download for verified payments
        if ($payment->status !== 'verified') {
            abort(404, 'Receipt not available for unverified payments.');
        }

        // Generate printable receipt HTML that opens in new window for PDF printing
        $receiptHtml = $this->generatePrintableReceiptHtml($payment);
        
        return response($receiptHtml, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    private function generateReceiptPdf(EnrollmentPayment $payment)
    {
        $enrollment = $payment->enrollment;
        $user = $enrollment->user;
        
        // Create a simple PDF using TCPDF or HTML to PDF conversion
        // For now, we'll create a simple HTML-to-PDF approach using DomPDF or similar
        // Since we don't want to add complex dependencies, let's create a styled HTML that browsers can print as PDF
        
        $receiptHtml = $this->generatePrintableReceiptHtml($payment);
        
        // For now, return HTML that can be printed as PDF by browser
        // In production, you might want to use a proper PDF library like DomPDF
        return $receiptHtml;
    }

    private function generatePrintableReceiptHtml(EnrollmentPayment $payment)
    {
        $enrollment = $payment->enrollment;
        $user = $enrollment->user;
        
        return '<!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt #' . $payment->id . '</title>
            <meta charset="utf-8">
            <style>
                @media print { 
                    body { margin: 0; }
                    .no-print { display: none; }
                }
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px; 
                    color: #333;
                    line-height: 1.6;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 40px; 
                    padding: 20px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 10px;
                }
                .header h1 { margin: 0; font-size: 28px; }
                .header h2 { margin: 5px 0 0 0; font-size: 16px; opacity: 0.9; }
                .receipt-id { 
                    background: rgba(255,255,255,0.2); 
                    padding: 10px; 
                    border-radius: 5px; 
                    margin-top: 15px;
                    font-weight: bold;
                }
                .content { 
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .row { 
                    display: flex;
                    justify-content: space-between;
                    margin: 15px 0; 
                    padding: 10px 0;
                    border-bottom: 1px solid #eee;
                }
                .row:last-child { border-bottom: none; }
                .label { 
                    font-weight: bold; 
                    color: #555;
                    width: 40%;
                }
                .value { 
                    color: #333;
                    width: 60%;
                    text-align: right;
                }
                .amount { 
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                    margin: 20px 0;
                    border-left: 4px solid #28a745;
                }
                .amount .currency { font-size: 24px; font-weight: bold; color: #28a745; }
                .status { 
                    color: #28a745; 
                    font-weight: bold; 
                    text-transform: uppercase;
                    padding: 5px 10px;
                    background: #d4edda;
                    border-radius: 5px;
                }
                .footer { 
                    margin-top: 40px; 
                    text-align: center; 
                    color: #666;
                    font-size: 14px;
                    border-top: 2px solid #eee;
                    padding-top: 20px;
                }
                .print-btn {
                    background: #007bff;
                    color: white;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    margin: 20px auto;
                    display: block;
                }
                .print-btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>📄 Payment Receipt</h1>
                <h2>StudySeco Learning Platform</h2>
                <div class="receipt-id">Receipt #' . $payment->id . '</div>
            </div>
            
            <div class="content">
                <div class="row">
                    <span class="label">Student Name:</span>
                    <span class="value">' . htmlspecialchars($user ? $user->name : $enrollment->name) . '</span>
                </div>
                <div class="row">
                    <span class="label">Email Address:</span>
                    <span class="value">' . htmlspecialchars($enrollment->email) . '</span>
                </div>
                <div class="row">
                    <span class="label">Payment Method:</span>
                    <span class="value">' . htmlspecialchars($payment->paymentMethod->name ?? 'Unknown Method') . '</span>
                </div>
                <div class="row">
                    <span class="label">Reference Number:</span>
                    <span class="value">' . htmlspecialchars($payment->reference_number) . '</span>
                </div>
                
                <div class="amount">
                    <div class="currency">' . $payment->currency . ' ' . number_format($payment->calculated_amount, 2) . '</div>
                    <div>Total Amount Paid</div>
                </div>
                
                <div class="row">
                    <span class="label">Payment Type:</span>
                    <span class="value">' . ($payment->payment_type === 'subject_increase' ? 'Subject Addition' : 'Access Payment') . '</span>
                </div>
                <div class="row">
                    <span class="label">Access Duration:</span>
                    <span class="value">' . $payment->access_duration_days . ' days</span>
                </div>
                <div class="row">
                    <span class="label">Payment Date:</span>
                    <span class="value">' . $payment->created_at->format('M j, Y \a\t g:i A') . '</span>
                </div>
                <div class="row">
                    <span class="label">Verified Date:</span>
                    <span class="value">' . $payment->verified_at->format('M j, Y \a\t g:i A') . '</span>
                </div>
                <div class="row">
                    <span class="label">Payment Status:</span>
                    <span class="value"><span class="status">✓ Verified</span></span>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>This is an official receipt for payment verification.</strong></p>
                <p>Generated on ' . now()->format('M j, Y \a\t g:i A') . '</p>
                <p>StudySeco Learning Platform | Email: support@studyseco.com</p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 30px;">
                <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
                <button class="print-btn" onclick="downloadAsPDF()" style="background: #28a745; margin-left: 10px;">📄 Save as PDF</button>
                <button class="print-btn" onclick="window.close()" style="background: #6c757d; margin-left: 10px;">✕ Close</button>
            </div>
            
            <script>
                // Print functionality
                function downloadAsPDF() {
                    // For modern browsers with print-to-PDF support
                    window.print();
                }
                
                // Auto-focus and provide guidance
                window.onload = function() {
                    console.log(\"Receipt loaded successfully\");
                    // You can print immediately by uncommenting the next line
                    // window.print();
                }
                
                // Handle print dialog
                window.addEventListener(\"beforeprint\", function() {
                    document.title = \"StudySeco Receipt #' . $payment->id . '\";
                });
            </script>
        </body>
        </html>';
    }

    private function generateReceiptHtml($payment)
    {
        $enrollment = $payment->enrollment;
        $user = $enrollment->user;
        
        return '<!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 40px; }
                .content { margin: 20px 0; }
                .row { margin: 10px 0; }
                .label { font-weight: bold; display: inline-block; width: 150px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Payment Receipt</h1>
                <h2>StudySeco Learning Platform</h2>
            </div>
            <div class="content">
                <div class="row"><span class="label">Receipt ID:</span> #' . $payment->id . '</div>
                <div class="row"><span class="label">Student Name:</span> ' . ($user ? $user->name : $enrollment->name) . '</div>
                <div class="row"><span class="label">Email:</span> ' . $enrollment->email . '</div>
                <div class="row"><span class="label">Payment Method:</span> ' . $payment->paymentMethod->name . '</div>
                <div class="row"><span class="label">Amount:</span> ' . $payment->currency . ' ' . number_format($payment->amount, 2) . '</div>
                <div class="row"><span class="label">Reference:</span> ' . $payment->reference_number . '</div>
                <div class="row"><span class="label">Access Duration:</span> ' . $payment->access_duration_days . ' days</div>
                <div class="row"><span class="label">Payment Date:</span> ' . $payment->created_at->format('M j, Y') . '</div>
                <div class="row"><span class="label">Verified Date:</span> ' . $payment->verified_at->format('M j, Y') . '</div>
                <div class="row"><span class="label">Status:</span> VERIFIED ✓</div>
            </div>
            <div style="margin-top: 40px; text-align: center; color: #666;">
                <p>This is an official receipt for payment verification.</p>
                <p>Generated on ' . now()->format('M j, Y \a\t g:i A') . '</p>
            </div>
        </body>
        </html>';
    }

    private function getRegionFromCountry($country)
    {
        $country = strtolower(trim($country));
        
        // African countries with specific payment methods
        $countryRegionMap = [
            // Southern Africa
            'malawi' => 'malawi',
            'mw' => 'malawi', 
            'mwi' => 'malawi',
            
            'south africa' => 'south_africa',
            'south_africa' => 'south_africa',
            'za' => 'south_africa',
            'zaf' => 'south_africa',
            
            'botswana' => 'botswana',
            'bw' => 'botswana',
            'bwa' => 'botswana',
            
            'zimbabwe' => 'zimbabwe',
            'zw' => 'zimbabwe',
            'zwe' => 'zimbabwe',
            
            'zambia' => 'zambia',
            'zm' => 'zambia',
            'zmb' => 'zambia',
            
            'namibia' => 'namibia',
            'na' => 'namibia',
            'nam' => 'namibia',
            
            'lesotho' => 'lesotho',
            'ls' => 'lesotho',
            'lso' => 'lesotho',
            
            'eswatini' => 'eswatini',
            'swaziland' => 'eswatini',
            'sz' => 'eswatini',
            'swz' => 'eswatini',
            
            'mozambique' => 'mozambique',
            'mz' => 'mozambique',
            'moz' => 'mozambique',
            
            // East Africa
            'kenya' => 'east_africa',
            'ke' => 'east_africa',
            'ken' => 'east_africa',
            
            'tanzania' => 'east_africa',
            'tz' => 'east_africa',
            'tza' => 'east_africa',
            
            'uganda' => 'east_africa',
            'ug' => 'east_africa',
            'uga' => 'east_africa',
            
            'rwanda' => 'east_africa',
            'rw' => 'east_africa',
            'rwa' => 'east_africa',
            
            'ethiopia' => 'east_africa',
            'et' => 'east_africa',
            'eth' => 'east_africa',
            
            // West Africa
            'nigeria' => 'west_africa',
            'ng' => 'west_africa',
            'nga' => 'west_africa',
            
            'ghana' => 'west_africa',
            'gh' => 'west_africa',
            'gha' => 'west_africa',
            
            'senegal' => 'west_africa',
            'sn' => 'west_africa',
            'sen' => 'west_africa',
            
            'ivory coast' => 'west_africa',
            'cote divoire' => 'west_africa',
            'ci' => 'west_africa',
            'civ' => 'west_africa',
            
            // Other African countries default to their respective regions
            // For countries not specifically mapped, we'll use a general approach
        ];
        
        if (isset($countryRegionMap[$country])) {
            return $countryRegionMap[$country];
        }
        
        // For countries not in our specific map, use broader regional defaults
        // You can expand this as needed
        return 'international'; // Default to international payment methods
    }

    private function fixMissingCountryData($enrollment)
    {
        // Try to determine country from various sources
        $country = null;
        $region = null;

        // Method 1: Check if region exists and convert it to country
        if ($enrollment->region) {
            $region = $enrollment->region;
            $country = ucfirst(str_replace('_', ' ', $enrollment->region));
        }
        // Method 2: Check payment methods used to infer region
        elseif ($enrollment->payment_method) {
            $paymentMethod = strtolower($enrollment->payment_method);
            
            // Malawi payment methods
            if (in_array($paymentMethod, ['tnm_mpamba', 'airtel_money', 'national_bank'])) {
                $region = 'malawi';
                $country = 'Malawi';
            }
            // South Africa payment methods
            elseif (in_array($paymentMethod, ['fnb', 'capitec', 'standard_bank', 'nedbank', 'absa'])) {
                $region = 'south_africa';
                $country = 'South Africa';
            }
            // East Africa payment methods
            elseif (in_array($paymentMethod, ['m_pesa', 'mpesa', 't_kash', 'tigo_pesa', 'mtn_mobile_money'])) {
                $region = 'east_africa';
                $country = 'Kenya'; // Default to Kenya for M-Pesa
            }
            // West Africa payment methods
            elseif (in_array($paymentMethod, ['flutterwave', 'paystack', 'vodafone_cash', 'orange_money'])) {
                $region = 'west_africa';
                $country = 'Nigeria'; // Default to Nigeria for Flutterwave/Paystack
            }
            // Botswana payment methods
            elseif (in_array($paymentMethod, ['orange_money_bw', 'mascom_myzaka', 'fnb_botswana'])) {
                $region = 'botswana';
                $country = 'Botswana';
            }
            // Zimbabwe payment methods
            elseif (in_array($paymentMethod, ['ecocash', 'onemoney'])) {
                $region = 'zimbabwe';
                $country = 'Zimbabwe';
            }
            // Zambia payment methods
            elseif (in_array($paymentMethod, ['mtn_momo', 'zamtel_kwacha'])) {
                $region = 'zambia';
                $country = 'Zambia';
            }
        }
        // Method 3: Check currency to infer region
        elseif ($enrollment->currency) {
            switch ($enrollment->currency) {
                case 'MWK':
                    $region = 'malawi';
                    $country = 'Malawi';
                    break;
                case 'ZAR':
                    $region = 'south_africa';
                    $country = 'South Africa';
                    break;
                case 'BWP':
                    $region = 'botswana';
                    $country = 'Botswana';
                    break;
                case 'ZMW':
                    $region = 'zambia';
                    $country = 'Zambia';
                    break;
                case 'KES':
                    $region = 'east_africa';
                    $country = 'Kenya';
                    break;
                case 'TZS':
                    $region = 'east_africa';
                    $country = 'Tanzania';
                    break;
                case 'UGX':
                    $region = 'east_africa';
                    $country = 'Uganda';
                    break;
                case 'NGN':
                    $region = 'west_africa';
                    $country = 'Nigeria';
                    break;
                case 'GHS':
                    $region = 'west_africa';
                    $country = 'Ghana';
                    break;
                case 'USD':
                    // Could be Zimbabwe, other African countries, or International
                    $region = 'international';
                    $country = 'International';
                    break;
                case 'EUR':
                    $region = 'international';
                    $country = 'International';
                    break;
                default:
                    $region = 'international';
                    $country = 'International';
            }
        }
        // Method 4: Default to Malawi
        else {
            $region = 'malawi';
            $country = 'Malawi';
        }

        // Update the enrollment with the inferred data
        $enrollment->update([
            'country' => $country,
            'region' => $region
        ]);
    }

    /**
     * View payment proof screenshot (admin only)
     */
    public function viewPaymentProof(EnrollmentPayment $payment)
    {
        // Ensure only admin can access
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Check if payment proof exists
        if (!$payment->payment_proof_path) {
            abort(404, 'Payment proof not found');
        }

        // Get the full path to the file
        $filePath = storage_path('app/public/' . $payment->payment_proof_path);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'Payment proof file not found');
        }

        // Determine content type based on file extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentType = match(strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg'
        };

        // Return the file with appropriate headers
        return response()->file($filePath, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
}

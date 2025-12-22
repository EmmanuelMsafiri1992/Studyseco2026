<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\AccessDuration;
use App\Models\EnrollmentPayment;
use App\Models\PaymentMethod;

class EnrollmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $availableSubjects = Subject::where('is_active', true)
            ->orderBy('department')
            ->orderBy('name')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
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

        $accessDurations = AccessDuration::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Student/Enrollment/Index', [
            'enrollment' => $enrollment,
            'availableSubjects' => $availableSubjects,
            'accessDurations' => $accessDurations,
            'enrollmentStatus' => $enrollment ? [
                'status' => $enrollment->status,
                'is_trial' => $enrollment->is_trial,
                'subjects_count' => count($enrollment->selected_subjects ?: []),
                'access_expires_at' => $enrollment->access_expires_at,
                'access_days_remaining' => $enrollment->access_days_remaining,
            ] : null
        ]);
    }

    public function show()
    {
        $user = auth()->user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.enrollment.index')
                ->with('error', 'No enrollment found. Please enroll first.');
        }

        return Inertia::render('Student/Enrollment/Show', [
            'enrollment' => $enrollment,
            'enrollmentDetails' => [
                'id' => $enrollment->id,
                'status' => $enrollment->status,
                'is_trial' => $enrollment->is_trial,
                'subjects' => $enrollment->selected_subjects ?: [],
                'created_at' => $enrollment->created_at,
                'access_expires_at' => $enrollment->access_expires_at,
                'access_days_remaining' => $enrollment->access_days_remaining,
                'is_access_expired' => $enrollment->is_access_expired,
            ]
        ]);
    }

    public function increaseSubjects()
    {
        $user = auth()->user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.enrollment.index')
                ->with('error', 'No enrollment found. Please enroll first.');
        }

        if ($enrollment->is_trial) {
            return redirect()->route('student.enrollment.index')
                ->with('error', 'Please upgrade to premium first before adding more subjects.');
        }

        if ($enrollment->status !== 'approved') {
            return redirect()->route('student.enrollment.index')
                ->with('error', 'Your enrollment must be approved before adding more subjects.');
        }

        $currentSubjects = $enrollment->selected_subjects ?: [];
        $allSubjects = Subject::where('is_active', true)
            ->orderBy('department')
            ->orderBy('name')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
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

        // Filter out subjects already enrolled in
        $availableSubjects = $allSubjects->filter(function ($subject) use ($currentSubjects) {
            return !in_array($subject['id'], $currentSubjects);
        });

        // Get payment methods for the enrollment's region
        $paymentMethods = PaymentMethod::active()
            ->forRegion($enrollment->region)
            ->get()
            ->map(function ($method) {
                return [
                    'code' => $method->code,
                    'name' => $method->name,
                    'icon' => $method->icon,
                    'color' => $method->color,
                    'description' => $method->description,
                    'account_details' => $method->account_details,
                ];
            });

        // Ensure price_per_subject is set based on region if null
        $pricePerSubject = $enrollment->price_per_subject ?? \App\Models\Enrollment::getPricePerSubject($enrollment->region);

        // Debug logging
        \Log::info('Subject increase page data:', [
            'enrollment_region' => $enrollment->region,
            'price_per_subject' => $pricePerSubject,
            'payment_methods_count' => $paymentMethods->count(),
            'payment_methods' => $paymentMethods->toArray(),
        ]);

        return Inertia::render('Student/Enrollment/IncreaseSubjects', [
            'enrollment' => $enrollment,
            'currentSubjects' => $allSubjects->whereIn('id', $currentSubjects)->values(),
            'availableSubjects' => $availableSubjects->values(),
            'pricePerSubject' => $pricePerSubject,
            'currency' => $enrollment->currency,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function storeSubjectIncrease(Request $request)
    {
        $user = auth()->user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$enrollment || $enrollment->is_trial || $enrollment->status !== 'approved') {
            return back()->with('error', 'Invalid enrollment status for adding subjects.');
        }

        $request->validate([
            'additional_subjects' => 'required|array|min:1',
            'additional_subjects.*' => 'exists:subjects,id',
            'payment_method' => 'required|string',
            'reference_number' => 'required_unless:payment_method,trial|string|max:255',
            'proof_screenshot' => 'required_unless:payment_method,trial|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $additionalSubjects = $request->additional_subjects;
        $currentSubjects = $enrollment->selected_subjects ?: [];
        
        // Check for duplicates
        $duplicates = array_intersect($currentSubjects, $additionalSubjects);
        if (!empty($duplicates)) {
            return back()->with('error', 'Some subjects are already in your enrollment.');
        }

        // Calculate additional cost
        $additionalCost = count($additionalSubjects) * $enrollment->price_per_subject;

        // Handle proof screenshot upload
        $proofPath = null;
        if ($request->hasFile('proof_screenshot')) {
            $proofPath = $request->file('proof_screenshot')->store('payment-proofs', 'public');
        }

        // Create payment record for additional subjects
        $payment = EnrollmentPayment::create([
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => \App\Models\PaymentMethod::where('code', $request->payment_method)->first()?->id,
            'reference_number' => $request->reference_number,
            'amount' => $additionalCost,
            'currency' => $enrollment->currency,
            'payment_proof_path' => $proofPath,
            'status' => 'pending',
            'payment_type' => 'subject_increase',
            'additional_data' => json_encode([
                'additional_subjects' => $additionalSubjects,
                'subject_count' => count($additionalSubjects),
                'previous_subject_count' => count($currentSubjects),
                'new_total_subjects' => count($currentSubjects) + count($additionalSubjects)
            ])
        ]);

        return redirect()->route('payments.index')->with('success', 
            'Subject increase request submitted! Your payment is being verified. New subjects will be added once approved.');
    }
}
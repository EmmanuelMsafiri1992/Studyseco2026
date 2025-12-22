<?php

namespace App\Http\Controllers;

use App\Models\CancellationRequest;
use App\Models\Enrollment;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CancellationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $cancellations = CancellationRequest::where('user_id', $user->id)
            ->with(['enrollment', 'processedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $eligibleEnrollments = Enrollment::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDoesntHave('cancellationRequests', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->get();

        return Inertia::render('Cancellation/Index', [
            'cancellations' => $cancellations,
            'eligibleEnrollments' => $eligibleEnrollments
        ]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $enrollmentId = $request->get('enrollment_id');
        
        $enrollment = null;
        if ($enrollmentId) {
            $enrollment = Enrollment::where('id', $enrollmentId)
                ->where('user_id', $user->id)
                ->first();
        }

        $eligibleEnrollments = Enrollment::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDoesntHave('cancellationRequests', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->get()
            ->map(function ($enroll) {
                $daysRemaining = 14 - now()->diffInDays($enroll->created_at);
                return [
                    'id' => $enroll->id,
                    'enrollment_number' => $enroll->enrollment_number,
                    'created_at' => $enroll->created_at,
                    'total_amount' => $enroll->total_amount,
                    'currency' => $enroll->currency,
                    'days_remaining_for_cancellation' => max(0, $daysRemaining),
                    'eligible_for_refund' => $daysRemaining > 0,
                    'estimated_refund' => $daysRemaining > 0 ? $this->calculateRefund($enroll) : 0
                ];
            });

        return Inertia::render('Cancellation/Create', [
            'enrollment' => $enrollment,
            'eligibleEnrollments' => $eligibleEnrollments,
            'reasons' => $this->getCancellationReasons()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'reason' => 'required|string|min:10|max:1000',
            'additional_details' => 'nullable|string|max:2000'
        ]);

        $user = auth()->user();
        $enrollment = Enrollment::where('id', $request->enrollment_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if already has pending cancellation
        $existingRequest = CancellationRequest::where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->back()->withErrors(['error' => 'You already have a pending cancellation request for this enrollment.']);
        }

        // Create cancellation request
        $cancellationRequest = CancellationRequest::create([
            'user_id' => $user->id,
            'enrollment_id' => $enrollment->id,
            'cancellation_type' => 'enrollment',
            'reason' => $request->reason . ($request->additional_details ? "\n\nAdditional Details: " . $request->additional_details : ''),
            'requested_at' => now(),
            'cancellation_deadline' => $enrollment->created_at->addDays(14),
        ]);

        // Calculate refund amount
        $refundAmount = $cancellationRequest->calculateRefundAmount();
        $cancellationRequest->update([
            'refund_amount' => $refundAmount,
            'original_payment_data' => [
                'enrollment_amount' => $enrollment->total_amount,
                'currency' => $enrollment->currency,
                'payments' => $enrollment->payments()->where('status', 'verified')->get()->toArray()
            ]
        ]);

        // Log the cancellation request
        AuditService::log('cancellation_requested', $cancellationRequest, null, [
            'enrollment_id' => $enrollment->id,
            'refund_amount' => $refundAmount,
            'within_policy' => $cancellationRequest->isWithinCancellationPolicy()
        ], "Cancellation request submitted for enrollment {$enrollment->enrollment_number}");

        return redirect()->route('cancellation.index')
            ->with('success', 'Cancellation request submitted successfully. You will be notified once it is reviewed.');
    }

    public function show(CancellationRequest $cancellationRequest)
    {
        // Ensure user can only view their own requests
        if ($cancellationRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $cancellationRequest->load(['enrollment', 'processedBy']);

        return Inertia::render('Cancellation/Show', [
            'cancellationRequest' => $cancellationRequest
        ]);
    }

    // Admin methods
    public function adminIndex()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        $cancellations = CancellationRequest::with(['user', 'enrollment', 'processedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'pending' => CancellationRequest::where('status', 'pending')->count(),
            'approved_today' => CancellationRequest::where('status', 'approved')
                ->whereDate('processed_at', today())->count(),
            'total_refunds' => CancellationRequest::where('refund_status', 'completed')->sum('refund_amount'),
            'within_policy' => CancellationRequest::whereHas('enrollment', function ($query) {
                $query->where('created_at', '>=', now()->subDays(14));
            })->count()
        ];

        return Inertia::render('Admin/Cancellation/Index', [
            'cancellations' => $cancellations,
            'stats' => $stats
        ]);
    }

    public function adminProcess(Request $request, CancellationRequest $cancellationRequest)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $cancellationRequest->update([
            'status' => $request->status,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
            'admin_notes' => $request->admin_notes
        ]);

        if ($request->status === 'approved') {
            // If approved and within policy, mark refund as pending
            if ($cancellationRequest->isWithinCancellationPolicy() && $cancellationRequest->refund_amount > 0) {
                $cancellationRequest->update([
                    'refund_status' => 'pending'
                ]);
            }

            // Cancel the enrollment
            if ($cancellationRequest->enrollment) {
                $cancellationRequest->enrollment->update([
                    'status' => 'cancelled'
                ]);
            }

            $message = 'Cancellation request approved successfully.';
        } else {
            $message = 'Cancellation request rejected.';
        }

        // Log the processing
        AuditService::log('cancellation_processed', $cancellationRequest, null, [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'refund_amount' => $cancellationRequest->refund_amount
        ], "Cancellation request {$request->status} by admin");

        return redirect()->back()->with('success', $message);
    }

    private function calculateRefund(Enrollment $enrollment): float
    {
        $daysFromEnrollment = now()->diffInDays($enrollment->created_at);
        
        if ($daysFromEnrollment > 14) {
            return 0.0;
        }

        // Get total verified payments
        return $enrollment->payments()
            ->where('status', 'verified')
            ->sum('amount');
    }

    private function getCancellationReasons(): array
    {
        return [
            'financial_hardship' => 'Financial hardship',
            'course_not_suitable' => 'Subjects not suitable for my needs',
            'technical_issues' => 'Technical issues with platform',
            'quality_concerns' => 'Quality concerns',
            'time_constraints' => 'Time constraints',
            'family_circumstances' => 'Family circumstances',
            'health_reasons' => 'Health reasons',
            'relocation' => 'Relocation',
            'other' => 'Other (please specify)'
        ];
    }
}
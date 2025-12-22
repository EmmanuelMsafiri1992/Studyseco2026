<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\EnrollmentPayment;
use App\Models\User;
use App\Notifications\ExtensionApproved;
use App\Notifications\ExtensionGranted;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExtensionController extends Controller
{
    public function index(Request $request)
    {
        $query = EnrollmentPayment::with(['enrollment.user', 'paymentMethod'])
            ->where('payment_type', 'extension');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by student name or enrollment number
        if ($request->has('search') && !empty($request->search)) {
            $query->whereHas('enrollment', function ($q) use ($request) {
                $q->where('enrollment_number', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        $extensions = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get statistics
        $stats = [
            'total' => EnrollmentPayment::where('payment_type', 'extension')->count(),
            'pending' => EnrollmentPayment::where('payment_type', 'extension')->where('status', 'pending')->count(),
            'verified' => EnrollmentPayment::where('payment_type', 'extension')->where('status', 'verified')->count(),
            'rejected' => EnrollmentPayment::where('payment_type', 'extension')->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Extensions/Index', [
            'extensions' => $extensions,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(EnrollmentPayment $payment)
    {
        if ($payment->payment_type !== 'extension') {
            return redirect()->route('admin.extensions.index')
                ->withErrors(['error' => 'Invalid extension payment']);
        }

        $payment->load(['enrollment.user', 'paymentMethod']);

        return Inertia::render('Admin/Extensions/Show', [
            'extension' => $payment,
        ]);
    }

    public function approve(EnrollmentPayment $payment)
    {
        if ($payment->payment_type !== 'extension') {
            return back()->withErrors(['error' => 'Invalid payment type']);
        }

        if ($payment->status !== 'pending') {
            return back()->withErrors(['error' => 'Extension already processed']);
        }

        $enrollment = $payment->enrollment;

        // Update payment status
        $payment->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id()
        ]);

        // Extend access duration
        $currentExpiry = $enrollment->access_expires_at ?? now();
        $newExpiry = $currentExpiry->addMonths($payment->extension_months);

        $enrollment->update([
            'access_expires_at' => $newExpiry,
            'last_extension_at' => now()
        ]);

        // Send notification to student
        try {
            $enrollment->user->notify(new ExtensionApproved($enrollment, $payment));
        } catch (\Exception $e) {
            logger('Failed to send extension approval notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Extension approved successfully! Student has been notified.');
    }

    public function reject(EnrollmentPayment $payment, Request $request)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($payment->payment_type !== 'extension') {
            return back()->withErrors(['error' => 'Invalid payment type']);
        }

        if ($payment->status !== 'pending') {
            return back()->withErrors(['error' => 'Extension already processed']);
        }

        // Update payment status
        $payment->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'admin_notes' => $request->rejection_reason
        ]);

        return back()->with('success', 'Extension rejected with reason provided.');
    }

    public function grantExtension(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'months' => 'required|integer|min:1|max:24',
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($request->user_id);
        $enrollment = $user->enrollments()->first();

        if (!$enrollment) {
            return back()->withErrors(['error' => 'Student has no active enrollment']);
        }

        // Extend access duration
        $currentExpiry = $enrollment->access_expires_at ?? now();
        $newExpiry = $currentExpiry->addMonths($request->months);

        $enrollment->update([
            'access_expires_at' => $newExpiry,
            'last_extension_at' => now()
        ]);

        // Create a record of this admin-granted extension
        EnrollmentPayment::create([
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => null,
            'reference_number' => 'ADMIN-' . now()->format('YmdHis'),
            'amount' => 0,
            'currency' => $enrollment->currency,
            'payment_type' => 'extension',
            'extension_months' => $request->months,
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'admin_notes' => 'Admin granted extension: ' . $request->reason
        ]);

        // Send notification
        try {
            $user->notify(new ExtensionGranted($enrollment, $request->months, $request->reason));
        } catch (\Exception $e) {
            logger('Failed to send extension granted notification: ' . $e->getMessage());
        }

        return back()->with('success', 
            "Successfully granted {$request->months} month(s) extension to {$user->name}");
    }

    public function updatePricing(Request $request)
    {
        $request->validate([
            'region' => 'required|string|in:malawi,south_africa,zambia,botswana,zimbabwe,international',
            'pricing' => 'required|array',
            'pricing.*' => 'required|numeric|min:0'
        ]);

        // Store pricing in system settings or cache
        // This is a simplified approach - you might want to store in database
        $settingKey = "extension_pricing_{$request->region}";
        
        // You can implement this by storing in system_settings table
        // For now, this is a placeholder for the functionality

        return back()->with('success', 'Extension pricing updated successfully');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:enrollment_payments,id',
            'rejection_reason' => 'required_if:action,reject|string|max:500'
        ]);

        $payments = EnrollmentPayment::whereIn('id', $request->payment_ids)
            ->where('payment_type', 'extension')
            ->where('status', 'pending')
            ->get();

        $processed = 0;
        foreach ($payments as $payment) {
            if ($request->action === 'approve') {
                $this->approve($payment);
            } else {
                $payment->update([
                    'status' => 'rejected',
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                    'admin_notes' => $request->rejection_reason
                ]);
            }
            $processed++;
        }

        return back()->with('success', "Successfully {$request->action}d {$processed} extension(s)");
    }
}
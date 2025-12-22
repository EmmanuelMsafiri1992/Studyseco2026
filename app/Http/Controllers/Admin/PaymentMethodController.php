<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        $paymentMethods = PaymentMethod::orderBy('region')
            ->orderBy('sort_order')
            ->paginate(15);

        $stats = [
            'total' => PaymentMethod::count(),
            'active' => PaymentMethod::where('is_active', true)->count(),
            'malawi' => PaymentMethod::where('region', 'malawi')->count(),
            'south_africa' => PaymentMethod::where('region', 'south_africa')->count(),
            'international' => PaymentMethod::where('region', 'international')->count(),
        ];

        return Inertia::render('Admin/PaymentMethods/Index', [
            'paymentMethods' => $paymentMethods,
            'stats' => $stats
        ]);
    }

    public function create()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return Inertia::render('Admin/PaymentMethods/Create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods',
            'type' => 'required|string|max:255',
            'region' => 'required|string|in:malawi,south_africa,international',
            'currency' => 'required|string|size:3',
            'instructions' => 'required|string|max:1000',
            'requirements' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:1'
        ]);

        PaymentMethod::create($validated);

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('message', 'Payment method created successfully.');
    }

    public function show(PaymentMethod $paymentMethod)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return Inertia::render('Admin/PaymentMethods/Show', [
            'paymentMethod' => $paymentMethod
        ]);
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return Inertia::render('Admin/PaymentMethods/Edit', [
            'paymentMethod' => $paymentMethod
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code,' . $paymentMethod->id,
            'type' => 'required|string|max:255',
            'region' => 'required|string|in:malawi,south_africa,international',
            'currency' => 'required|string|size:3',
            'instructions' => 'required|string|max:1000',
            'requirements' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:1'
        ]);

        $paymentMethod->update($validated);

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('message', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        // Check if payment method is being used
        if ($paymentMethod->enrollmentPayments()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete payment method that has been used in enrollments.']);
        }

        $paymentMethod->delete();

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('message', 'Payment method deleted successfully.');
    }

    public function toggle(PaymentMethod $paymentMethod)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        $status = $paymentMethod->is_active ? 'activated' : 'deactivated';

        return back()->with('message', "Payment method {$status} successfully.");
    }
}
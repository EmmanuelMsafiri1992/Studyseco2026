<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\AccessDuration;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentSettingsController extends Controller
{
    public function __construct()
    {
        // Admin check will be handled within methods
    }

    /**
     * Display payment settings dashboard
     */
    public function index()
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can manage payment settings.');
        }

        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
        $accessDurations = AccessDuration::orderBy('sort_order')->get();

        return Inertia::render('Admin/PaymentSettings/Index', [
            'paymentMethods' => $paymentMethods,
            'accessDurations' => $accessDurations,
        ]);
    }

    /**
     * Store a new payment method
     */
    public function storePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:50|unique:payment_methods,key',
            'type' => 'required|in:mobile_money,bank_transfer',
            'instructions' => 'nullable|string',
            'config' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = PaymentMethod::max('sort_order') + 1;

        PaymentMethod::create($validated);

        return back()->with('success', 'Payment method added successfully.');
    }

    /**
     * Update a payment method
     */
    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:50|unique:payment_methods,key,' . $paymentMethod->id,
            'type' => 'required|in:mobile_money,bank_transfer',
            'instructions' => 'nullable|string',
            'config' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update($validated);

        return back()->with('success', 'Payment method updated successfully.');
    }

    /**
     * Delete a payment method
     */
    public function destroyPaymentMethod(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return back()->with('success', 'Payment method deleted successfully.');
    }

    /**
     * Store a new access duration
     */
    public function storeAccessDuration(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = AccessDuration::max('sort_order') + 1;

        AccessDuration::create($validated);

        return back()->with('success', 'Access duration added successfully.');
    }

    /**
     * Update an access duration
     */
    public function updateAccessDuration(Request $request, AccessDuration $accessDuration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $accessDuration->update($validated);

        return back()->with('success', 'Access duration updated successfully.');
    }

    /**
     * Delete an access duration
     */
    public function destroyAccessDuration(AccessDuration $accessDuration)
    {
        $accessDuration->delete();
        return back()->with('success', 'Access duration deleted successfully.');
    }

    /**
     * Update sort order for payment methods
     */
    public function updatePaymentMethodOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:payment_methods,id'
        ]);

        foreach ($validated['order'] as $index => $id) {
            PaymentMethod::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Payment method order updated.');
    }

    /**
     * Update sort order for access durations
     */
    public function updateAccessDurationOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:access_durations,id'
        ]);

        foreach ($validated['order'] as $index => $id) {
            AccessDuration::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Access duration order updated.');
    }
}

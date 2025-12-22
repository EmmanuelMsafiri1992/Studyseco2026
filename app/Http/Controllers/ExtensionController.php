<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\EnrollmentPayment;
use App\Models\PaymentMethod;
use App\Notifications\ExtensionApproved;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExtensionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $enrollment = $user->enrollments()->with('payments')->first();
        
        if (!$enrollment) {
            return redirect()->route('dashboard')->with('error', 'You need to enroll in subjects first before you can extend access. Please contact support if you need assistance with enrollment.');
        }

        // Check if user has a premium account (not trial)
        if ($enrollment->is_trial) {
            return redirect()->route('account.settings')->with('error', 'You must upgrade to a Premium account before extending access. Please upgrade your account first.');
        }
        
        // Get available payment methods for user's region
        // If region is empty, default to 'malawi' or show all available methods
        $region = $enrollment->region ?: 'malawi';
        
        // Update enrollment region if it's empty
        if (empty($enrollment->region)) {
            $enrollment->region = 'malawi';
            $enrollment->save();
        }
        
        // Fix currency mismatch based on region
        $correctCurrency = $this->getCurrencyForRegion($region);
        if ($enrollment->currency !== $correctCurrency) {
            $enrollment->currency = $correctCurrency;
            $enrollment->save();
        }
        
        // Calculate extension pricing based on user's corrected currency
        $extensionPricing = $this->getExtensionPricing($enrollment->currency);
        
        $paymentMethods = PaymentMethod::where('region', $region)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return Inertia::render('Student/Extension', [
            'enrollment' => $enrollment,
            'extensionPricing' => $extensionPricing,
            'paymentMethods' => $paymentMethods,
            'daysRemaining' => $enrollment->access_days_remaining,
            'accessExpired' => $enrollment->is_access_expired,
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'extension_months' => 'required|integer|min:1|max:12',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_reference' => 'required|string|max:255',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        
        $user = auth()->user();
        $enrollment = $user->enrollments()->first();
        
        if (!$enrollment) {
            return back()->withErrors(['enrollment' => 'No active enrollment found.']);
        }

        // Check if user has a premium account (not trial)
        if ($enrollment->is_trial) {
            return back()->withErrors(['error' => 'You must upgrade to a Premium account before extending access. Please upgrade your account first.']);
        }
        
        // Calculate extension price
        $extensionPrice = $this->calculateExtensionPrice($enrollment->currency, $validated['extension_months']);
        
        // Store payment proof
        $paymentProofPath = $request->file('payment_proof')->store('extension_payments', 'public');
        
        // Create extension payment record
        $extensionPayment = EnrollmentPayment::create([
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => $validated['payment_method_id'],
            'reference_number' => $validated['payment_reference'],
            'amount' => $extensionPrice,
            'currency' => $enrollment->currency,
            'payment_proof_path' => $paymentProofPath,
            'payment_type' => 'extension',
            'extension_months' => $validated['extension_months'],
            'status' => 'pending'
        ]);
        
        return redirect()->route('student.extension')->with('success', 
            'Extension request submitted successfully! We will verify your payment within 24 hours and notify you via email and SMS.');
    }
    
    private function getExtensionPricing($currency)
    {
        $basePrice = match($currency) {
            'MWK' => 25000, // Half price for extensions
            'ZAR' => 175,
            'USD' => 12.50,
            default => 25000
        };
        
        return [
            1 => $basePrice * 1,
            2 => $basePrice * 2,
            3 => $basePrice * 3,
            4 => $basePrice * 4,
            6 => $basePrice * 5.5, // 6-month discount
            12 => $basePrice * 10,  // 1-year discount (2 months free)
        ];
    }
    
    private function calculateExtensionPrice($currency, $months)
    {
        $pricing = $this->getExtensionPricing($currency);
        return $pricing[$months] ?? ($pricing[1] * $months);
    }
    
    public function approve(EnrollmentPayment $payment)
    {
        if ($payment->payment_type !== 'extension') {
            return back()->withErrors(['error' => 'Invalid payment type']);
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
            'access_expires_at' => $newExpiry
        ]);
        
        // Send notification to student
        try {
            $enrollment->user->notify(new ExtensionApproved($enrollment, $payment));
        } catch (\Exception $e) {
            // Log error but don't fail the approval
            logger('Failed to send extension approval notification: ' . $e->getMessage());
        }
        
        return back()->with('success', 'Extension approved successfully! Student has been notified.');
    }
    
    private function getCurrencyForRegion($region)
    {
        return match($region) {
            'malawi' => 'MWK',
            'south_africa' => 'ZAR',
            'zambia' => 'ZMW',
            'botswana' => 'BWP',
            'zimbabwe' => 'USD', // USD is commonly used in Zimbabwe
            'international' => 'USD',
            default => 'MWK' // Default to Malawi Kwacha
        };
    }
}
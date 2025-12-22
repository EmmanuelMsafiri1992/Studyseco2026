<?php

namespace App\Http\Controllers;

use App\Models\TermsAcceptance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TermsController extends Controller
{
    public function show(Request $request)
    {
        $user = auth()->user();
        $termsType = $request->get('type', 'general');
        
        // Check if user has already accepted current version
        $hasAccepted = TermsAcceptance::hasAccepted($user->id, $termsType, '1.0');
        
        // Get latest acceptance record
        $latestAcceptance = TermsAcceptance::getLatestAcceptance($user->id, $termsType);
        
        return Inertia::render('Terms/Show', [
            'termsType' => $termsType,
            'hasAccepted' => $hasAccepted,
            'latestAcceptance' => $latestAcceptance,
            'currentVersion' => '1.0',
            'termsContent' => $this->getTermsContent($termsType),
            'requiresAcceptance' => !$hasAccepted
        ]);
    }

    public function accept(Request $request)
    {
        $request->validate([
            'terms_type' => 'required|string|in:general,enrollment,payment',
            'terms_version' => 'required|string',
            'accepted' => 'required|boolean|accepted'
        ]);

        $user = auth()->user();
        
        // Check if already accepted
        if (TermsAcceptance::hasAccepted($user->id, $request->terms_type, $request->terms_version)) {
            return redirect()->back()->with('info', 'You have already accepted these terms and conditions.');
        }

        // Record acceptance
        $termsContent = $this->getTermsContent($request->terms_type);
        
        TermsAcceptance::recordAcceptance(
            $user->id,
            $request->terms_type,
            $request->terms_version,
            $termsContent
        );

        return redirect()->back()->with('success', 'Terms and conditions accepted successfully.');
    }

    public function history()
    {
        $user = auth()->user();
        
        $acceptances = TermsAcceptance::where('user_id', $user->id)
            ->orderBy('accepted_at', 'desc')
            ->get();

        return Inertia::render('Terms/History', [
            'acceptances' => $acceptances
        ]);
    }

    private function getTermsContent(string $type): array
    {
        $baseTerms = [
            'version' => '1.0',
            'effective_date' => '2025-09-01',
            'last_updated' => '2025-09-01'
        ];

        switch ($type) {
            case 'enrollment':
                return array_merge($baseTerms, [
                    'title' => 'Enrollment Terms and Conditions',
                    'sections' => [
                        [
                            'title' => 'Enrollment Policy',
                            'content' => 'By enrolling in StudySeco subjects, you agree to attend classes regularly and complete assignments as required. Enrollment fees are non-refundable except as specified in our cancellation policy.'
                        ],
                        [
                            'title' => '14-Day Cancellation Policy',
                            'content' => 'You may cancel your enrollment within 14 days of registration for a full refund. After 14 days, no refunds will be provided. Cancellation requests must be submitted through our official platform.'
                        ],
                        [
                            'title' => 'Academic Integrity',
                            'content' => 'Students are expected to maintain high standards of academic integrity. Plagiarism, cheating, or any form of academic dishonesty will result in immediate termination of enrollment without refund.'
                        ],
                        [
                            'title' => 'Subject Access',
                            'content' => 'Subject materials and access are provided for the duration specified in your enrollment package. Access will be revoked upon expiration or violation of these terms.'
                        ]
                    ]
                ]);

            case 'payment':
                return array_merge($baseTerms, [
                    'title' => 'Payment Terms and Conditions',
                    'sections' => [
                        [
                            'title' => 'Payment Policy',
                            'content' => 'All payments must be made through approved payment methods. Payments are processed securely and confirmations are sent via email.'
                        ],
                        [
                            'title' => 'Refund Policy',
                            'content' => 'Refunds are available within 14 days of payment for unused services. Refund processing may take 5-10 business days depending on your payment method.'
                        ],
                        [
                            'title' => 'Billing and Charges',
                            'content' => 'You agree to pay all charges associated with your account. Failure to pay may result in suspension of services.'
                        ],
                        [
                            'title' => 'Payment Verification',
                            'content' => 'All payments are subject to verification. Access to services will be granted upon successful payment verification by our administrators.'
                        ]
                    ]
                ]);

            default: // general
                return array_merge($baseTerms, [
                    'title' => 'General Terms and Conditions',
                    'sections' => [
                        [
                            'title' => 'Acceptance of Terms',
                            'content' => 'By using StudySeco services, you agree to be bound by these terms and conditions. If you do not agree to these terms, you may not use our services.'
                        ],
                        [
                            'title' => 'User Responsibilities',
                            'content' => 'Users are responsible for maintaining the confidentiality of their account information and for all activities that occur under their account.'
                        ],
                        [
                            'title' => 'Service Availability',
                            'content' => 'We strive to maintain service availability but do not guarantee uninterrupted access. Maintenance and updates may temporarily affect service availability.'
                        ],
                        [
                            'title' => 'Privacy and Data Protection',
                            'content' => 'We respect your privacy and handle your personal data in accordance with applicable data protection laws and our Privacy Policy.'
                        ],
                        [
                            'title' => 'Limitation of Liability',
                            'content' => 'StudySeco shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of your use of our services.'
                        ]
                    ]
                ]);
        }
    }
}
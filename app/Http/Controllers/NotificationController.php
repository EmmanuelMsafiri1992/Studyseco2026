<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Enrollment;
use App\Models\EnrollmentPayment;
use App\Models\User;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get user notifications from database
        $userNotifications = $user->notifications()->latest()->take(10)->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $this->getNotificationType($notification->type),
                'title' => $this->getNotificationTitle($notification),
                'description' => $this->getNotificationDescription($notification),
                'time' => $notification->created_at->diffForHumans(),
                'timestamp' => $notification->created_at,
                'read' => $notification->read_at !== null,
                'priority' => $this->getNotificationPriority($notification->type),
                'data' => $notification->data
            ];
        });

        // Generate system notifications based on real data
        $systemNotifications = collect();
        
        if ($user->role === 'admin') {
            // Recent enrollments
            $recentEnrollments = Enrollment::with('user')
                ->where('created_at', '>=', now()->subDays(7))
                ->latest()
                ->limit(5)
                ->get();

            foreach ($recentEnrollments as $enrollment) {
                $systemNotifications->push([
                    'id' => 'enrollment_' . $enrollment->id,
                    'type' => 'enrollment',
                    'title' => 'New Student Enrollment',
                    'description' => ($enrollment->user ? $enrollment->user->name : $enrollment->name) . ' enrolled in ' . count($enrollment->selected_subjects ?: []) . ' subjects',
                    'time' => $enrollment->created_at->diffForHumans(),
                    'timestamp' => $enrollment->created_at,
                    'read' => true, // System notifications are auto-read
                    'priority' => 'normal',
                    'avatar' => $enrollment->user?->profile_photo_url
                ]);
            }

            // Recent payments
            $recentPayments = EnrollmentPayment::with('enrollment.user')
                ->where('created_at', '>=', now()->subDays(7))
                ->latest()
                ->limit(5)
                ->get();

            foreach ($recentPayments as $payment) {
                $systemNotifications->push([
                    'id' => 'payment_' . $payment->id,
                    'type' => 'payment',
                    'title' => 'Payment ' . ucfirst($payment->status),
                    'description' => ($payment->enrollment->user ? $payment->enrollment->user->name : $payment->enrollment->name) . ' payment of ' . $payment->amount . ' ' . $payment->currency,
                    'time' => $payment->created_at->diffForHumans(),
                    'timestamp' => $payment->created_at,
                    'read' => true,
                    'priority' => $payment->status === 'verified' ? 'normal' : 'high',
                    'amount' => $payment->amount . ' ' . $payment->currency
                ]);
            }

            // Pending enrollments alert
            $pendingCount = Enrollment::where('status', 'pending')->count();
            if ($pendingCount > 0) {
                $systemNotifications->push([
                    'id' => 'pending_enrollments',
                    'type' => 'alert',
                    'title' => 'Pending Enrollments',
                    'description' => $pendingCount . ' enrollment' . ($pendingCount > 1 ? 's' : '') . ' awaiting your approval',
                    'time' => 'Now',
                    'timestamp' => now(),
                    'read' => false,
                    'priority' => 'high'
                ]);
            }
        } elseif ($user->role === 'student') {
            // Student-specific notifications
            $enrollment = Enrollment::where('email', $user->email)->first();
            
            if ($enrollment) {
                // Account type notification
                $systemNotifications->push([
                    'id' => 'account_type',
                    'type' => 'account',
                    'title' => 'Account Type',
                    'description' => $enrollment->is_trial ? 'Free Trial Account - ' . $enrollment->trial_days_remaining . ' days remaining' : 'Full Access Account',
                    'time' => $enrollment->created_at->diffForHumans(),
                    'timestamp' => $enrollment->created_at,
                    'read' => true,
                    'priority' => $enrollment->is_trial && $enrollment->trial_days_remaining <= 2 ? 'high' : 'normal'
                ]);
                
                // Trial expiry warning
                if ($enrollment->is_trial && $enrollment->trial_days_remaining <= 3) {
                    $systemNotifications->push([
                        'id' => 'trial_expiry',
                        'type' => 'warning',
                        'title' => 'Trial Expiring Soon',
                        'description' => 'Your free trial will expire in ' . $enrollment->trial_days_remaining . ' days. Upgrade to continue access.',
                        'time' => 'Now',
                        'timestamp' => now(),
                        'read' => false,
                        'priority' => 'high'
                    ]);
                }
                
                // Welcome notification for new enrollments
                if ($enrollment->created_at->greaterThan(now()->subDays(1))) {
                    $systemNotifications->push([
                        'id' => 'welcome',
                        'type' => 'welcome',
                        'title' => 'Welcome to StudySeco!',
                        'description' => 'Your enrollment has been processed. You now have access to ' . count($enrollment->selected_subjects) . ' subjects.',
                        'time' => $enrollment->created_at->diffForHumans(),
                        'timestamp' => $enrollment->created_at,
                        'read' => false,
                        'priority' => 'normal'
                    ]);
                }
            }
        }

        // Combine user notifications and system notifications
        $allNotifications = $userNotifications->concat($systemNotifications)
            ->sortByDesc('timestamp')
            ->values();

        return Inertia::render('Notifications/Index', [
            'notifications' => $allNotifications
        ]);
    }

    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }

    private function getNotificationType($type)
    {
        // Map Laravel notification types to frontend types
        $typeMap = [
            'App\\Notifications\\PaymentApproved' => 'payment',
            'App\\Notifications\\PaymentRejected' => 'payment',
            'App\\Notifications\\ExtensionApproved' => 'system',
            'App\\Notifications\\VerificationCode' => 'system',
        ];

        return $typeMap[$type] ?? 'system';
    }

    private function getNotificationTitle($notification)
    {
        $type = $notification->type;
        $data = $notification->data;

        switch ($type) {
            case 'App\\Notifications\\PaymentApproved':
                return 'Payment Approved';
            case 'App\\Notifications\\PaymentRejected':
                return 'Payment Rejected';
            case 'App\\Notifications\\ExtensionApproved':
                return 'Extension Approved';
            case 'App\\Notifications\\VerificationCode':
                return 'Verification Code';
            default:
                return 'Notification';
        }
    }

    private function getNotificationDescription($notification)
    {
        $type = $notification->type;
        $data = $notification->data;

        switch ($type) {
            case 'App\\Notifications\\PaymentApproved':
                return 'Your payment has been approved and access granted';
            case 'App\\Notifications\\PaymentRejected':
                return isset($data['rejection_reason']) ? $data['rejection_reason'] : 'Your payment was rejected';
            case 'App\\Notifications\\ExtensionApproved':
                return 'Your access extension has been approved';
            case 'App\\Notifications\\VerificationCode':
                return 'Your verification code: ' . ($data['verification_code'] ?? '');
            default:
                return $data['message'] ?? 'You have a new notification';
        }
    }

    private function getNotificationPriority($type)
    {
        $priorityMap = [
            'App\\Notifications\\PaymentApproved' => 'normal',
            'App\\Notifications\\PaymentRejected' => 'high',
            'App\\Notifications\\ExtensionApproved' => 'normal',
            'App\\Notifications\\VerificationCode' => 'normal',
        ];

        return $priorityMap[$type] ?? 'normal';
    }

    public function getUnreadCount()
    {
        $user = auth()->user();
        $count = $user->unreadNotifications()->count();
        
        // Add system alerts for admin
        if ($user->role === 'admin') {
            $count += Enrollment::where('status', 'pending')->count();
        }
        
        return response()->json(['count' => $count]);
    }
}
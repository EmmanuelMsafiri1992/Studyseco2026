<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollment;
use App\Models\EnrollmentPayment;

class ExtensionApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $enrollment;
    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Enrollment $enrollment, EnrollmentPayment $payment)
    {
        $this->enrollment = $enrollment;
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysRemaining = $this->enrollment->access_days_remaining;
        $expiresAt = $this->enrollment->access_expires_at;
        $extensionMonths = $this->payment->extension_months;
        
        return (new MailMessage)
            ->subject('✅ Access Extended - StudySeco')
            ->greeting('Great news, ' . $this->enrollment->user->name . '!')
            ->line('Your access extension has been approved and your StudySeco access has been extended.')
            ->line('**Extension Details:**')
            ->line('• Extension Period: ' . $extensionMonths . ' month' . ($extensionMonths > 1 ? 's' : ''))
            ->line('• New Expiry Date: ' . $expiresAt->format('F j, Y'))
            ->line('• Days Remaining: ' . $daysRemaining . ' days')
            ->line('• Amount Paid: ' . number_format($this->payment->amount) . ' ' . $this->payment->currency)
            ->action('Continue Learning', route('dashboard'))
            ->line('Your extended access is now active. Continue your learning journey!')
            ->line('Thank you for choosing StudySeco! 📚✨');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'extension_approved',
            'title' => 'Access Extended!',
            'message' => 'Your access extension has been approved for ' . $this->payment->extension_months . ' month(s).',
            'enrollment_id' => $this->enrollment->id,
            'extension_months' => $this->payment->extension_months,
            'expires_at' => $this->enrollment->access_expires_at,
            'days_remaining' => $this->enrollment->access_days_remaining,
            'action_url' => route('dashboard')
        ];
    }

    /**
     * Get SMS representation of the notification (if SMS service configured)
     */
    public function toNexmo($notifiable)
    {
        $name = $this->enrollment->user->name;
        $months = $this->payment->extension_months;
        $days = $this->enrollment->access_days_remaining;
        
        return [
            'content' => "🎉 Hi {$name}! Your {$months}-month StudySeco extension is approved! You now have {$days} days of access. Keep learning!",
        ];
    }
}
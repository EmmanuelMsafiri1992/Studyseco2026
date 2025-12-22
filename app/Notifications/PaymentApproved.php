<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollment;
use App\Models\EnrollmentPayment;

class PaymentApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $enrollment;
    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Enrollment $enrollment, $payment = null)
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
        // Add 'nexmo' here for SMS when configured
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysRemaining = $this->enrollment->access_days_remaining;
        $expiresAt = $this->enrollment->access_expires_at;
        $subjectCount = $this->enrollment->subject_count ?? count($this->enrollment->selected_subjects ?? []);

        return (new MailMessage)
            ->subject('🎉 Payment Approved - Welcome to StudySeco!')
            ->greeting('Congratulations, ' . $this->enrollment->user->name . '!')
            ->line('Great news! Your payment has been approved and your StudySeco access is now active.')
            ->line('**Access Details:**')
            ->line('• Expires: ' . $expiresAt->format('F j, Y'))
            ->line('• Duration: ' . $daysRemaining . ' days remaining')
            ->line('• Subjects: ' . $subjectCount . ' enrolled')
            ->action('Access Your Dashboard', route('dashboard'))
            ->line('You can now access all your enrolled subjects and start learning!')
            ->line('If you have any questions, our support team is here to help.')
            ->line('Welcome to StudySeco! 🚀');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_approved',
            'title' => 'Payment Approved!',
            'message' => 'Your payment has been approved. Welcome to StudySeco!',
            'enrollment_id' => $this->enrollment->id,
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
        $days = $this->enrollment->access_days_remaining;
        
        return [
            'content' => "🎉 Hi {$name}! Your StudySeco payment is approved! You now have {$days} days of access. Login at studyseco.com to start learning. Welcome!",
        ];
    }
}
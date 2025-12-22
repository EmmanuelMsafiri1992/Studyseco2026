<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollment;
use App\Models\EnrollmentPayment;

class PaymentRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $enrollment;
    protected $payment;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Enrollment $enrollment, $payment = null, $reason = null)
    {
        $this->enrollment = $enrollment;
        $this->payment = $payment;
        $this->reason = $reason;
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
        $message = (new MailMessage)
            ->subject('❌ Payment Update - StudySeco')
            ->greeting('Hello ' . $this->enrollment->user->name . ',')
            ->line('We\'ve reviewed your payment submission, but unfortunately we cannot approve it at this time.')
            ->line('**Reason for rejection:**')
            ->line($this->reason ?: 'The payment details could not be verified.')
            ->line('**What you can do:**')
            ->line('• Double-check your payment details and try again')
            ->line('• Contact our support team if you need assistance')
            ->line('• Make sure your payment reference and proof are clear and accurate')
            ->action('Submit New Payment', route('enrollment.store'))
            ->line('We\'re here to help you get access to StudySeco. Please don\'t hesitate to reach out!')
            ->line('Thank you for your understanding.');
            
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_rejected',
            'title' => 'Payment Not Approved',
            'message' => 'Your payment could not be approved. Please review the details and try again.',
            'enrollment_id' => $this->enrollment->id,
            'reason' => $this->reason,
            'action_url' => route('enrollment.store')
        ];
    }

    /**
     * Get SMS representation of the notification (if SMS service configured)
     */
    public function toNexmo($notifiable)
    {
        $name = $this->enrollment->user->name;
        
        return [
            'content' => "Hi {$name}, your StudySeco payment couldn't be approved. Please check your email for details and resubmit. Need help? Contact support.",
        ];
    }
}
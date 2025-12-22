<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentPending extends Notification implements ShouldQueue
{
    use Queueable;

    protected $enrollment;

    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subjectCount = is_array($this->enrollment->selected_subjects) ? count($this->enrollment->selected_subjects) : 0;
        
        return (new MailMessage)
            ->subject('📋 StudySeco Enrollment - Payment Verification in Progress')
            ->greeting('Hello ' . $this->enrollment->name . '!')
            ->line('Thank you for enrolling with StudySeco! We have received your enrollment application and payment proof.')
            ->line('')
            ->line('**Enrollment Details:**')
            ->line('• Name: ' . $this->enrollment->name)
            ->line('• Email: ' . $this->enrollment->email)
            ->line('• Phone: ' . $this->enrollment->phone)
            ->line('• Subjects: ' . $subjectCount . ' selected')
            ->line('• Amount: ' . $this->enrollment->currency . ' ' . number_format($this->enrollment->total_amount, 2))
            ->line('• Payment Method: ' . ucfirst($this->enrollment->payment_method))
            ->line('• Reference: ' . $this->enrollment->payment_reference)
            ->line('')
            ->line('**What happens next?**')
            ->line('🔍 Our team is currently verifying your payment')
            ->line('⏱️ Verification typically takes 2-24 hours')
            ->line('✅ Once approved, you will receive login credentials via email')
            ->line('📚 You will then have immediate access to all your enrolled subjects')
            ->line('')
            ->line('**Need to track your application?**')
            ->line('Reference Number: **' . $this->enrollment->payment_reference . '**')
            ->line('Status: **Payment Verification Pending**')
            ->line('')
            ->line('If you have any questions or concerns, please contact our support team.')
            ->line('')
            ->line('Thank you for choosing StudySeco for your education journey! 🎓');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'enrollment_pending',
            'title' => 'Enrollment Under Review',
            'message' => 'Your enrollment application is being reviewed. We will notify you once approved.',
            'enrollment_id' => $this->enrollment->id,
            'reference_number' => $this->enrollment->payment_reference
        ];
    }
}
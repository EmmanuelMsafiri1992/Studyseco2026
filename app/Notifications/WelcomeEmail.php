<?php

namespace App\Notifications;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $enrollment;
    protected $tempPassword;

    public function __construct(User $user, Enrollment $enrollment, string $tempPassword)
    {
        $this->user = $user;
        $this->enrollment = $enrollment;
        $this->tempPassword = $tempPassword;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subjectCount = is_array($this->enrollment->selected_subjects) ? count($this->enrollment->selected_subjects) : 0;
        
        return (new MailMessage)
            ->subject('🎉 Welcome to StudySeco - Your Account is Ready!')
            ->greeting('Welcome, ' . $this->user->name . '!')
            ->line('Congratulations! Your enrollment has been approved and your StudySeco account is now active.')
            ->line('')
            ->line('**Your Login Credentials:**')
            ->line('• Email: ' . $this->user->email)
            ->line('• Temporary Password: **' . $this->tempPassword . '**')
            ->line('')
            ->line('**Enrollment Details:**')
            ->line('• Subjects Enrolled: ' . $subjectCount)
            ->line('• Access Duration: 4 months')
            ->line('• Access Expires: ' . $this->enrollment->access_expires_at->format('F j, Y'))
            ->line('• Currency: ' . $this->enrollment->currency)
            ->line('• Total Paid: ' . $this->enrollment->currency . ' ' . number_format($this->enrollment->total_amount, 2))
            ->line('')
            ->action('Login to Your Dashboard', route('login'))
            ->line('**Important Security Note:**')
            ->line('Please change your password after logging in for the first time.')
            ->line('')
            ->line('You can now access all your enrolled subjects and start learning!')
            ->line('If you have any questions, our support team is here to help.')
            ->line('Welcome to StudySeco! 🚀');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome_email',
            'title' => 'Welcome to StudySeco!',
            'message' => 'Your account is ready. Check your email for login credentials.',
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->user->id,
            'action_url' => route('dashboard')
        ];
    }
}
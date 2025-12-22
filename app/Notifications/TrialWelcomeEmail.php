<?php

namespace App\Notifications;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialWelcomeEmail extends Notification implements ShouldQueue
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
        $trialDays = $this->enrollment->trial_expires_at->diffInDays(now());
        
        return (new MailMessage)
            ->subject('🎉 Welcome to StudySeco - Your FREE Trial is Ready!')
            ->greeting('Welcome, ' . $this->user->name . '!')
            ->line('🚀 Great news! Your FREE 7-day trial with StudySeco has been activated!')
            ->line('')
            ->line('**Your Login Credentials:**')
            ->line('• Email: ' . $this->user->email)
            ->line('• Temporary Password: **' . $this->tempPassword . '**')
            ->line('')
            ->line('**Trial Details:**')
            ->line('• Subjects Available: ' . $subjectCount)
            ->line('• Trial Duration: FREE for 7 days')
            ->line('• Trial Expires: ' . $this->enrollment->trial_expires_at->format('F j, Y'))
            ->line('• Full Access: All features included!')
            ->line('')
            ->action('Start Your Free Trial Now', route('login'))
            ->line('')
            ->line('**What you get with your trial:**')
            ->line('✅ Complete access to all enrolled subjects')
            ->line('✅ Interactive learning materials')
            ->line('✅ Community discussions and support')
            ->line('✅ Progress tracking and assessments')
            ->line('')
            ->line('**Important Notes:**')
            ->line('• Please change your password after your first login')
            ->line('• Your trial will automatically expire after 7 days')
            ->line('• You can upgrade to a full subscription anytime during your trial')
            ->line('')
            ->line('Ready to start learning? Click the button above to access your dashboard!')
            ->line('')
            ->line('Questions? Our support team is here to help. Welcome to StudySeco! 🎓');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trial_welcome_email',
            'title' => 'FREE Trial Activated!',
            'message' => 'Your 7-day free trial is ready. Check your email for login credentials.',
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->user->id,
            'trial_expires_at' => $this->enrollment->trial_expires_at,
            'action_url' => route('dashboard')
        ];
    }
}
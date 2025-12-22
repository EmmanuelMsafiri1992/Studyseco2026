<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollment;
use App\Models\Subject;

class SubjectsAdded extends Notification
{
    use Queueable;

    protected $enrollment;
    protected $additionalSubjects;

    /**
     * Create a new notification instance.
     */
    public function __construct(Enrollment $enrollment, array $additionalSubjects)
    {
        $this->enrollment = $enrollment;
        $this->additionalSubjects = $additionalSubjects;
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
        $subjectNames = Subject::whereIn('id', $this->additionalSubjects)
            ->pluck('name')
            ->implode(', ');

        return (new MailMessage)
            ->subject('New Subjects Added to Your Enrollment')
            ->greeting('Great news!')
            ->line('Your payment has been approved and the following subjects have been added to your enrollment:')
            ->line('**New Subjects:** ' . $subjectNames)
            ->line('You now have access to **' . count($this->enrollment->selected_subjects) . ' subjects** in total.')
            ->action('Access Your Subjects', route('student.subjects.index'))
            ->line('Happy learning!')
            ->salutation('Best regards, The StudySeco Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $subjectNames = Subject::whereIn('id', $this->additionalSubjects)
            ->pluck('name')
            ->toArray();

        return [
            'title' => 'New Subjects Added',
            'message' => 'Payment approved! ' . count($this->additionalSubjects) . ' new subject(s) added to your enrollment.',
            'type' => 'subject_addition',
            'enrollment_id' => $this->enrollment->id,
            'added_subjects' => $subjectNames,
            'total_subjects' => count($this->enrollment->selected_subjects),
            'action_url' => route('student.subjects.index'),
        ];
    }
}
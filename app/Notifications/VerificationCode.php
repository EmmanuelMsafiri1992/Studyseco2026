<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCode extends Notification
{
    use Queueable;

    public $verificationCode;
    public $type;

    public function __construct($verificationCode, $type = 'email')
    {
        $this->verificationCode = $verificationCode;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        if ($this->type === 'email') {
            return ['mail'];
        }
        
        // For SMS, you would add your SMS channel here
        // return ['mail', 'sms'];
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('StudySeco - Verification Code')
            ->greeting('Hello!')
            ->line('Your verification code is: **' . $this->verificationCode . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this verification, please ignore this email.')
            ->salutation('Best regards, StudySeco Team');
    }

    public function toArray($notifiable)
    {
        return [
            'verification_code' => $this->verificationCode,
            'type' => $this->type
        ];
    }
}
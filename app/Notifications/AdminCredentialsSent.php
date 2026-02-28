<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCredentialsSent extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $loginUrl,
        public string $scheme = 'https'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TalentTune - Your Admin Account Credentials')
            ->greeting("Hello {$this->name},")
            ->line('You have been added as a **TalentTune Admin** and can manage institutions, users, payments, and support.')
            ->line('You can log in using the credentials below.')
            ->line('## Your Login Credentials')
            ->line("**Email:** {$this->email}")
            ->line("**Password:** {$this->password}")
            ->line('> **Important:** Please change your password after your first login for security purposes.')
            ->action('Log in to TalentTune Admin', $this->loginUrl)
            ->line('If you did not expect this email or have any questions, please contact the TalentTune team.')
            ->salutation('Best regards, The TalentTune Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'role' => 'admin',
        ];
    }
}

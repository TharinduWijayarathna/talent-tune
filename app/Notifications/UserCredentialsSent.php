<?php

namespace App\Notifications;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCredentialsSent extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Institution $institution,
        public string $name,
        public string $email,
        public string $password,
        public string $role,
        public string $baseDomain,
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
        $roleLabel = $this->role === 'lecturer' ? 'Lecturer' : 'Student';
        $loginUrl = "{$this->scheme}://{$this->institution->slug}.{$this->baseDomain}/login";

        return (new MailMessage)
            ->subject("{$this->institution->name} - Your {$roleLabel} Account Credentials")
            ->greeting("Hello {$this->name},")
            ->line("You have been added as a **{$roleLabel}** to **{$this->institution->name}** on TalentTune.")
            ->line('You can log in using the credentials below.')
            ->line('## Your Login Credentials')
            ->line("**Email:** {$this->email}")
            ->line("**Password:** {$this->password}")
            ->line('> **Important:** Please change your password after your first login for security purposes.')
            ->action('Log in to TalentTune', $loginUrl)
            ->line("Your institution portal: **{$this->institution->slug}.{$this->baseDomain}**")
            ->line('If you did not expect this email or have any questions, please contact your institution administrator.')
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
            'institution_id' => $this->institution->id,
            'institution_name' => $this->institution->name,
            'role' => $this->role,
        ];
    }
}

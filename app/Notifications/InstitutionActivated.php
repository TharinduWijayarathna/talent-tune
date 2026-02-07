<?php

namespace App\Notifications;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstitutionActivated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Institution $institution,
        public string $email,
        public string $password,
        public string $baseDomain
    ) {
        //
    }

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
        $loginUrl = url("https://{$this->institution->slug}.{$this->baseDomain}/login");

        return (new MailMessage)
            ->subject("{$this->institution->name} - Your TalentTune Account Has Been Activated")
            ->greeting("Hello {$this->institution->contact_person},")
            ->line("Great news! Your institution **{$this->institution->name}** has been reviewed and activated by our admin team.")
            ->line('You can now access your TalentTune portal and start managing viva sessions.')
            ->line('## Your Login Credentials')
            ->line("**Email:** {$this->email}")
            ->line("**Password:** {$this->password}")
            ->line('> **Important:** Please change your password after your first login for security purposes.')
            ->action('Access Your Portal', $loginUrl)
            ->line("Your institution portal is available at: **{$this->institution->slug}.{$this->baseDomain}**")
            ->line('If you have any questions or need assistance, please don\'t hesitate to contact our support team.')
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
        ];
    }
}

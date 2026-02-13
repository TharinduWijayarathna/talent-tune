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
     *
     * @param  string|null  $paymentUrl  Signed URL to complete subscription (required before dashboard is available)
     */
    public function __construct(
        public Institution $institution,
        public string $email,
        public string $password,
        public string $baseDomain,
        public ?string $paymentUrl = null
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = "https://{$this->institution->slug}.{$this->baseDomain}/login";

        $mail = (new MailMessage)
            ->subject("{$this->institution->name} - Your TalentTune Account Has Been Activated")
            ->greeting("Hello {$this->institution->contact_person},")
            ->line("Great news! Your institution **{$this->institution->name}** has been reviewed and activated by our admin team.")
            ->line('## Your Login Credentials')
            ->line("**Email:** {$this->email}")
            ->line("**Password:** {$this->password}")
            ->line('> **Important:** Please change your password after your first login.')
            ->line("Your portal: **{$this->institution->slug}.{$this->baseDomain}**");

        if ($this->paymentUrl) {
            $mail->line('## Complete payment to access the workspace')
                ->line('Before you can use the dashboard and other features, you must complete your monthly subscription payment.')
                ->action('Complete payment', $this->paymentUrl)
                ->line('You can also log in first, then complete payment from the subscription page.');
        }

        $mail->action('Log in', $loginUrl)
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Best regards, The TalentTune Team');

        return $mail;
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

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

        $mail->line('## 14-day free trial')
            ->line('You have **14 days of free access** to the dashboard and all features. Log in now to get started.');

        if ($this->paymentUrl) {
            $mail->line('## After your trial')
                ->line('When your trial ends, you will need to complete your monthly subscription payment to continue. You can do this from the subscription page after logging in.');
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

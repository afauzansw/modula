<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Delivers the one-time code for the OTP-gated password change flow.
 *
 * AuthRepository triggers this instead of sending mail inline, so the
 * template and transport stay swappable without touching the repository.
 */
class PasswordChangeOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly int $expiresInMinutes = 10,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your password change code')
            ->line('Use this one-time code to change your password:')
            ->line("**{$this->code}**")
            ->line("The code expires in {$this->expiresInMinutes} minutes.")
            ->line('If you did not request a password change, you can ignore this email.');
    }
}

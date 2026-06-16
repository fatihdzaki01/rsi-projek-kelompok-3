<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmail extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
        public string $email,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verifyUrl = env('FRONTEND_URL', 'http://localhost:5173')
            . '/verify-email?email=' . urlencode($this->email)
            . '&token=' . urlencode($this->token);

        return (new MailMessage)
            ->subject('Verifikasi Email - Berbagive')
            ->greeting('Halo!')
            ->line('Terima kasih telah mendaftar di Berbagive.')
            ->line('Silakan klik tombol di bawah untuk memverifikasi email Anda.')
            ->action('Verifikasi Email', $verifyUrl)
            ->line('Link ini akan kedaluwarsa dalam 24 jam.')
            ->line('Jika Anda tidak melakukan pendaftaran, abaikan email ini.');
    }
}

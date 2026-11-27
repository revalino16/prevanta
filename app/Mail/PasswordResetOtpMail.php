<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $userName,
        public readonly string $otpCode,
        public readonly int $expiresInMinutes = 10,
    ) {}

    /**
     * Mendapatkan konfigurasi amplop email (subject).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Reset Kata Sandi - Prevanta Posyandu Jambu 77',
        );
    }

    /**
     * Mendapatkan konten HTML dan versi teks polos email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp',
            text: 'emails.password-reset-otp-text',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
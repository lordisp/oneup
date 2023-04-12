<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MailerMail extends Mailable
{
    public function __construct()
    {
    }

    public function envelope(): Envelope
    {

        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.mailer',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemExpirationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $daysRemaining;
    public $expirationDate;

    /**
     * Create a new message instance.
     */
    public function __construct($daysRemaining, $expirationDate)
    {
        $this->daysRemaining = $daysRemaining;
        $this->expirationDate = $expirationDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio de Vencimiento de Membresía del Sistema SURTITORNILLOS - Faltan ' . $this->daysRemaining . ' días',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.system-expiration',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

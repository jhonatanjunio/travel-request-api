<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancellationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly TravelRequest $travelRequest,
        public readonly string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.mail_cancellation_rejected_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cancellation-rejected',
        );
    }
}

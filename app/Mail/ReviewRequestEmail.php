<?php

namespace App\Mail;

use App\Models\ReviewRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReviewRequest $reviewRequest,
        public bool $isReminder = false
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isReminder 
            ? 'Reminder: ' . $this->reviewRequest->subject
            : $this->reviewRequest->subject;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.review-request',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

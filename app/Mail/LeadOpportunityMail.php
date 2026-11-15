<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadOpportunityMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Lead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'A new local project may suit your business');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lead-opportunity');
    }
}

<?php

namespace App\Mail;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSubmissionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Site $site,
        public ContactMessage $message,
    ) {}

    public function envelope(): Envelope
    {
        $subject = filled($this->message->subject)
            ? 'New contact form submission: '.$this->message->subject
            : 'New contact form submission on '.($this->site->name ?? config('app.name'));

        return new Envelope(
            subject: $subject,
            // Replying to the email goes straight back to the visitor.
            replyTo: [new Address($this->message->email, $this->message->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-submission',
            with: [
                'dashboardUrl' => $this->dashboardUrl(),
            ],
        );
    }

    protected function dashboardUrl(): ?string
    {
        try {
            return ContactMessageResource::getUrl('index', panel: 'admin', tenant: $this->site);
        } catch (\Throwable) {
            return null;
        }
    }
}

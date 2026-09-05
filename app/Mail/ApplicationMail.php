<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $title; 
    public $news;
    public $locale;

    public function __construct($title, $news, $locale = null)
    {
        //
        $this->title = $title;
        $this->news = $news;
        $this->locale = $locale ?: app()->getLocale();
    }


    /**
     * Get the message envelope.
     */
     public function envelope(): Envelope
    {
        $subject = $this->locale === 'bm' ? 'Berita QIS' : 'QIS News';
        return new Envelope(
            subject: $subject,
        );
    }
    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $viewName = $this->locale === 'bm' ? 'email.application_email_bm' : 'email.application_email_en';
        return new Content(
            view: $viewName,
            with: ([
                'title' => $this->title,
                'news' => $this->news
            ]),
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

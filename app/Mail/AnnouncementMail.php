<?php

namespace App\Mail;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public Announcement $announcement;
    public ?string $imagePath;
    public $locale;

    /**
     * Create a new message instance.
     */
    public function __construct(Announcement $announcement, $locale = null)
    {
        $this->announcement = $announcement;
        $this->locale = $locale ?: app()->getLocale();

        // Embed announcement image path
        $firstAttachment = $announcement->attachments->first();
        if ($firstAttachment) {
            $filePath = storage_path('app/public/' . $firstAttachment->file_path);
            if (file_exists($filePath)) {
                $this->imagePath = $filePath;
            } else {
                $this->imagePath = null;
            }
        } else {
            $this->imagePath = null;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->locale === 'bm' 
            ? '[Pengumuman QIS] ' . $this->announcement->title
            : '[QIS Announcement] ' . $this->announcement->title;

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $viewName = $this->locale === 'bm' ? 'email.announcement_email_bm' : 'email.announcement_email_en';

        return new Content(
            view: $viewName,
            with: [
                'announcement' => $this->announcement,
                'imagePath'    => $this->imagePath,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        foreach ($this->announcement->attachments as $att) {
            $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromStorageDisk('public', $att->file_path)
                ->as($att->file_name)
                ->withMime($att->file_type);
        }
        return $attachments;
    }
}

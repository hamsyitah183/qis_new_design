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

    /**
     * Create a new message instance.
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;

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
        return new Envelope(
            subject: '[QIS Announcement] ' . $this->announcement->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.announcement_email',
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

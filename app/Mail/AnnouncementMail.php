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
    public ?string $imageBase64;
    public ?string $imageMime;
    public string $logoBase64;

    /**
     * Create a new message instance.
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;

        // Embed logo as base64
        $logoPath = public_path('asset/Logo-DOA.png');
        $this->logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';

        // Embed announcement image as base64
        $firstAttachment = $announcement->attachments->first();
        if ($firstAttachment) {
            $filePath = storage_path('app/public/' . $firstAttachment->file_path);
            if (file_exists($filePath)) {
                $this->imageBase64 = 'data:' . $firstAttachment->file_type . ';base64,' . base64_encode(file_get_contents($filePath));
                $this->imageMime   = $firstAttachment->file_type;
            } else {
                $this->imageBase64 = null;
                $this->imageMime   = null;
            }
        } else {
            $this->imageBase64 = null;
            $this->imageMime   = null;
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
                'imageBase64'  => $this->imageBase64,
                'logoBase64'   => $this->logoBase64,
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

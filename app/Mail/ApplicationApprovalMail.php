<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageEn;
    public $user_fullname;
    public $url;
    public $application_id;

    /**
     * Create a new message instance.
     */
    public function __construct($messageEn, $user_fullname, $url, $application_id)
    {
        $this->messageEn = $messageEn;
        $this->user_fullname = $user_fullname;
        $this->url = $url;
        $this->application_id = $application_id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Application Submitted: ' . $this->application_id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Fetch the application based on its type/ID
        $application = \App\Models\IpApplication::where('application_id', $this->application_id)->first()
            ?? \App\Models\InspectionApplication::where('application_id', $this->application_id)->first()
            ?? \App\Models\ConsignmentApplication::where('application_id', $this->application_id)->first();

        $appData = [];
        if ($application) {
            $appData = [
                'type' => $application->application_type ?? 'Unknown',
                'status' => $application->status ?? '-',
                'category' => $application->category_application ?? '-',
                'importer' => is_array($application->importer_detail) 
                                ? ($application->importer_detail['fullname'] ?? '-') 
                                : '-',
                'eta' => $application->eta ? \Carbon\Carbon::parse($application->eta)->format('d M Y') : '-',
                'transport' => $application->transport_type ?? '-',
            ];
        }

        $logoPath = public_path('images/Logo-DOA.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        return new Content(
            view: 'email.application_approval',
            with: [
                'messageEn' => $this->messageEn,
                'user_fullname' => $this->user_fullname,
                'url' => $this->url,
                'application_id' => $this->application_id,
                'appData' => $appData,
                'logoBase64' => $logoBase64,
            ]
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

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
    public $messageBm;
    public $user_fullname;
    public $url;
    public $application_id;
    public $locale;

    /**
     * Create a new message instance.
     */
    public function __construct($messageEn, $messageBm, $user_fullname, $url, $application_id, $locale = 'en')
    {
        $this->messageEn = $messageEn;
        $this->messageBm = $messageBm;
        $this->user_fullname = $user_fullname;
        $this->url = $url;
        $this->application_id = $application_id;
        $this->locale = $locale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->locale === 'bm' 
            ? 'Permohonan Baru Dihantar: ' . $this->application_id
            : 'New Application Submitted: ' . $this->application_id;

        return new Envelope(
            subject: $subject,
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

        $viewName = $this->locale === 'bm' 
            ? 'email.application_approval_bm' 
            : 'email.application_approval_en';

        return new Content(
            view: $viewName,
            with: [
                'messageEn' => $this->messageEn,
                'messageBm' => $this->messageBm,
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

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\ApplicationApprovalMail;

class ApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $messageEn;
    public $messageBm;
    public $user;
    public $url;
    public $application_id;

    /**
     * Create a new notification instance.
     *
     * @param string $messageEn
     * @param string $messageBm
     * @param string $user
     * @param string|null $url
     * @param string|null $application_id
     */
    public function __construct($messageEn, $messageBm, $user, $url = null, $application_id = null)
    {
        $this->messageEn = $messageEn;
        $this->messageBm = $messageBm;
        $this->user = $user;
        $this->url = $url;
        $this->application_id = $application_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => [
                'en' => $this->messageEn,
                'bm' => $this->messageBm,
            ],
            'user' => $this->user,
            'url'  => $this->url,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new ApplicationApprovalMail(
            $this->messageEn,
            $this->messageBm,
            $this->user,
            $this->url,
            $this->application_id,
            app()->getLocale()
        ))->to($notifiable->email);
    }
}

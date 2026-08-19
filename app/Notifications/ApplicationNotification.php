<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationNotification extends Notification
{
    use Queueable;

    public $messageEn;
    public $messageBm;
    public $user;
    public $url;

    /**
     * Create a new notification instance.
     *
     * @param string $messageEn  English message
     * @param string $messageBm  Bahasa Malaysia message
     * @param string $user       Sender or related user name
     * @param string|null $url   Action URL
     */
    public function __construct($messageEn, $messageBm, $user, $url = null)
    {
        $this->messageEn = $messageEn;
        $this->messageBm = $messageBm;
        $this->user = $user;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

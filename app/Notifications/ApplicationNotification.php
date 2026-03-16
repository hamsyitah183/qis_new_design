<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $message;
    public $user;
    public $url;

    public function __construct($message, $user, $url = null)
    {
        $this->message = $message;
        $this->user = $user;
        $this->url = $url;
        // $this->time = $time;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => $this->message,
            'user'  => $this->user,
            'url' => $this->url,
        ];
    }


    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('ORIS - Application Notification')
            ->greeting('Hello, ' . ($notifiable->name ?? 'User') . '!')
            ->line($this->message);

        if ($this->url) {
            $mail->action('View Application', $this->url);
        }

        return $mail->line('Thank you for using the Online Road Information System (ORIS).');
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

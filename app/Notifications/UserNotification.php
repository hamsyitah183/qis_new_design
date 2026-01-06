<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public string $message;
    public string $user;
    public string $url;

    public function __construct(string $message, string $user, string $url = null)
    {
        $this->message = $message;
        $this->user = $user;
        $this->url = $url;
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    
    /**
     * Stored in database (Spatie / Laravel notifications)
     */
    public function toDatabase($notifiable): array
    {
        return [
            'message' => $this->message,
            'user'  => $this->user,
            'url' => $this->url,
        ];
    }
}

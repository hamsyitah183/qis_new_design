<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class InternalUserEditedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

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
     * Where the notification is delivered
     */
    public function via($notifiable): array
    {
        // return ['database', 'broadcast'];
        return ['database'];
    }

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

    /**
     * Broadcast payload
     */
    // public function toBroadcast($notifiable): BroadcastMessage
    // {
    //     return new BroadcastMessage([
    //         'message' => $this->message,
    //         'editor'  => $this->editorName,
    //     ]);
    // }

    /**
     * Custom broadcast name (important!)
     */
    // public function broadcastAs(): string
    // {
    //     return 'InternalUserEdited';
    // }
}

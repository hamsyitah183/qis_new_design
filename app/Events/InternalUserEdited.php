<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class InternalUserEdited extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
        ];
    }
}

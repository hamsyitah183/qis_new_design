<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationCreatedPublicUser
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public string $message;
    public string $publicUserUuid;

    public function __construct(string $message, string $publicUserUuid)
    {
        $this->message = $message;
        $this->publicUserUuid = $publicUserUuid;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('public-user.' . $this->publicUserUuid),
        ];
    }

     public function broadcastAs(): string
    {
        return 'ApplicationCreatedPublicUser';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'publicUserUuid' => $this->publicUserUuid,
        ];
    }
}

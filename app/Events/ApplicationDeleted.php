<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public string $message;
    /**
     * Create a new event instance.
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('internal-users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ApplicationDeleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}

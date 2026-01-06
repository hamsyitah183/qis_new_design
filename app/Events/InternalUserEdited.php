<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InternalUserEdited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $internalUserId;

    public function __construct($message, $internalUserId)
    {
        $this->message = $message;
        $this->internalUserId = $internalUserId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('internal-user-edited.' . $this->internalUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'InternalUserEdited';
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublicUserUpdatedForInternal implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

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
            new PrivateChannel('internal-users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PublicUserUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'public_user_uuid' => $this->publicUserUuid,
        ];
    }
}

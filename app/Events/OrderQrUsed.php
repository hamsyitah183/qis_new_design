<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderQrUsed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $orderNumber,
        public string $permitNumber,
        public string $usedAt,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('internal-users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderQrUsed';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->orderNumber,
            'permit_number' => $this->permitNumber,
            'used_at' => $this->usedAt,
        ];
    }
}

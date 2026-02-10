<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatId;
    public $userId;

    public function __construct(int $chatId, int $userId)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ChatEvent.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChatDeleted';
    }

    public function broadcastWith(): array
    {
        return [
            'chatId' => $this->chatId,
        ];
    }
}

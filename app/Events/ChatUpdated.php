<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use App\Http\Resources\ChatResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;
    public $user;

    public function __construct(Chat $chat, int $userId)
    {
        $this->chat = $chat;
        $this->user = User::find($userId);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ChatEvent.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChatUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'chat' => new ChatResource($this->chat->loadDefault($this->user)),
        ];
    }
}

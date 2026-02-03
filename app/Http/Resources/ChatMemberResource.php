<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'joined_at' => $this->created_at,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? new UserResource($this->user) : null;
            }),
        ];
    }
}

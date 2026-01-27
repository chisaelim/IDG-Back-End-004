<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_message' => $this->whenLoaded('messages', function () {
                return $this->messages->isNotEmpty() ? new ChatMessageResource($this->messages->first()) : null;
            }),
            'unread_count' => $this->when(isset($this->unread_count), $this->unread_count ?? 0),
        ];
    }
}

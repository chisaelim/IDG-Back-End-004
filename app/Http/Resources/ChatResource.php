<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // filtered to only the other user, grab the first one
        $other = null;
        if ($this->type === 'personal' && $this->relationLoaded('users')) {
            $other = $this->users->first();
        }
        return [
            'id' => $this->id,
            'name' => $other ? $other->name : $this->name,
            'photo' => $other ? $other->photo : $this->photo,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_message' => $this->whenLoaded('messages', function () {
                return $this->messages->isNotEmpty() ? new ChatMessageResource($this->messages->first()) : null;
            }),
            'unread_count' => $this->when(isset($this->unread_count), $this->unread_count ?? 0),
            'updatable' => (bool) $this->when(isset($this->updatable), $this->updatable ?? false),
        ];
    }
}

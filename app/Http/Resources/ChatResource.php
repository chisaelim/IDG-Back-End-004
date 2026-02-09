<?php

namespace App\Http\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $other = null;
        if ($this->type === 'personal' && $this->relationLoaded('users')) {
            $other = $this->users->where('id', '<>', $user->id)->first();
        }
        $updatable = (bool) ($this->type === 'group' && $this->hasAdmin($user->id, false));
        return [
            'id' => $this->id,
            'name' => $this->type === 'personal' && $other ? $other->name : $this->name,
            'photo' => $this->type === 'personal' && $other ? $other->photo : $this->photo,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_message' => $this->whenLoaded('messages', function () {
                return $this->messages->isNotEmpty() ? new ChatMessageResource($this->messages->first()) : null;
            }),
            'unread_count' => $this->when(isset($this->unread_count), $this->unread_count ?? 0),
            'updatable' => $updatable,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $content = $this->content;

        // Build full URL for non-text messages
        if ($this->type !== 'text') {
            $folder = $this->type . 's'; // images, videos, files
            $content = env('APP_URL') . "/api/chats/read/{$this->chat_id}/{$folder}/{$this->content}";
        }

        return [
            'id' => $this->id,
            'content' => $content,
            'originalContent' => $this->type !== 'text' ? $this->content : null,
            'type' => $this->type,
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'seen_at' => $this->seen_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? new UserResource($this->user) : null;
            }),
            // Flag if message is from current user
            'own_message' => $this->user_id === $request->user()->id,
        ];
    }
}

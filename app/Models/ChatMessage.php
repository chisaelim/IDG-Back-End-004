<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;
    protected $table = 'chat_messages';
    protected $primaryKey = 'id';
    protected $casts = [
        'seen_at' => 'datetime',
    ];
    protected $fillable = [
        'content',
        'type',
        'chat_id',
        'user_id',
        'seen_at'
    ];

    protected function Content(): Attribute
    {
        return Attribute::make(
            get: function (string|null $value) {
                if ($this->type === 'text' || !$value) {
                    return $value;
                }
                $folder = $this->type . 's'; // images, videos, files
                return env('APP_URL') . "/api/chats/read/{$this->chat_id}/{$folder}/{$value}";
            },
        );
        
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

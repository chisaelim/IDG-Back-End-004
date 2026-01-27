<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $table = 'chats';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'type',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function members()
    {
        return $this->hasMany(ChatMember::class);
    }

    public function users()
    {
        /**
         * Key mapping:
         * - User::class: The related model (User).
         * - ChatMember::class: The intermediate model (ChatMember).
         * - 'chat_id': Foreign key on the ChatMember table referencing the Chat.
         * - 'id': Local key on the Chat model.
         * - 'id': Local key on the User model.
         * - 'user_id': Foreign key on the ChatMember table referencing the User.
         */
        return $this->hasManyThrough(User::class, ChatMember::class, 'chat_id', 'id', 'id', 'user_id');
    }
}

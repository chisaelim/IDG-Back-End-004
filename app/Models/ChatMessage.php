<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

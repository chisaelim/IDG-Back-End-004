<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens, MustVerifyEmail;

    protected $appends = [
        'password_null',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'photo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function Photo(): Attribute
    {
        return Attribute::make(
            get: fn(string|null $value) => $value ?
                asset('storage/profile/' . $value) :
                null,
        );
    }

    function getPasswordNullAttribute(): bool
    {
        return empty($this->password);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function members()
    {
        return $this->hasMany(ChatMember::class);
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function hasChatAsAdmin($chatId, $throw = true): Chat
    {
        $chat = $this->chats()
            ->where('chat_id', $chatId)
            ->wherePivot('role', 'admin')
            ->first();
        if (!$chat && $throw) {
            throw new ModelNotFoundException('Chat not found or user is not an admin.');
        }
        return $chat;
    }

    public function hasChat($chatId, $throw = true) : Chat
    {
        $chat = $this->chats()
            ->where('chat_id', $chatId)
            ->first();
        if (!$chat && $throw) {
            throw new ModelNotFoundException('Chat not found.');
        }
        return $chat;
    }

    public function isChatMember($chatId, $throw = true) : ChatMember
    {
        $member = $this->members()
            ->where('chat_id', $chatId)
            ->first();
        if (!$member && $throw) {
            throw new ModelNotFoundException('You are not a member of this chat.');
        }
        return $member;
    }

    public function hasMessageInChat($messageId, $chatId, $throw = true) : ChatMessage
    {
        $message = $this->messages()
            ->where('id', $messageId)
            ->where('chat_id', $chatId)
            ->first();
        if (!$message && $throw) {
            throw new ModelNotFoundException('Chat message not found.');
        }
        return $message;
    }
}

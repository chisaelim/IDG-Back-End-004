<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Chat extends Model
{
    use HasFactory;
    protected $table = 'chats';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'photo',
        'type',
    ];

    protected function Photo(): Attribute
    {
        $other = $this->otherUser();
        return Attribute::make(
            get: function (string|null $value) use ($other) {
                if ($this->type === 'personal' && $other) {
                    $hasPhoto = $other->getRawOriginal('photo');
                    return $hasPhoto ? asset('storage/profile/' . $other->getRawOriginal('photo')) : null;
                }
                if ($value) {
                    return env('APP_URL') . "/api/chats/read/{$this->id}/images/" . $value;
                }
                return null;
            }
        );
    }

    protected function Name(): Attribute
    {
        $other = $this->otherUser();
        return Attribute::make(
            get: fn(string|null $value) => $value ? $value : ($other ? $other->name : 'Unnamed Chat')
        );
    }

    public function otherUser(): User|null
    {
        return $this->users()
            ->where('user_id', '<>', request()->user()->id)
            ->first();
    }

    protected function scopeWithDefault(Builder $query, $user): void
    {
        $query->with(['messages' => function ($query) {
            $query->latest()->limit(1)->with('user');
        }])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('user_id', '<>', $user->id)
                    ->whereNull('seen_at');
            }]);
    }

    public function loadDefault($user)
    {
        $this->load(['messages' => function ($query) {
            $query->latest()->limit(1)->with('user');
        }]);

        $this->loadCount(['messages as unread_count' => function ($query) use ($user) {
            $query->where('user_id', '<>', $user->id)
                ->whereNull('seen_at');
        }]);

        return $this;
    }

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

    public function mainAdmin(): ChatMember
    {
        return $this->members()->where('role', 'admin')->orderBy('created_at', 'asc')->first();
    }

    public function hasMember($memberId): ChatMember
    {
        $member = $this->members()
            ->where('user_id', $memberId)
            ->first();
        if (!$member) {
            throw new ModelNotFoundException('Member not found in this chat.');
        }
        return $member;
    }
}

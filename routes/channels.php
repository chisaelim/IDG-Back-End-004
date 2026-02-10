<?php

use Illuminate\Support\Facades\Broadcast;

// Message channel - authorized if user is a member of the chat
Broadcast::channel('MessageEvent.{chatId}', function ($user, $chatId) {
    try {
        return (bool) $user->isChatMember($chatId);
    } catch (\Exception $e) {
        return false;
    }
});

// Chat channel - authorized if user is the channel owner
Broadcast::channel('ChatEvent.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
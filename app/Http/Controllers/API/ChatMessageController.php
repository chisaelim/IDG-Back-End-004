<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessageResource;
use App\Http\Requests\ChatMessage\SendMessageRequest;
use App\Http\Requests\ChatMessage\UpdateMessageRequest;

class ChatMessageController extends Controller
{
    public function getMessages(Request $request, int $chatId)
    {
        $user = $request->user();

        // Verify user is member of this chat
        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not found or you are not a member'
            ], 404);
        }

        $messages = ChatMessage::where('chat_id', $chatId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response([
            'messages' => ChatMessageResource::collection($messages),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }
    public function createMessage(SendMessageRequest $request, int $chatId)
    {
        $user = $request->user();
        $data = $request->validated();

        // Verify user is member of this chat
        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not found or you are not a member'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $message = ChatMessage::create([
                'chat_id' => $chatId,
                'user_id' => $user->id,
                'content' => $data['content'],
                'type' => $data['type'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Message sent successfully',
            'data' => new ChatMessageResource($message->load('user'))
        ], 201);
    }
    public function updateMessage(UpdateMessageRequest $request, int $chatId, int $messageId)
    {
        $user = $request->user();
        $data = $request->validated();
        $message = ChatMessage::where('id', $messageId)
            ->where('chat_id', $chatId)
            ->where('user_id', $user->id)
            ->first();

        if (!$message) {
            return response([
                'message' => 'Message not found or you cannot edit this message'
            ], 404);
        }
        if ($message->type !== 'text') {
            return response([
                'message' => 'Only text messages can be edited'
            ], 400);
        }

        try {
            DB::beginTransaction();
            $message->update($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([

            'message' => 'Message updated successfully',
            'data' => new ChatMessageResource($message->load('user'))
        ]);
    }
    public function deleteMessage(Request $request, int $chatId, int $messageId)
    {
        $user = $request->user();

        $message = ChatMessage::where('id', $messageId)
            ->where('chat_id', $chatId)
            ->where('user_id', $user->id)
            ->first();
        if (!$message) {
            return response([
                'message' => 'Message not found or you cannot delete this message'
            ], 404);
        }
        try {
            DB::beginTransaction();
            $message->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Message deleted successfully'
        ]);
    }
    public function markMessageAsSeen(Request $request, int $chatId, int $messageId)
    {
        $user = $request->user();

        // Verify user is member of this chat
        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not found or you are not a member'
            ], 404);
        }

        $message = ChatMessage::where('id', $messageId)
            ->where('chat_id', $chatId)
            ->where('user_id', '<>', $user->id) // Can't marek own message as seen
            ->whereNull('seen_at')
            ->first();

        if (!$message) {
            return response([
                'message' => 'Message not found or already seen'
            ], 404);
        }
        try {
            DB::beginTransaction();
            $message->update(['seen_at' => now()]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Message marked as seen',
            'data' => new ChatMessageResource($message->load('user'))
        ]);
    }
    public function markAllMessagesAsSeen(Request $request, int $chatId)
    {
        $user = $request->user();

        // Verify user is member of this chat
        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not found or you are not a member'
            ], 404);
        }
        try {
            DB::beginTransaction();
            $updatedCount = ChatMessage::where('chat_id', $chatId)
                ->where('user_id', '<>', $user->id)
                ->whereNull('seen_at')
                ->update(['seen_at' => now()]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([

            'message' => 'All messages marked as seen',
            'data' => [
                'updated_count' => $updatedCount
            ]
        ]);
    }
}

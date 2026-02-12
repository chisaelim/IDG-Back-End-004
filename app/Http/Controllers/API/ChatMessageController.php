<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Chat;
use App\Events\ChatUpdated;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Events\MessageCreated;
use App\Events\MessageDeleted;
use App\Events\MessageUpdated;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ChatMessageResource;
use App\Http\Requests\ChatMessage\SendMessageRequest;
use App\Http\Requests\ChatMessage\UpdateMessageRequest;

class ChatMessageController extends Controller
{
    public function getMessages(Request $request, int $chatId)
    {
        $user = $request->user();
        $search = $request->get('search');

        $user->isChatMember($chatId);

        $messages = ChatMessage::where('chat_id', $chatId)
            ->when($search, function ($query, $search) {
                $query->where('type', 'text')
                    ->where('content', 'like', "%{$search}%");
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response([
            'chat_messages' => ChatMessageResource::collection($messages),
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

        $user->isChatMember($chatId);

        try {
            DB::beginTransaction();

            $content = $data['content'];

            // Handle file upload for non-text messages
            if ($data['type'] !== 'text') {
                $file = $data['content'];
                $extension = $file->getClientOriginalExtension();
                $filename = strtoupper($data['type']) . '-' . uniqid() . '.' . $extension;
                $folder = "chats/{$chatId}/{$data['type']}s";

                Storage::disk('local')->putFileAs($folder, $file, $filename);
                $content = $filename;
            }

            $message = ChatMessage::create([
                'chat_id' => $chatId,
                'user_id' => $user->id,
                'content' => $content,
                'type' => $data['type'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        broadcast(new MessageCreated($message))->toOthers();
        $userIds = Chat::find($chatId)->members()->pluck('user_id')->toArray();
        foreach ($userIds as $userId) {
            broadcast(new ChatUpdated($message->chat()->first(), $userId));
        }
        return response([
            'message' => 'Message sent successfully',
            'chat_message' => new ChatMessageResource($message->load('user'))
        ], 201);
    }
    public function updateMessage(UpdateMessageRequest $request, int $chatId, int $messageId)
    {
        $user = $request->user();
        $data = $request->validated();
        $message = $user->hasMessageInChat($messageId, $chatId);
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

        broadcast(new MessageUpdated($message))->toOthers();
        $userIds = Chat::find($chatId)->members()->pluck('user_id')->toArray();
        foreach ($userIds as $userId) {
            broadcast(new ChatUpdated($message->chat()->first(), $userId));
        }
        return response([
            'message' => 'Message updated successfully',
            'chat_message' => new ChatMessageResource($message->load('user'))
        ]);
    }
    public function deleteMessage(Request $request, int $chatId, int $messageId)
    {
        $user = $request->user();

        $message = $user->hasMessageInChat($messageId, $chatId);
        try {
            DB::beginTransaction();

            // Remove file from disk for non-text messages
            if ($message->type !== 'text') {
                $folder = "chats/{$chatId}/{$message->type}s";
                Storage::disk('local')->delete("{$folder}/{$message->content}");
            }

            $message->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        broadcast(new MessageDeleted($message->id, $chatId))->toOthers();
        $userIds = Chat::find($chatId)->members()->pluck('user_id')->toArray();
        foreach ($userIds as $userId) {
            broadcast(new ChatUpdated($message->chat()->first(), $userId));
        }

        return response([
            'message' => 'Message deleted successfully'
        ]);
    }
    public function markMessageAsSeen(Request $request, int $chatId, int $messageId)
    {
        $user = $request->user();

        $user->isChatMember($chatId);

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
            $message->timestamps = false; // Disable automatic timestamp update
            $message->update(['seen_at' => now()]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Message marked as seen',
            'chat_message' => new ChatMessageResource($message->load('user'))
        ]);
    }
    public function markAllMessagesAsSeen(Request $request, int $chatId)
    {
        $user = $request->user();

        $user->isChatMember($chatId);

        try {
            DB::beginTransaction();
            $messages = ChatMessage::where('chat_id', $chatId)
                ->where('user_id', '<>', $user->id) // Can't marek own messages as seen
                ->whereNull('seen_at')->get();
            $messages->each(function ($message) {
                $message->timestamps = false;
                $message->update(['seen_at' => now()]);
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'All messages marked as seen',
        ]);
    }
}

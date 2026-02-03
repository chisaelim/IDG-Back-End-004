<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Chat;
use App\Models\ChatMember;
use App\Traits\UploadMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Chat\CreateChatRequest;
use App\Http\Requests\Chat\UpdateChatRequest;

class ChatController extends Controller
{
    public function getChats(Request $request)
    {
        $user = $request->user();
        $search = $request->get('search');

        $chats = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withDefault($user)
            ->orderByDesc(DB::raw('(SELECT MAX(created_at) FROM chat_messages WHERE chat_messages.chat_id = chats.id)'))
            ->paginate($request->get('per_page', 15));

        return response([
            'chats' => ChatResource::collection($chats),
            'meta' => [
                'current_page' => $chats->currentPage(),
                'last_page' => $chats->lastPage(),
                'per_page' => $chats->perPage(),
                'total' => $chats->total(),
            ]
        ], 200);
    }
    public function createChat(CreateChatRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        // For personal chat, check if chat already exists between these two users
        if ($data['type'] === 'personal') {
            $exists =  Chat::where('type', 'personal')
                ->whereHas('members', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('members', function ($query) use ($data) {
                    $query->where('user_id', $data['user_ids'][0]);
                })
                ->withDefault($user)
                ->first();
            if ($exists) {
                return response([
                    'message' => 'Chat already exists',
                    'chat' => new ChatResource($exists)
                ], 200);
            }
        }
        try {
            DB::beginTransaction();
            // Create chat
            $chat = Chat::create([
                'name' => $data['name'] ?? null,
                'type' => $data['type'],
            ]);

            if ($data['type'] === 'group' && isset($data['photo'])) {
                // Store group chat photo
                $chat->photo = UploadMethod::storeImage($data['photo'], "chats/{$chat->id}/images", true);
                $chat->save();
            }

            // Add creator as admin
            ChatMember::create([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
                'role' => 'admin',
            ]);

            // Add other members
            if (!empty($data['user_ids'])) {
                foreach ($data['user_ids'] as $userId) {
                    if ($userId != $user->id) {
                        ChatMember::create([
                            'chat_id' => $chat->id,
                            'user_id' => $userId,
                            'role' => 'member',
                        ]);
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        $chat->loadDefault($user);
        return response([
            'message' => 'Chat created successfully',
            'chat' => new ChatResource($chat)
        ], 201);
    }
    public function readChat(Request $request, int $chatId)
    {
        $user = $request->user();

        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->withDefault($user)
            ->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not foun!d'
            ], 404);
        }

        return response([
            'chat' => new ChatResource($chat)
        ], 200);
    }
    public function updateGroupChat(UpdateChatRequest $request, int $chatId)
    {
        $user = $request->user();
        $data = $request->validated();
        $chat = $user->hasChatAsAdmin($chatId);
        if ($chat->type === 'personal') {
            return response([
                'message' => 'Cannot update personal chat'
            ], 400);
        }

        try {
            DB::beginTransaction();
            if (array_key_exists('photo', $data)) {
                UploadMethod::discardImage($chat->getRawOriginal('photo'), "chats/{$chat->id}/images", true);
                if (!empty($data['photo'])) {
                    $data['photo'] = UploadMethod::storeImage($data['photo'], "chats/{$chat->id}/images", true);
                }
            }
            $chat->update($data);
            DB::commit();
            $chat->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        $chat->loadDefault($user);
        return response([
            'message' => 'Chat updated successfully',
            'chat' => new ChatResource($chat)
        ]);
    }
    public function deleteGroupChat(Request $request, int $chatId)
    {
        $user = $request->user();

        $chat = $user->hasChatAsAdmin($chatId);
        try {
            DB::beginTransaction();

            // Delete all messages
            $chat->messages()->delete();

            // Delete all members
            $chat->members()->delete();

            // Delete chat
            $chat->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Chat deleted successfully'
        ]);
    }
    public function leaveGroupChat(Request $request, int $chatId)
    {
        $user = $request->user();
        $member = $user->isChatMember($chatId);
        try {
            DB::beginTransaction();

            $chat = Chat::find($chatId);

            // If admin is leaving a group chat, assign another admin
            if ($member->role === 'admin' && $chat->type === 'group') {
                $newAdmin = ChatMember::where('chat_id', $chatId)
                    ->where('user_id', '<>', $user->id)
                    ->first();

                if ($newAdmin) {
                    $newAdmin->update(['role' => 'admin']);
                }
            }

            $member->delete();

            // Delete chat if no members left
            $remainingMembers = ChatMember::where('chat_id', $chatId)->count();
            if ($remainingMembers === 0) {
                $chat->messages()->delete();
                $chat->delete();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Left chat successfully'
        ]);
    }
    function readChatFile(Request $request, int $chatId, string $folder, string $filename)
    {
        $user = $request->user();
        $user->isChatMember($chatId);

        $disk = Storage::disk('local');

        $filePath = "chats/{$chatId}/{$folder}/{$filename}";

        if (!$disk->exists($filePath)) {
            return response([
                'message' => 'File not found.'
            ], 404);
        }

        $mimeType = $disk->mimeType($filePath);
        $content = $disk->get($filePath);

        return response($content)
            ->header('Content-Type', $mimeType);
    }
}

<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Chat;
use App\Models\ChatMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Requests\Chat\CreateChatRequest;
use App\Http\Requests\Chat\UpdateChatRequest;

class ChatController extends Controller
{
    public function getChats(Request $request)
    {
        $user = $request->user();

        $chats = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->has('messages')
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1)->with('user');
            }])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('user_id', '<>', $user->id)
                    ->whereNull('seen_at');
            }])
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
                ->with(['messages' => function ($query) {
                    $query->latest()->limit(1)->with('user');
                }])
                ->withCount(['messages as unread_count' => function ($query) use ($user) {
                    $query->where('user_id', '<>', $user->id)
                        ->whereNull('seen_at');
                }])
                ->first();
            if ($exists) {
                return response([
                    'message' => 'Chat already exists',
                    'data' => new ChatResource($exists)
                ], 200);
            }
        }
        try {
            DB::beginTransaction();
            // Create chat
            $chat = Chat::create([
                'name' => $data['name'],
                'type' => $data['type'],
            ]);

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
        $chat->load(['messages' => function ($query) {
            $query->latest()->limit(1)->with('user');
        }]);
        $chat->loadCount(['messages as unread_count' => function ($query) use ($user) {
            $query->where('user_id', '<>', $user->id)
                ->whereNull('seen_at');
        }]);
        return response([
            'message' => 'Chat created successfully',
            'data' => new ChatResource($chat)
        ], 201);
    }
    public function readChat(Request $request, int $chatId)
    {
        $user = $request->user();

        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1)->with('user');
            }])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('user_id', '<>', $user->id)
                    ->whereNull('seen_at');
            }])
            ->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not foun!d'
            ], 404);
        }

        return response([
            'data' => new ChatResource($chat)
        ], 200);
    }
    public function updateGroupChat(UpdateChatRequest $request, int $chatId)
    {
        $user = $request->user();
        $data = $request->validated();
        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('role', 'admin');
        })->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not found or you are not an admin'
            ], 403);
        }

        if ($chat->type === 'personal') {
            return response([
                'message' => 'Cannot update personal chat'
            ], 400);
        }

        try {
            DB::beginTransaction();
            $chat->update($data);
            DB::commit();
            $chat->refresh();
            $chat->load(['messages' => function ($query) {
                $query->latest()->limit(1)->with('user');
            }]);
            $chat->loadCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('user_id', '<>', $user->id)
                    ->whereNull('seen_at');
            }]);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Chat updated successfully',
            'data' => new ChatResource($chat)
        ]);
    }
    public function deleteGroupChat(Request $request, int $chatId)
    {
        $user = $request->user();

        $chat = Chat::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('role', 'admin');
        })->find($chatId);

        if (!$chat) {
            return response([
                'message' => 'Chat not found or you are not an admin'
            ], 403);
        }

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

        $member = ChatMember::where('chat_id', $chatId)
            ->where('user_id', $user->id)
            ->first();

        if (!$member) {
            return response([
                'message' => 'You are not a member of this chat'
            ], 404);
        }

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
}

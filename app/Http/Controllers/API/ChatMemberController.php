<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Chat;
use App\Models\ChatMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMemberResource;
use App\Http\Requests\ChatMember\AddMemberRequest;
use App\Http\Requests\ChatMember\UpdateMemberRequest;

class ChatMemberController extends Controller
{
    public function getMembers(Request $request, int $chatId)
    {
        $user = $request->user();
        $search = $request->get('search');

        $user->isChatMember($chatId);

        $members = ChatMember::where('chat_id', $chatId)
            ->where('user_id', '<>', $user->id)
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with('user')
            ->paginate($request->get('per_page', 15));

        return response([
            'chat_members' => ChatMemberResource::collection($members),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ]
        ]);
    }
    public function addMember(AddMemberRequest $request, int $chatId)
    {
        $user = $request->user();
        $data = $request->validated();

        $chat = $user->hasChatAsAdmin($chatId);

        if ($chat->type === 'personal') {
            return response([
                'message' => 'Cannot add members to personal chat'
            ], 400);
        }

        try {
            DB::beginTransaction();
            $member = ChatMember::firstOrCreate([
                'chat_id' => $chatId,
                'user_id' => $data['user_id'],
            ], [
                'role' => 'member',
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Member added successfully',
            'chat_member' => new ChatMemberResource($member->load('user'))
        ], 201);
    }
    public function updateMember(UpdateMemberRequest $request, int $chatId, int $memberId)
    {
        $user = $request->user();
        $data = $request->validated();
        $chat = $user->hasChatAsAdmin($chatId);
        $member = $chat->hasMember($memberId);

        // Prevent changing own role
        if ($member->user_id === $user->id) {
            return response([
                'message' => 'You cannot change your own role'
            ], 400);
        }
        // prevent update on main admin
        if ($member->user_id === $chat->mainAdmin()->id) {
            return response([
                'message' => 'You cannot change the role of the main admin'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $member->update($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([

            'message' => 'Member role updated successfully',
            'chat_member' => new ChatMemberResource($member->load('user'))
        ]);
    }
    public function removeMember(Request $request, int $chatId, int $memberId)
    {
        $user = $request->user();
        $chat = $user->hasChatAsAdmin($chatId);
        $member = $chat->hasMember($memberId);

        // Prevent removing self
        if ($member->user_id === $user->id) {
            return response([
                'message' => 'You cannot remove yourself. Use leave endpoint instead'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $member->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return response([
            'message' => 'Member removed successfully'
        ]);
    }
}

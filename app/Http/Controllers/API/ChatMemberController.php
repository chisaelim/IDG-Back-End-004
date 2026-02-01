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
use App\Http\Requests\ChatMember\UpdateMemberRoleRequest;

class ChatMemberController extends Controller
{
    public function getMembers(Request $request, int $chatId)
    {
        $user = $request->user();

        $user->isChatMember($chatId);

        $members = ChatMember::where('chat_id', $chatId)
            ->with('user')
            ->get();

        return response([
            'data' => ChatMemberResource::collection($members)
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
            'data' => new ChatMemberResource($member->load('user'))
        ], 201);
    }
    public function updateMember(UpdateMemberRoleRequest $request, int $chatId, int $memberId)
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
            'data' => new ChatMemberResource($member->load('user'))
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

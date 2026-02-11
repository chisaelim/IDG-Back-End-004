<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Manage\DetailUserResource;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        $user = $request->user();
        $search = $request->get('search');

        $users = User::where('id', '<>', $user->id)
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->get('per_page', 15));

        return response([
            'users' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ], 200);
    }


    public function getDetailUsers(Request $request)
    {
        $user = $request->user();
        $search = $request->get('search');
        $perPage = $request->get('per_page');

        $query = User::when($search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        })->where('id', '<>', $user->id)
            ->where('level', '<>', 'admin');

        $users = $query->paginate($perPage ?: $query->count());

        return response([
            'data' => DetailUserResource::collection($users),
            'meta' => collect($users->toArray())->except('data')->toArray()
        ], 200);
    }

    function readDetailUser(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('id', $id)->where('id', '<>', $user->id)
            ->where('level', '<>', 'admin')->firstOrFail();
        return response([
            'user' => new DetailUserResource($targetUser)
        ], 200);
    }

    function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|max:10'
        ]);
        try {
            $request->merge(['password' => Hash::make($request->password), 'email_verified_at' => now(), 'level' => 'user']);
            $user = User::create($request->only('name', 'email', 'password', 'email_verified_at', 'level'));
        } catch (Exception $e) {
            return response([
                'message' => 'Failed to create user'
            ], 500);
        }
        return response([
            'message' => 'User created.',
            'user' => new DetailUserResource($user)
        ], 201);
    }

    function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|max:10'
        ]);

        $user = $request->user();
        $targetUser = User::where('id', $id)->where('id', '<>', $user->id)
            ->where('level', '<>', 'admin')->firstOrFail();
        try {
            DB::beginTransaction();
            if ($request->filled('password')) {
                $request->merge(['password' => Hash::make($request->password)]);
            } else {
                $request->request->remove('password');
            }

            // Fill the model to check for changes
            $targetUser->fill($request->only('name', 'email', 'password'));

            if ($targetUser->isDirty()) {
                $targetUser->tokens()->delete(); // Invalidate all tokens on any field change
            }

            $targetUser->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Failed to update user'
            ], 500);
        }
        return response([
            'message' => 'User updated.',
            'user' => new DetailUserResource($targetUser)
        ], 200);
    }

    function deleteUser(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('id', $id)->where('id', '<>', $user->id)
            ->where('level', '<>', 'admin')->firstOrFail();
        try {
            DB::beginTransaction();
            $targetUser->tokens()->delete(); // Invalidate all tokens of the user before deletion
            $targetUser->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Failed to delete user'
            ], 500);
        }

        return response([
            'message' => 'User deleted.'
        ], 200);
    }
}

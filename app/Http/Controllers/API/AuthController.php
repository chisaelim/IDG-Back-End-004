<?php

namespace App\Http\Controllers\API;

use Hash;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    function signup(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|max:10|confirmed'
        ]);

        $user = User::create($fields);

        return response([
            'message' => 'User created.',
            'user' => $user
        ], 201);
    }
    function signin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();


        if (empty($user)) {
            throw ValidationException::withMessages([
                'email' => 'Email does not exist.',
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password does not match.',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'message' => 'User signed in.',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    function signout(Request $request)
    {
        $user = $request->user();

        // method 1
        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', $currentToken->id)->delete();

        // method 2
        // $user->currentAccessToken()->delete();
        
        return response([
            'message' => 'User signed out.'
        ], 200);
    }
}

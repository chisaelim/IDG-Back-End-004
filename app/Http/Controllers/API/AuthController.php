<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}

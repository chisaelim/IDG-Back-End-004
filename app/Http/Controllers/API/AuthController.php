<?php

namespace App\Http\Controllers\API;

use Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
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

        try {
            DB::beginTransaction();

            $user = User::create($fields);

            $user->sendEmailVerificationNotification();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'User creation failed.',
                'error' => $e->getMessage()
            ], 500);
        }

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

        if (!$user->hasVerifiedEmail()) {
            return response([
                'message' => 'Your email address is not verified.'
            ], 403);
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
    function verifyEmail(Request $request)
    {
        $userID = $request->route('id');
        $user = User::findOrFail($userID);

        if (empty($user)) {
            return response([
                'message' => 'User not found.'
            ], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response([
                'message' => 'Email already verified.'
            ], 200);
        }

        $user->markEmailAsVerified();

        return response([
            'message' => 'Email verified successfully.'
        ], 200);
    }
    function resendVerificationMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            throw ValidationException::withMessages([
                'email' => 'Email does not exist.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response([
                'message' => 'Email already verified.'
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response([
            'message' => 'Verification email resent.'
        ], 200);
    }

    function sendResetPasswordMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            throw ValidationException::withMessages([
                'email' => 'Email does not exist.',
            ]);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response([
                'message' => 'Password reset link sent to your email'
            ], 200);
        }

        return response([
            'message' => 'Password reset link sent to your email'
        ], 200);
    }

    function setNewPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:10|confirmed'
        ]);

        #####// method 1 : using Password facade
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response([
                'message' => 'Password has been reset successfully.'
            ], 200);
        }

        #####// method 2 : manual implementation
        // 1. Find the user by email
        // $user = User::where('email', $request->email)->first();
        // if (!$user) {
        //     return response([
        //         'message' => 'User not found.'
        //     ], 404);
        // }

        // // 2. Check if the token is valid and not expired
        // $record = DB::table('password_resets')->where('email', $request->email)->first();
        // if (!$record) {
        //     return response([
        //         'message' => 'Invalid or expired token.'
        //     ], 400);
        // }

        // // Check token match (Laravel hashes the token in the DB)
        // if (!Hash::check($request->token, $record->token)) {
        //     return response([
        //         'message' => 'Invalid or expired token.'
        //     ], 400);
        // }

        // // Check expiration (default: 60 minutes)
        // $expires = 60 * 60; // 60 minutes in seconds
        // if (strtotime($record->created_at) + $expires < time()) {
        //     return response([
        //         'message' => 'Token has expired.'
        //     ], 400);
        // }

        // // 3. Hash and update the password
        // $user->forceFill([
        //     'password' => Hash::make($request->password)
        // ])->setRememberToken(Str::random(60));
        // $user->save();

        // // 4. Invalidate the token
        // DB::table('password_resets')->where('email', $request->email)->delete();


        return response([
            'message' => 'Failed to reset password.'
        ], 500);
    }
}

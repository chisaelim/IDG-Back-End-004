<?php

namespace App\Http\Controllers\API;

use Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\UploadMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use UploadMethod;
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
            throw new \Exception($e->getMessage());
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
            throw ValidationException::withMessages([
                'email' => 'Email is not verified.',
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
    function verifyEmail(Request $request)
    {
        $userID = $request->route('id');
        $user = User::findOrFail($userID);

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

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'password' => [__($status)],
            ]);
        }

        return response([
            'message' => 'Password has been reset successfully.'
        ], 200);
    }
    function refreshToken(Request $request)
    {
        $user = $request->user();

        // method 1
        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', $currentToken->id)->delete();

        // method 2
        // $user->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'message' => 'Token refreshed successfully.',
            'token' => $token
        ], 200);
    }
    function verifyAccount(Request $request)
    {
        $user = $request->user();

        return response([
            'message' => 'Account is valid.',
            'user' => $user
        ], 200);
    }

    function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:10|confirmed',
            'terminate_sessions' => 'required|boolean'
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => 'Old password does not match.',
            ]);
        }
        try {
            DB::beginTransaction();
            $user->password = Hash::make($request->new_password);
            $user->save();

            if ($request->terminate_sessions) {
                // delete all tokens
                $user->tokens()->delete();
            } else {
                // delete current token only
                $currentToken = $user->currentAccessToken();
                $user->tokens()->where('id', $currentToken->id)->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }

        return response([
            'message' => 'Password changed successfully.'
        ], 200);
    }

    function createPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|max:10|confirmed',
            'terminate_sessions' => 'required|boolean'
        ]);

        $user = $request->user();

        try {
            DB::beginTransaction();
            $user->password = Hash::make($request->new_password);
            $user->save();

            if ($request->terminate_sessions) {
                // delete all tokens
                $user->tokens()->delete();
            } else {
                // delete current token only
                $currentToken = $user->currentAccessToken();
                $user->tokens()->where('id', $currentToken->id)->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }

        return response([
            'message' => 'Password created successfully.'
        ], 200);
    }
    function updateUserPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'nullable|base64image|base64mimes:png,jpg,jpeg|base64dimensions:width=454,height=453'
        ]);

        $user = $request->user();

        try {
            DB::beginTransaction();
            UploadMethod::discardImage($user->getRawOriginal('photo'), 'profile');
            $user->photo = null;
            if (!empty($request->photo)) {
                $user->photo = UploadMethod::storeImage($request->photo, 'profile');
            }
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }

        return response([
            'message' => 'User photo updated successfully.',
            'photo' => $user->photo
        ], 200);
    }
}

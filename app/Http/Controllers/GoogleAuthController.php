<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find or create user by email only
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'email_verified_at' => now(),
                ]
            );

            $token = $user->createToken('auth_token', ['*'], now()->addMinute())->plainTextToken;

            // Redirect to frontend with token
            return redirect()->away(
                env('APP_FRONTEND_URL') . '/auth/google/callback?token=' . $token
            );
        } catch (\Exception $e) {
            return redirect()->away(
                env('APP_FRONTEND_URL') . '/auth/google/callback/error'
            );
        }
    }
}

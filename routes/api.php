<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\GoogleAuthController;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/signout', [AuthController::class, 'signout'])->middleware('auth:sanctum');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verify.email');
Route::post('/email/verify/resend', [AuthController::class, 'resendVerificationMail'])->middleware('throttle:3,1');
Route::post('/password/forgot', [AuthController::class, 'sendResetPasswordMail']);
Route::post('/password/reset', [AuthController::class, 'setNewPassword'])->name('reset.password');
Route::patch('token/refresh', [AuthController::class, 'refreshToken'])->middleware('auth:sanctum');

// Google OAuth routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

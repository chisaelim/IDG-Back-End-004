<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\API\BackupController;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verify.email');
Route::post('/email/verify/resend', [AuthController::class, 'resendVerificationMail'])->middleware('throttle:3,1');
Route::post('/password/forgot', [AuthController::class, 'sendResetPasswordMail']);
Route::post('/password/reset', [AuthController::class, 'setNewPassword'])->name('reset.password');
// Google OAuth routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/signout', [AuthController::class, 'signout']);
    Route::patch('/token/refresh', [AuthController::class, 'refreshToken']);
    Route::get('/verify/account', [AuthController::class, 'verifyAccount']);
    Route::patch('/password/change', [AuthController::class, 'changePassword']);
    Route::patch('/password/create', [AuthController::class, 'createPassword']);
    Route::patch('/update/photo', [AuthController::class, 'updateUserPhoto']);

    Route::middleware('admin')->group(function () {
        Route::prefix('backups')->group(function () {
            Route::get('/', [BackupController::class, 'getBackups']);
            Route::post('/create', [BackupController::class, 'createBackup']);
            Route::get('/download/{filename}', [BackupController::class, 'downloadBackup']);
            Route::delete('/delete/{filename}', [BackupController::class, 'deleteBackup']);
        });
    });
});

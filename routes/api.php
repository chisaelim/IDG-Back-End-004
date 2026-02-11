<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BackupController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\API\ChatMemberController;
use App\Http\Controllers\API\ChatMessageController;

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
        Route::prefix('manage')->group(function () {
            Route::prefix('users')->group(function () {
                Route::get('/', [UserController::class, 'getDetailUsers']);
                Route::get('/read/{id}', [UserController::class, 'readDetailUser']);
                Route::post('/create', [UserController::class, 'createUser']);
                Route::put('/update/{id}', [UserController::class, 'updateUser']);
                Route::delete('/delete/{id}', [UserController::class, 'deleteUser']);
            });
        });
    });

    // User API Routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'getUsers']);
    });
    // Chat API Routes
    Route::prefix('chats')->group(function () {
        // Chat management
        Route::get('/', [ChatController::class, 'getChats']);
        Route::post('/create', [ChatController::class, 'createChat']);
        Route::get('/read/{chatId}', [ChatController::class, 'readChat']);
        Route::patch('/update/{chatId}', [ChatController::class, 'updateGroupChat']);
        Route::delete('/delete/{chatId}', [ChatController::class, 'deleteGroupChat']);
        Route::post('/leave/{chatId}', [ChatController::class, 'leaveGroupChat']);
        Route::get('/read/{chatId}/{folder}/{filename}', [ChatController::class, 'readChatFile']);


        // Chat messages
        Route::get('/{chatId}/messages', [ChatMessageController::class, 'getMessages']);
        Route::post('/{chatId}/messages/create', [ChatMessageController::class, 'createMessage']);
        Route::patch('/{chatId}/messages/update/{messageId}', [ChatMessageController::class, 'updateMessage']);
        Route::delete('/{chatId}/messages/delete/{messageId}', [ChatMessageController::class, 'deleteMessage']);
        Route::post('/{chatId}/messages/seen/{messageId}', [ChatMessageController::class, 'markMessageAsSeen']);
        Route::post('/{chatId}/messages/seen-all', [ChatMessageController::class, 'markAllMessagesAsSeen']);

        // Chat members
        Route::get('/{chatId}/members', [ChatMemberController::class, 'getMembers']);
        Route::post('/{chatId}/members/add', [ChatMemberController::class, 'addMember']);
        Route::patch('/{chatId}/members/update/{memberId}', [ChatMemberController::class, 'updateMember']);
        Route::delete('/{chatId}/members/remove/{memberId}', [ChatMemberController::class, 'removeMember']);
    });


});

<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);

Route::post('/signout', [AuthController::class, 'signout'])
    ->middleware('auth:sanctum');

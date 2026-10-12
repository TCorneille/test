<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RestrictTo;

// Public Auth Routes
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);
Route::post('/resetPassword', [AuthController::class, 'resetPassword']);

// Protected Routes (Requires Bearer / Cookie Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Password Actions
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/updateMyPassword', [AuthController::class, 'updatePassword']);

    // User Self Actions
    Route::get('/users/me', [UserController::class, 'getMe']);
    Route::patch('/users/updateMe', [UserController::class, 'updateMe']);
    Route::delete('/users/deleteMe', [UserController::class, 'deleteMe']);

    // Admin / Lead-Guide Restricted Routes
    Route::middleware(RestrictTo::class . ':admin,lead-guide')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});
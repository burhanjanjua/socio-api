<?php

use App\Http\Controllers\Api\AdminPostController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserPostController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('admin/login', [AuthController::class, 'adminLogin']);
});

Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post:slug}', [PostController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::prefix('me')->group(function () {
        Route::get('posts', [UserPostController::class, 'index']);
        Route::post('posts', [UserPostController::class, 'store']);
        Route::patch('posts/{post}', [UserPostController::class, 'update']);
        Route::delete('posts/{post}', [UserPostController::class, 'destroy']);
    });
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('posts', [AdminPostController::class, 'index']);
    Route::post('posts', [AdminPostController::class, 'store']);
    Route::patch('posts/{post}', [AdminPostController::class, 'update']);
    Route::delete('posts/{post}', [AdminPostController::class, 'destroy']);
});

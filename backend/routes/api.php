<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Welcome to Pinoy Music & Media Hub API!'
    ]);
});

// Auth (public)
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

// OPM Playlist API Routes (public reads)
Route::get('/songs', [SongController::class, 'index']);
Route::get('/songs/stats', [SongController::class, 'stats']);
Route::get('/songs/{id}', [SongController::class, 'show']);

// Movie Hub API Routes (public read)
Route::get('/movies', [MovieController::class, 'index']);

// Authenticated routes
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::patch('/songs/{id}/favorite', [SongController::class, 'toggleFavorite']);
    Route::patch('/songs/{id}/playlist', [SongController::class, 'togglePlaylist']);

    Route::patch('/movies/{id}/favorite', [MovieController::class, 'toggleFavorite']);
});

// Admin-only routes
Route::middleware(['auth:sanctum', 'can:admin', 'throttle:60,1'])->group(function () {
    Route::post('/songs', [SongController::class, 'store']);
    Route::put('/songs/{id}', [SongController::class, 'update']);
    Route::post('/songs/{id}', [SongController::class, 'update']);
    Route::delete('/songs/{id}', [SongController::class, 'destroy']);

    Route::post('/movies', [MovieController::class, 'store']);
    Route::put('/movies/{id}', [MovieController::class, 'update']);
    Route::delete('/movies/{id}', [MovieController::class, 'destroy']);

    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
    Route::get('/admin/stats', [AdminUserController::class, 'stats']);
});

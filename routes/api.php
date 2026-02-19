<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AdsController;
use Illuminate\Http\Request;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/ads', [AdsController::class, 'index']);
Route::get('/ads/filters', [AdsController::class, 'filters']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('rooms', RoomController::class);

    Route::post('/ads', [AdsController::class, 'store']);
    Route::delete('/ads/{ad}', [AdsController::class, 'destroy']);

    // Superadmin rute
    Route::get('/admin/users', [AuthController::class, 'listUsers']);
    Route::post('/admin/create-admin', [AuthController::class, 'createAdmin']);
    Route::put('/admin/users/{id}', [AuthController::class, 'updateUser']);
    Route::delete('/admin/users/{id}', [AuthController::class, 'deleteUser']);
});

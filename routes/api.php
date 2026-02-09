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
Route::get('/ads', [AdsController::class, 'index']); // svi vide oglase

// NOVO: endpoint za dohvat filtera (države i gradovi)
Route::get('/ads/filters', [AdsController::class, 'filters']); // svi vide filtere

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn(Request $request) => $request->user());
    
    Route::apiResource('users', UserController::class);
    Route::apiResource('rooms', RoomController::class);

    Route::post('/ads', [AdsController::class, 'store']); // admin i superadmin
    Route::delete('/ads/{ad}', [AdsController::class, 'destroy']); // vlasnik ili superadmin
});

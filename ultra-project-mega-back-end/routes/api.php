<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/resources/available', [ResourceController::class, 'available']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    
    Route::apiResource('resources', ResourceController::class);
    
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    
    Route::post('/resources/{resource}/reviews', [ReviewController::class, 'store']);
    
    Route::middleware('admin')->group(function () {
        Route::get('/admin/bookings', [BookingController::class, 'adminIndex']);
    });
});

Route::get('/resources/{resource}/schedule', [ResourceController::class, 'schedule']);
Route::get('/resources/{resource}/reviews', [ReviewController::class, 'index']);
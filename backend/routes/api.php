<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Auth\CurrentUserController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class)->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', LogoutController::class);
        Route::get('/user', CurrentUserController::class);

        Route::middleware('admin')->prefix('admin')->group(function (): void {
            Route::get('/dashboard', DashboardController::class);
        });
    });
});

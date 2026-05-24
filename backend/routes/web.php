<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'health']);
Route::get('/ready', [HealthController::class, 'ready']);

Route::get('/', function () {
    return response()->json([
        'name' => 'Auction Platform API',
        'version' => 'v1',
    ]);
});

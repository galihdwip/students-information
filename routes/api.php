<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ExternalDataController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or bootstrap/app.php
| and will be assigned the "api" middleware group.
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {

    Route::apiResource('users', UserController::class);

});

Route::middleware('auth:api')->group(function () {

    Route::get(
        '/students/data',
        [ExternalDataController::class, 'index']
    );

});

Route::get(
    '/students/search',
    [ExternalDataController::class, 'search']
);

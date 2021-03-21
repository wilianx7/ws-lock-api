<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LockController;
use App\Http\Controllers\LockHistoryController;
use App\Http\Controllers\MQTTController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use Illuminate\Support\Facades\Route;

/**
 * Public routes
 */
Route::group(['middleware' => ['api', 'throttle:180,1']], function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::put('users/create-or-update', [UserController::class, 'createOrUpdate']);

    Route::put('validations/unique', [ValidationController::class, 'unique']);
});

/**
 * Private routes
 */
Route::group(['middleware' => ['api', 'auth:api', 'throttle:60,1']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('validate', [AuthController::class, 'validateJwt']);

    Route::apiResource('users', UserController::class)->except(['store', 'update']);

    Route::put('locks/create-or-update', [LockController::class, 'createOrUpdate']);
    Route::apiResource('locks', LockController::class)->except(['store', 'update']);

    Route::apiResource('lock-histories', LockHistoryController::class)->only(['index']);

    Route::put('mqtt/open-door', [MQTTController::class, 'openDoor']);
    Route::put('mqtt/close-door', [MQTTController::class, 'closeDoor']);
});

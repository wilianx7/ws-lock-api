<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * Public routes
 */
Route::group(['middleware' => ['api', 'throttle:180,1']], function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::put('users/create-or-update', [UserController::class, 'createOrUpdate']);
});

Route::group(['middleware' => ['api', 'auth:api', 'throttle:60,1']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('validate', [AuthController::class, 'validateJwt']);

    Route::apiResource('users', UserController::class)->except(['store', 'update']);
});

<?php

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

Route::prefix('v1')->group(function () {
    Route::post('register', [\App\Http\Controllers\API\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\API\AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::put('products/{slug}', [\App\Http\Controllers\API\ProductController::class, 'updateQuantity']);
        Route::resource('products', \App\Http\Controllers\API\ProductController::class);
    });
});

<?php

use Illuminate\Http\Request;
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
Route::group(['prefix' => 'v1'], function($router) {
    Route::group(['prefix' => 'data'], function($router) {
        Route::post('/get', [\App\Http\Controllers\DataController::class, 'get']);
        Route::post('/sort', [\App\Http\Controllers\DataController::class, 'sort']);
        Route::post('/take', [\App\Http\Controllers\DataController::class, 'take']);
        Route::post('/change', [\App\Http\Controllers\DataController::class, 'change']);
        Route::post('/columns', [\App\Http\Controllers\DataController::class, 'columns']);
    });
});

<?php

use App\Http\Controllers\api\v2\MusicController;
use App\Http\Controllers\api\v2\UserController;
use App\Http\Controllers\api\v3\UserController as V3UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the framework within a group which is assigned
| the "api" middleware group and the "api" prefix.
|
*/

// From Bookmarklet
Route::prefix('v2')->group(function () {
    Route::middleware('cors')->get('/user/update/status', [UserController::class, 'GetUpdateStatus'])->middleware('auth:api');
    Route::middleware('cors')->match(['post', 'options'], '/user/update', [UserController::class, 'PostUpdate'])->middleware('auth:api');
    Route::middleware('cors')->get('/music/samename', [MusicController::class, 'GetSameNameMusic']);
});

// v3: data を JSON → gzip → base64 した文字列で受け取る（v2 と並置。samename は v2 のまま）
Route::prefix('v3')->group(function () {
    Route::middleware('cors')->get('/user/update/status', [V3UserController::class, 'GetUpdateStatus'])->middleware('auth:api');
    Route::middleware('cors')->match(['post', 'options'], '/user/update', [V3UserController::class, 'PostUpdate'])->middleware('auth:api');
    Route::middleware('cors')->get('/music/samename', [MusicController::class, 'GetSameNameMusic']);
});

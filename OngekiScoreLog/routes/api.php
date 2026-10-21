<?php

use App\Http\Controllers\api\v2\MusicController;
use App\Http\Controllers\api\v2\UserController;
use App\Http\Controllers\BookmarkletAccessController;
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
Route::middleware('cors')->match(['post', 'options'], '/user/update', [BookmarkletAccessController::class, 'postUserUpdate'])->middleware('auth:api');

Route::prefix('v2')->group(function () {
    Route::middleware('cors')->get('/user/update/status', [UserController::class, 'GetUpdateStatus'])->middleware('auth:api');
    Route::middleware('cors')->match(['post', 'options'], '/user/update', [UserController::class, 'PostUpdate'])->middleware('auth:api');
    Route::middleware('cors')->get('/music/samename', [MusicController::class, 'GetSameNameMusic']);
});

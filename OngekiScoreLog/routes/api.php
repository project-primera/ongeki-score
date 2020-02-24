<?php

use Illuminate\Http\Request;

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

// From Bookmarklet
Route::middleware('cors')->match(['post', 'options'], '/user/update', 'BookmarkletAccessController@postUserUpdate')->middleware('auth:api');

Route::namespace('api')->group(function(){
    Route::namespace('v2')->prefix('v2')->group(function(){
        Route::middleware('cors')->get('/user/update/status', 'UserController@GetUpdateStatus')->middleware('auth:api');
    });
});

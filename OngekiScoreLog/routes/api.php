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
Route::middleware('cors')->get('/live', 'SimpleViewController@getApiLive');
Route::middleware('cors')->match(['post', 'options'], '/user/update', 'BookmarkletAccessController@postUserUpdate')->middleware('auth:api');
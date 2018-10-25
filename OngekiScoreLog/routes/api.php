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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// From my site(for debug)
Route::get('/user/music/{id}/{songID}/{difficulty}', 'ApiController@getRecentGenerationOfScoreData')->where(['id' => '\d+', 'songID' => '\d+', 'difficulty' => '\d+']);
Route::get('/user/music/recent_id/{id}', 'ApiController@getRecentGenerationOfScoreDataAll')->where(['id' => '\d+']);
Route::get('/user/music/{id}', 'ApiController@getUserMusic')->where(['id' => '\d+']);


// From Bookmarklet
Route::middleware('cors')->match(['post', 'options'],'/user/update', 'BookmarkletAccessController@postUserUpdate')->middleware('auth:api');

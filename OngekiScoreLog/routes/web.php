<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', 'SimpleViewController@getIndex');

Route::get('/user/{id}/progress/{generation?}', 'ViewUserProgressController@getIndex')->where(['id' => '\d+', 'generation' => '\d+']);
Route::get('/user/{id}/rating', 'ViewUserRatingController@getIndex')->where(['id' => '\d+']);
Route::get('/user/{id}/trophy', 'ViewUserTrophyController@getIndex')->where(['id' => '\d+']);
Route::get('/user/{id}/music/{music}/{difficulty}', 'ViewUserMusicController@getIndex')->where(['id' => '\d+', 'music' => '\d+', 'difficulty' => '\w+']);
Route::get('/user/{id}/music/{music}', 'ViewUserMusicController@getRedirect')->where(['id' => '\d+', 'music' => '\d+']);
Route::get('/user/{id}/battlescore/{difficulty?}', 'ViewUserController@getBattleScorePage')->where(['id' => '\d+', 'difficulty' => '\w+']);
Route::get('/user/{id}/overdamage/{difficulty?}', 'ViewUserController@getOverDamegePage')->where(['id' => '\d+', 'difficulty' => '\w+']);
Route::get('/user/{id}/{mode?}', 'ViewUserController@getUserPage')->where(['id' => '\d+']);

Route::middleware('throttle:3,1')->group(function () {
    Route::get('/music/{music}/{difficulty}', 'ViewMusicStatisticsController@getIndex')->where(['music' => '\d+', 'difficulty' => '\w+']);
    Route::get('/music/{music}', 'ViewMusicStatisticsController@getRedirect')->where(['music' => '\d+']);
});

Route::get('/music', 'ViewMusicExtraLevelController@getIndex');

Route::get('/random', 'ViewUserController@redirectRandomUserPage');
Route::get('/mypage/{path?}', 'ViewUserController@getMyUserPage');

Route::redirect('/alluser', '/user', 301);
Route::get('/user', 'ViewAllUserController@getIndex');

Route::get('/bookmarklet', 'BookmarkletGenerateController@getIndex');
Route::get('/bookmarklet/agree', 'BookmarkletGenerateController@getBookmarklet');
Route::get('/setting', 'SettingController@getSetting');
Route::get('/setting/twitter', 'SettingController@getTwitterAuthentication');

Route::get('/howto', 'SimpleViewController@getHowto');
Route::get('/faq', 'SimpleViewController@getFAQ');
Route::get('/eula', 'SimpleViewController@getEula');

Route::get('/changelog', 'SimpleViewController@getChangelog');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/logout', 'SimpleViewController@getLogout');

Route::get('/tweet/image', 'TweetController@getIndex');
Route::post('/tweet/image', 'TweetController@postTweetImage');

// for admin
Route::group(['middleware' => ['auth', 'can:admin']], function () {
    Route::get('/admin', 'AdminController@GetIndex');
    Route::get('/admin/config', 'AdminController@GetConfig');
    Route::get('/admin/aggregate', 'AdminController@GetAggregate');
    Route::get('/admin/apply/{type}/{action?}', 'AdminController@GetApply');
    Route::get('/admin/generate/battle-score', 'AdminController@GetGenerateBattleScore');
    Route::get('/admin/generate/over-damage', 'AdminController@GetGenerateOverDamage');
    Route::get('/admin/log/{path}/{fileName}', 'SimpleViewController@getLogFile');
});

/*  for debug
Route::get('/version/update', 'SimpleViewController@versionUpdate');
Route::get('/t/{s}', 'SimpleViewController@testTweet');
*/

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

Route::get('/user/{id}/{mode?}', 'ViewUserController@getUserPage')->where(['id' => '\d+']);
Route::get('/user/progress/{id}', 'ViewUserProgressController@getIndex')->where(['id' => '\d+']);

Route::get('/random', 'ViewUserController@redirectRandomUserPage');
Route::get('/mypage', 'ViewUserController@getMyUserPage');
Route::get('/alluser', 'ViewAllUserController@getIndex');

Route::get('/bookmarklet', 'BookmarkletGenerateController@getIndex');
Route::get('/bookmarklet/agree', 'BookmarkletGenerateController@getBookmarklet');
Route::get('/setting', 'SettingController@getSetting');
Route::get('/setting/twitter', 'SettingController@getTwitterAuthentication');

Route::get('/howto', 'SimpleViewController@getHowto');
Route::get('/eula', 'SimpleViewController@getEula');

Route::get('/changelog', 'SimpleViewController@getChangelog');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/logout', 'SimpleViewController@getLogout');

/*  for debug
Route::get('/version/update', 'SimpleViewController@versionUpdate');
Route::get('/t/{s}', 'SimpleViewController@testTweet');
*/
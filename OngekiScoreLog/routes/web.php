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

Route::get('/', function () {
    return view('top');
});

Route::get('/user/{id}/{mode?}', 'ViewUserController@getUserPage')->where(['id' => '\d+']);
Route::get('/random', 'ViewUserController@redirectRandomUserPage');
Route::get('/mypage', 'ViewUserController@getMyUserPage');
Route::get('/alluser', 'ViewAllUserController@getIndex');

Route::get('/bookmarklet', 'BookmarkletGenerateController@getIndex');
Route::get('/bookmarklet/agree', 'BookmarkletGenerateController@getBookmarklet');

Route::get('/howto', function () {
    return view('howto');
});



Route::get('/home', 'HomeController@index')->name('home');

Route::get('/logout', function () {
    Auth::logout();
    return view('logout');
});

Route::get('/user_status', function () {
    $status = App\UserStatus::all();
    return $status;
});

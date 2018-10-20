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

Route::get('/', function () {
    return view('top');
});

Route::get('/logout', function () {
    Auth::logout();
    return view('logout');
});

Route::get('/user/{id}/{mode?}', 'ViewUserController@getUserPage')->where(['id' => '\d+']);



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/home', 'HomeController@index')->name('home');

Route::get('/user_status', function () {
    $status = App\UserStatus::all();
    return $status;
});

// http://127.0.0.1:8000/bookmarklet
Route::get('/bookmarklet', 'BookmarkletGenerateController@getIndex');

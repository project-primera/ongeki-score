<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookmarkletGenerateController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SimpleViewController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\ViewAllUserController;
use App\Http\Controllers\ViewMusicExtraLevelController;
use App\Http\Controllers\ViewMusicStatisticsController;
use App\Http\Controllers\ViewUserController;
use App\Http\Controllers\ViewUserMusicController;
use App\Http\Controllers\ViewUserProgressController;
use App\Http\Controllers\ViewUserRatingController;
use App\Http\Controllers\ViewUserTrophyController;

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

Route::get('/', [SimpleViewController::class, 'getIndex']);

Route::get('/user/{id}/progress/{generation?}', [ViewUserProgressController::class, 'getIndex'])->where(['id' => '\d+', 'generation' => '\d+']);
Route::get('/user/{id}/rating', [ViewUserRatingController::class, 'getIndex'])->where(['id' => '\d+']);
Route::get('/user/{id}/trophy', [ViewUserTrophyController::class, 'getIndex'])->where(['id' => '\d+']);
Route::get('/user/{id}/music/{music}/{difficulty}', [ViewUserMusicController::class, 'getIndex'])->where(['id' => '\d+', 'music' => '\d+', 'difficulty' => '\w+']);
Route::get('/user/{id}/music/{music}', [ViewUserMusicController::class, 'getRedirect'])->where(['id' => '\d+', 'music' => '\d+']);
Route::get('/user/{id}/battlescore/{difficulty?}', [ViewUserController::class, 'getBattleScorePage'])->where(['id' => '\d+', 'difficulty' => '\w+']);
Route::get('/user/{id}/overdamage/{difficulty?}', [ViewUserController::class, 'getOverDamegePage'])->where(['id' => '\d+', 'difficulty' => '\w+']);
Route::get('/user/{id}/{mode?}', [ViewUserController::class, 'getUserPage'])->where(['id' => '\d+']);

Route::middleware('throttle:1,1')->group(function () {
    Route::get('/music/{music}/{difficulty}', [ViewMusicStatisticsController::class, 'getIndex'])->where(['music' => '\d+', 'difficulty' => '\w+']);
    Route::get('/music/{music}', [ViewMusicStatisticsController::class, 'getRedirect'])->where(['music' => '\d+']);
});

Route::get('/music', [ViewMusicExtraLevelController::class, 'getIndex']);

Route::get('/random', [ViewUserController::class, 'redirectRandomUserPage']);
Route::get('/mypage/{path?}', [ViewUserController::class, 'getMyUserPage']);

Route::redirect('/alluser', '/user', 301);
Route::get('/user', [ViewAllUserController::class, 'getIndex']);

Route::get('/bookmarklet', [BookmarkletGenerateController::class, 'getIndex']);
Route::get('/bookmarklet/agree', [BookmarkletGenerateController::class, 'getBookmarklet']);
Route::get('/setting', [SettingController::class, 'getSetting']);
Route::get('/setting/private', [SettingController::class, 'getSettingPrivate']);
Route::get('/setting/public', [SettingController::class, 'getSettingPublic']);
#Route::get('/setting/twitter', [SettingController::class, 'getTwitterAuthentication']);

Route::get('/howto', [SimpleViewController::class, 'getHowto']);
Route::get('/faq', [SimpleViewController::class, 'getFAQ']);
Route::get('/eula', [SimpleViewController::class, 'getEula']);
Route::get('/data-policy', [SimpleViewController::class, 'getDataPolicy']);

Route::get('/changelog', [SimpleViewController::class, 'getChangelog']);

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/logout', [SimpleViewController::class, 'getLogout']);

Route::get('/tweet/image', [TweetController::class, 'getIndex']);
Route::post('/tweet/image', [TweetController::class, 'postTweetImage']);

// for admin
Route::group(['middleware' => ['auth', 'can:admin']], function () {
    Route::get('/admin', [AdminController::class, 'GetIndex']);
    Route::get('/admin/config', [AdminController::class, 'GetConfig']);
    Route::get('/admin/battle', [AdminController::class, 'GetBattle']);
    Route::get('/admin/overdamage', [AdminController::class, 'GetOverDamage']);
    Route::get('/admin/apply/{type}/{action?}', [AdminController::class, 'GetApply']);
    Route::get('/admin/generate/battle-score', [AdminController::class, 'GetGenerateBattleScore']);
    Route::get('/admin/generate/over-damage', [AdminController::class, 'GetGenerateOverDamage']);
    Route::get('/admin/log/{path}/{fileName}', [SimpleViewController::class, 'getLogFile']);
});

/*  for debug
Route::get('/version/update', [SimpleViewController::class, 'versionUpdate']);
Route::get('/t/{s}', [SimpleViewController::class, 'testTweet']);
*/

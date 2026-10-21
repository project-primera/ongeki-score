<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes / Schedule
|--------------------------------------------------------------------------
|
| 旧 app/Console/Kernel.php の schedule() をここへ移した。
| コマンドクラスは app/Console/Commands 以下から自動で登録される。
|
*/

// 毎月 1 日 7:00 に全ユーザーの課金状態をリセットする
Schedule::call(function () {
    \App\UserInformation::ResetAllUserPaymentState();
    \App\Facades\Slack::Info("<!here> すべてのユーザーの課金情報をリセットしました。");
})->monthlyOn(1, '7:00');

// バトルスコア集計
Schedule::call(function () {
    \App\AggregateBattleScore::execute();
})->dailyAt('4:10');

// オーバーダメージ集計
Schedule::call(function () {
    \App\AggregateOverdamage::execute();
})->dailyAt('4:10');

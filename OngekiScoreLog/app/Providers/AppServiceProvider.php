<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(190);

        // OAuth の認可（同意）画面は使わない。コントローラが要求するバインディングだけ用意する
        Passport::authorizationView(fn () => abort(404));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Passport の設定。ルート登録は PassportServiceProvider::boot() で行われるため、
        // その前に実行される register() で指定する。
        // 旧版（Passport 6）から引き継いだ整数 id の client と発行済みトークンを使う
        Passport::$clientUuids = false;
        // device code grant は使わない（oauth_device_codes テーブルを持たない）
        Passport::$deviceCodeGrantEnabled = false;
        // 旧版が公開していた JSON API（oauth/clients、oauth/tokens など）はアプリから使っていないため登録しない。
        // ブックマークレットのトークンはサーバー側の createToken() で発行する
    }
}

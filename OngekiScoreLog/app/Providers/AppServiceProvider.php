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
        // /oauth/* のルート（token、authorize、JSON API）は登録しない。
        // ブックマークレットのトークンはサーバー側の createToken() で発行し、検証は auth:api（TokenGuard）が行うので HTTP のルートは不要。
        // 旧版は Passport::routes() で全部開けていたが、password grant を含め本番で使われた形跡が無い（判断 L、upgrade_review3 2.4 / 2.5）
        Passport::ignoreRoutes();
    }
}

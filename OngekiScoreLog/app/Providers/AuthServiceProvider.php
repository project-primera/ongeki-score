<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        /* Laravel Gate Setting
         * 10:
         *  9:
         *  8:
         *  7: 管理者
         *  6:
         *  5:
         *  4:
         *  3:
         *  2: (deprecated) Ongeki-Net プレミアムプランユーザー
         *  1: (deprecated) Ongeki-Net スタンダードプランユーザー
         *  0: 一般ユーザー
         */
        Gate::define('admin', function ($user) {
            return ($user->role >= 7);
        });
    }
}

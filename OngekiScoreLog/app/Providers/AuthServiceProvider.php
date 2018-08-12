<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();


        /* Laravel Gate Setting
         * 10: 
         *  9: 
         *  8: 
         *  7: 管理者
         *  6: 
         *  5: 
         *  4: 
         *  3: 
         *  2: 
         *  1:  
         *  0: 一般ユーザー
         */
        /*
        Gate::define('higher_admin', function ($user) {
            return ($user->role >= 8);
        });
        */
    }
}

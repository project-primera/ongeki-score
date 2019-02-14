<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class OngekiUtilityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'OngekiUtility',
            'App\Services\OngekiUtility'
        );
    }
}

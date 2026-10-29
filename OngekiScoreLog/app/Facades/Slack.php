<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Services\Slack
 */
class Slack extends Facade {
    protected static function getFacadeAccessor() {
        return 'Slack';
    }
}
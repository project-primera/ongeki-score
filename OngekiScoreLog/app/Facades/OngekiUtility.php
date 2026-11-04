<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Services\OngekiUtility
 */
class OngekiUtility extends Facade {
    protected static function getFacadeAccessor() {
        return 'OngekiUtility';
    }
}
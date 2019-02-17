<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Slack extends Facade {
    protected static function getFacadeAccessor() {
        return 'Slack';
    }
}
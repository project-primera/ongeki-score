<?php

namespace Tests\Unit;

use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * bootstrap/app.php が APP_ENV_PATH を見て .env の置き場所を切り替えること。
 * コンテナでは .env をコード配置先（/app、root 所有）の外に置くために使う（Dockerfile / docker-entrypoint.sh）。
 */
class EnvironmentPathTest extends TestCase
{
    public function test_app_env_path_overrides_the_environment_directory(): void
    {
        putenv('APP_ENV_PATH=/tmp/ongeki-score-env-test');

        try {
            $app = require __DIR__.'/../../bootstrap/app.php';
            $this->assertSame('/tmp/ongeki-score-env-test', $app->environmentPath());
            $this->assertSame('/tmp/ongeki-score-env-test/.env', $app->environmentFilePath());
        } finally {
            putenv('APP_ENV_PATH');
            // bootstrap/app.php は読み込むたびにコンテナのインスタンスを差し替えるので、テスト中のアプリに戻す
            Container::setInstance($this->app);
        }
    }

    public function test_without_app_env_path_the_base_path_is_used(): void
    {
        putenv('APP_ENV_PATH');

        try {
            $app = require __DIR__.'/../../bootstrap/app.php';
            $this->assertSame($app->basePath(), $app->environmentPath());
        } finally {
            Container::setInstance($this->app);
        }
    }
}

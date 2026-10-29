<?php

namespace Tests\Feature;

use App\Facades\Slack;
use Tests\TestCase;

/**
 * メンテナンス中（artisan down --retry --refresh）の 503 に Retry-After と Refresh が付くことを確認する。
 * Laravel 11 以降はメンテナンス中の要求が 503 の HttpException になり、ヘッダは例外が持っている。
 * 旧 5.6 はメンテナンス例外を親の render() に委ねていたので付いていた。
 */
class MaintenanceHeadersTest extends TestCase
{
    public function test_maintenance_response_keeps_retry_and_refresh_headers(): void
    {
        Slack::spy();

        // storage/framework/down を作らないよう、テストプロセス内だけの array キャッシュでメンテナンス状態を持つ
        config(['app.maintenance.driver' => 'cache', 'app.maintenance.store' => 'array']);
        $this->app->maintenanceMode()->activate(['retry' => 60, 'refresh' => 15]);

        try {
            $this->get('/')
                ->assertStatus(503)
                ->assertHeader('Retry-After', '60')
                ->assertHeader('Refresh', '15');
        } finally {
            $this->app->maintenanceMode()->deactivate();
        }

        // 旧 MaintenanceModeException と同じく Notice で通知される
        Slack::shouldHaveReceived('Notice')->once();
    }
}

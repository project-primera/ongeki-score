<?php

namespace Tests\Feature;

use App\Facades\Slack;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * API の回数制限（throttle）が認証（auth:api）より先に実行されることを確認する。
 * Laravel 13 の既定の優先順は認証が先で、不正なトークンの要求が回数制限に到達しなくなる。
 */
class ApiThrottleOrderTest extends TestCase
{
    private const ROUTE = 'api/v2/user/update/status';

    public function test_throttle_runs_before_authentication_on_api_routes(): void
    {
        // ミドルウェアのグループ・別名・優先順は HTTP Kernel の生成時に router へ同期される
        $this->app->make(Kernel::class);

        $router = app('router');
        $route = collect($router->getRoutes()->getRoutes())->first(fn ($route) => $route->uri() === self::ROUTE);
        $this->assertNotNull($route, self::ROUTE.' が見つからない');

        $middleware = $router->gatherRouteMiddleware($route);
        $throttle = $this->indexOf($middleware, ThrottleRequests::class);
        $auth = $this->indexOf($middleware, Authenticate::class);

        $this->assertNotNull($throttle, 'throttle が無い: '.implode(', ', $middleware));
        $this->assertNotNull($auth, 'auth が無い: '.implode(', ', $middleware));
        $this->assertLessThan($auth, $throttle, '実行順: '.implode(', ', $middleware));
    }

    public function test_requests_with_invalid_token_are_rate_limited(): void
    {
        // 認証失敗は Slack に通知されるので、テスト中は送らない
        Slack::spy();

        $headers = ['Authorization' => 'Bearer invalid-token', 'User-Agent' => 'phpunit'];

        // api グループは throttle:120,1
        for ($i = 1; $i <= 120; $i++) {
            $status = $this->withHeaders($headers)->getJson('/'.self::ROUTE)->getStatusCode();
            $this->assertNotSame(429, $status, $i.' 回目で 429 になった');
        }

        $this->withHeaders($headers)->getJson('/'.self::ROUTE)->assertStatus(429);
    }

    /** gatherRouteMiddleware() の戻り値（"クラス名:引数" の形を含む）から、指定クラスの位置を返す */
    private function indexOf(array $middleware, string $class): ?int
    {
        foreach ($middleware as $index => $name) {
            if ($name === $class || str_starts_with($name, $class.':')) {
                return $index;
            }
        }

        return null;
    }
}

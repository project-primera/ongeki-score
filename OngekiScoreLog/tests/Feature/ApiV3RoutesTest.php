<?php

namespace Tests\Feature;

use App\Facades\Slack;
use App\Http\Controllers\api\v2\MusicController;
use App\Http\Controllers\api\v2\UserController as V2UserController;
use App\Http\Controllers\api\v3\UserController as V3UserController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Route;
use League\OAuth2\Server\ResourceServer;
use Mockery;
use Tests\TestCase;

/**
 * API v3（data を JSON → gzip → base64 で受け取る）のルート定義。
 * v2 と並置し、samename だけは v2 のコントローラを共用する。v2 のルートは変えない。
 */
class ApiV3RoutesTest extends TestCase
{
    public function test_v3_routes_are_registered_with_the_same_methods_as_v2(): void
    {
        $this->assertSame(['GET', 'HEAD'], $this->route('api/v3/user/update/status')->methods());
        $this->assertSame(['POST', 'OPTIONS'], $this->route('api/v3/user/update')->methods());
        $this->assertSame(['GET', 'HEAD'], $this->route('api/v3/music/samename')->methods());
    }

    public function test_v3_user_routes_use_the_v3_controller_and_samename_uses_v2(): void
    {
        $this->assertSame(V3UserController::class.'@GetUpdateStatus', $this->action('api/v3/user/update/status'));
        $this->assertSame(V3UserController::class.'@PostUpdate', $this->action('api/v3/user/update'));
        $this->assertSame(MusicController::class.'@GetSameNameMusic', $this->action('api/v3/music/samename'));
    }

    public function test_v2_routes_still_use_the_v2_controller(): void
    {
        $this->assertSame(V2UserController::class.'@GetUpdateStatus', $this->action('api/v2/user/update/status'));
        $this->assertSame(V2UserController::class.'@PostUpdate', $this->action('api/v2/user/update'));
        $this->assertSame(MusicController::class.'@GetSameNameMusic', $this->action('api/v2/music/samename'));
    }

    public function test_throttle_runs_before_authentication_on_v3_user_routes(): void
    {
        // ミドルウェアのグループ・別名・優先順は HTTP Kernel の生成時に router へ同期される
        $this->app->make(Kernel::class);
        $router = app('router');

        foreach (['api/v3/user/update/status', 'api/v3/user/update'] as $uri) {
            $middleware = $router->gatherRouteMiddleware($this->route($uri));
            $throttle = $this->indexOf($middleware, ThrottleRequests::class);
            $auth = $this->indexOf($middleware, Authenticate::class);

            $this->assertNotNull($throttle, $uri.' に throttle が無い: '.implode(', ', $middleware));
            $this->assertNotNull($auth, $uri.' に auth が無い: '.implode(', ', $middleware));
            $this->assertLessThan($auth, $throttle, $uri.' の実行順: '.implode(', ', $middleware));
        }
    }

    public function test_v3_user_routes_without_token_get_401_json(): void
    {
        Slack::spy();
        // Passport のリソースサーバーを差し替えて、鍵ファイル無しで auth:api を通す
        $this->app->instance(ResourceServer::class, Mockery::mock(ResourceServer::class));

        $this->get('/api/v3/user/update/status')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
        $this->post('/api/v3/user/update')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    /** ルートの action（先頭の \\ を除いた "クラス@メソッド"） */
    private function action(string $uri): string
    {
        return ltrim($this->route($uri)->getActionName(), '\\');
    }

    private function route(string $uri): Route
    {
        $route = collect(app('router')->getRoutes()->getRoutes())->first(fn (Route $route) => $route->uri() === $uri);
        $this->assertNotNull($route, $uri.' が見つからない');

        return $route;
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

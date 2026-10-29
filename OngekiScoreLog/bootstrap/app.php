<?php

use App\Exceptions\Handler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 旧 app/Http/Kernel.php のグローバルミドルウェアと同じ並び。
        // StartSession をグローバルに置き、web グループから外す構成も旧版どおり
        // （セッション cookie が EncryptCookies の後に付与されるため平文の id のまま、という挙動を維持する）。
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            // 旧 barryvdh/laravel-cors と同様に、プリフライト（OPTIONS）はルーティング前に応答する
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->web(remove: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->group('api', [
            'throttle:120,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // 実行順の優先一覧。Laravel 13 の既定は認証（auth）→ throttle の順だが、
        // 旧 5.6 の一覧に throttle は無く、api グループの並びどおり throttle → auth:api の順で動いていた。
        // 既定のままだと不正トークンの要求が 401 で止まって回数制限に到達しないので、throttle を認証より前に置く。
        // 一覧は vendor Illuminate\Foundation\Http\Kernel::$middlewarePriority（v13.38.0）の並びを基にしている
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        $middleware->alias([
            'cors' => \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->trimStrings(except: ['password', 'password_confirmation']);

        // 旧 TrustProxies: $proxies = '*', $headers = HEADER_X_FORWARDED_ALL（FOR | HOST | PORT | PROTO）
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->redirectUsersTo('/home');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 404 や認証失敗なども Slack 通知の分岐に通す（フレームワークの既定では report に回らない）
        $exceptions->stopIgnoring(Handler::FRAMEWORK_IGNORED);

        $exceptions->report(function (Throwable $e) {
            Handler::report($e);

            // false を返すと既定のロガーに進まない。旧版はこれらをログに出していなかった
            return Handler::shouldLog($e) ? null : false;
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            return Handler::render($request, $e);
        });
    })->create();

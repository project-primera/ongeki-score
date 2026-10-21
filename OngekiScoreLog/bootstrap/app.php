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

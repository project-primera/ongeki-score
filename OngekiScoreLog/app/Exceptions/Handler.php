<?php

namespace App\Exceptions;

use App\Facades\Slack;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Exceptions\OriginMismatchException;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * 例外の Slack 通知と、エラーページの描画。
 * bootstrap/app.php の withExceptions() から呼ばれる。旧 Laravel 5.6 の Handler の挙動を保つ。
 */
class Handler
{
    /** 存在しないページとして扱わず、攻撃とみなして 418 を返すパス */
    private const SECURITY_PAGES = [
        'administrator',
        'security.txt',
        '.well-known/security.txt',
    ];

    /** 通知しない 404 のパス */
    private const IGNORE_PAGES = [
        'apple-app-site-association',
        'cross-platform-app-identifiers',
        '.well-known/assetlinks.json',
        'rss',
        'atom',
        'feed',
        'blog',
        // 旧版が Passport::routes() で開けていた OAuth エンドポイント。登録をやめて 404 になったが、bot の走査を通知しない
        'oauth/authorize',
        'oauth/token',
        'oauth/token/refresh',
    ];

    /** 専用の view を持つステータスコード */
    private const VIEW_STATUS_CODES = [404, 418, 429, 500, 503];

    /**
     * フレームワークが既定で report() に回さない例外。
     * 旧版は report() を上書きしていたので、これらも Slack 通知の分岐を通っていた（ログには出さない）。
     * bootstrap/app.php の stopIgnoring() と、shouldLog() の両方で使う。
     *
     * vendor の Illuminate\Foundation\Exceptions\Handler::$internalDontReport と同じ内容にしておくこと
     * （ずれると、増えた例外は Slack に出ず、余分な例外はログに出ない）。
     * 一致は tests/Unit/ExceptionHandlerIgnoreListTest で確認している。
     */
    public const FRAMEWORK_IGNORED = [
        AuthenticationException::class,
        AuthorizationException::class,
        BackedEnumCaseNotFoundException::class,
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        OriginMismatchException::class,
        RecordNotFoundException::class,
        RecordsNotFoundException::class,
        RequestExceptionInterface::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /** 旧版と同じく、フレームワークが既定で無視する例外はログに書かない */
    public static function shouldLog(Throwable $exception): bool
    {
        foreach (self::FRAMEWORK_IGNORED as $class) {
            if ($exception instanceof $class) {
                return false;
            }
        }

        return true;
    }

    public static function isSecurityPage(string $path): bool
    {
        return in_array($path, self::SECURITY_PAGES, true) || str_contains($path, 'ini_set');
    }

    public static function report(Throwable $exception): void
    {
        $user = Auth::user();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
        if (strpos($ua, '+https://api.slack.com/robots') !== false) {
            return;
        }

        $content = $exception->getMessage()."\n".get_class($exception)."\n".url()->full();
        $fileContent = 'ip: '.request()->ip()."\nUser agent: ".$ua."\nReferer: ".($_SERVER['HTTP_REFERER'] ?? 'N/A')."\n\n";
        if (! is_null($user)) {
            $fileContent .= "User:\nid: ".$user->id."\nemail: ".$user->email."\nrole: ".$user->role."\n\n";
            $fields = ['File' => $exception->getFile(), 'Line' => $exception->getLine(), 'IP Address' => request()->ip(), 'User id' => $user->id];
        } else {
            $fileContent .= "User: N/A\n\n";
            $fields = ['File' => $exception->getFile(), 'Line' => $exception->getLine(), 'IP Address' => request()->ip(), 'User id' => 'N/A'];
        }
        $fileContent .= "Cookie:\n".var_export(Cookie::get(), true)."\n\nRequest:\n".var_export(request()->all(), true)."\n\n".$exception->__toString();

        $path = request()->path();

        switch (true) {
            // 旧 MaintenanceModeException。Laravel 11 以降はメンテナンス中の要求が 503 の HttpException になる
            case ($exception instanceof HttpException && $exception->getStatusCode() === 503):
                Slack::Notice($content, $fileContent, $fields, 'warning');
                break;

            case ($exception instanceof OAuthServerException):
            case ($exception instanceof AuthenticationException):
            case ($exception instanceof ValidationException):
            case ($exception instanceof TokenMismatchException):
                Slack::Warning($content, $fileContent, $fields, 'warning');
                break;

            case ($exception instanceof NotFoundHttpException):
                if (self::isSecurityPage($path)) {
                    // 旧版はここで abort(418) していた。描画側（render）で 418 を返す
                    Slack::Error($content, $fileContent, $fields, 'success');
                } elseif (! in_array($path, self::IGNORE_PAGES, true)) {
                    Slack::Warning($content, $fileContent, $fields, 'warning');
                }
                break;

            case ($exception instanceof HttpException):
                if (! self::isSecurityPage($path)) {
                    Slack::Warning($content, $fileContent, $fields, 'warning');
                }
                break;

            default:
                Slack::Critical($content, $fileContent, $fields, 'error');
                break;
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response|null null を返すとフレームワーク既定の描画に委ねる
     */
    public static function render(Request $request, Throwable $exception)
    {
        if ($exception instanceof ValidationException || $exception instanceof HttpResponseException) {
            // 勝手に処理してくれるのでもともとのハンドラに丸投げする
            return null;
        }

        if ($exception instanceof NotFoundHttpException && self::isSecurityPage($request->path())) {
            return response()->view('errors/418', [], 418);
        }

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            if (in_array($status, self::VIEW_STATUS_CODES, true)) {
                return response()->view('errors/'.$status, [], $status);
            }
        }

        if (config('app.debug')) {
            return null;
        }

        return response()->view('errors/500', [], 500);
    }
}

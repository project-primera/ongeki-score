<?php

namespace App\Exceptions;

use Exception;
use Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Facades\Slack;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        $user = Auth::user();
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "N/A";
        if(strpos($ua, "+https://api.slack.com/robots") === false){
            $content = $exception->getMessage() . "\n" . get_class($exception) . "\n" . url()->full();
            $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $ua . "\nReferer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "N/A") . "\n\n";
            if(!is_null($user)){
                $fileContent .= "User:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\n";
                $fields = ["File" => $exception->getFile(), "Line" => $exception->getLine(), "IP Address" => \Request::ip(), "User id" => $user->id];
            }else{
                $fileContent .= "User: N/A\n\n";
                $fields = ["File" => $exception->getFile(), "Line" => $exception->getLine(), "IP Address" => \Request::ip(), "User id" => "N/A"];
            }
            $fileContent .= "Cookie:\n" . var_export(Cookie::get(), true) . "\n\nRequest:\n" . var_export(Request::all(), true) . "\n\n" . $exception->__toString();

            $securityPages = [
                "administrator" => "",
                "security.txt" => "",
                ".well-known/security.txt" => "",
            ];
            $ignorePages = [
                "apple-app-site-association" => "",
                "cross-platform-app-identifiers" => "",
                ".well-known/assetlinks.json" => "",
                "rss" => "",
                "atom" => "",
                "feed" => "",
                "blog" => "",
            ];
    
            switch (true) {
                case ($exception instanceof \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException):
                    Slack::Notice($content, $fileContent, $fields, "warning");
                    break;
                
                case ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException):
                case ($exception instanceof \Illuminate\Auth\AuthenticationException):
                case ($exception instanceof \Illuminate\Validation\ValidationException):
                case ($exception instanceof \Illuminate\Session\TokenMismatchException):
                Slack::Warning($content, $fileContent, $fields, "warning");
                    break;

                case ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException):
                    if(isset($securityPages[request()->path()]) || strpos(request()->path(), "ini_set") !== false){
                        Slack::Error($content, $fileContent, $fields, "success");
                        abort(418);
                    }else if(!isset($ignorePages[request()->path()])){
                        Slack::Warning($content, $fileContent, $fields, "warning");
                    }
                    break;

                case ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException):
                if(isset($securityPages[request()->path()]) || strpos(request()->path(), "ini_set") !== false){
                }else{
                    Slack::Warning($content, $fileContent, $fields, "warning");
                }
                break;

                default:
                    Slack::Critical($content, $fileContent, $fields, "error");
                    break;
            }
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if(config('app.debug')){
            return parent::render($request, $exception);
        }

        if($this->isHttpException($exception)){
            switch(true){
                case ($exception->getStatusCode() == 404):
                    return response()->view('errors/404', [], 404);
            }
        }

        return response()->view('errors/500', [], 500);
        // return parent::render($request, $exception);
    }
}

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
        $content = $exception->getMessage() . "\n" . get_class($exception) . "\n" . url()->full();
        $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $_SERVER['HTTP_USER_AGENT'] . "\nReferer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "N/A") . "\n\n";
        if(!is_null($user)){
            $fileContent .= "User:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\n";
            $fields = ["File" => $exception->getFile(), "Line" => $exception->getLine(), "IP Address" => \Request::ip(), "User id" => $user->id];
        }else{
            $fileContent .= "User: N/A\n\n";
            $fields = ["File" => $exception->getFile(), "Line" => $exception->getLine(), "IP Address" => \Request::ip(), "User id" => "N/A"];
        }
        $fileContent .= "Cookie:\n" . var_export(Cookie::get(), true) . "\n\nRequest:\n" . var_export(Request::all(), true) . "\n\n" . $exception->__toString();

        switch (true) {
            case ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException):
                Slack::Warning($content, $fileContent, $fields, "warning");
                break;
            
            case ($exception instanceof \Illuminate\Auth\AuthenticationException):
                Slack::Notice($content, $fileContent, $fields, "warning");
                break;
            
            default:
                Slack::Critical($content, $fileContent, $fields, "error");
                break;
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
        return parent::render($request, $exception);
    }
}

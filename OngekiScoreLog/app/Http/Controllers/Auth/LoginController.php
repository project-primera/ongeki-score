<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * POST /logout（Auth::routes()）の後は GET /logout へリダイレクトし、旧版と同じ「ログアウトしました」の画面を出す。
     * POST の応答として画面を返すと、再読み込みや「戻る」でフォームの再送確認が出るので、GET に逃がす
     */
    protected function loggedOut(Request $request)
    {
        return redirect('/logout');
    }
}

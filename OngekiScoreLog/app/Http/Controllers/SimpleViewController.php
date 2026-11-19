<?php

namespace App\Http\Controllers;

use App;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SimpleViewController extends Controller
{
    public function getIndex(){
        return view('top');
    }

    public function getHowto(){
        return view('howto');
    }

    public function getFAQ(){
        return view('faq');
    }

    public function getEula(){
        return view('eula');
    }

    public function getDataPolicy(){
        return view('data-policy');
    }


    public function getChangelog(){
        $v = new App\ApplicationVersion();
        $version = $v->getAllVersion();
        return view('changelog', compact('version'));
    }

    /**
     * ログアウトは CSRF 付きの POST /logout（Auth::routes() が登録、LoginController::loggedOut() がここへリダイレクト）でだけ行う。
     * 旧版は GET でログアウトしていたので、他サイトのリンクを踏ませるだけでログアウトさせられた。
     * GET はログアウト後（未ログイン）なら「ログアウトしました」の画面、ログイン中に直接開かれたときは何もせずトップへ戻す
     */
    public function getLogout(){
        if (Auth::check()) {
            return redirect('/');
        }
        return view('logout');
    }

    /**
     * 保存されたログファイルの内容を、加工せずに text/plain で返す。
     * ログには訪問者が決められる文字列（User-Agent や入力値など）が入るので、HTML として解釈させない。
     * 中身を読めないファイルは、空の本文で返す
     */
    public function getLogFile($path, $fileName){
        $file = 'log/' . $path . '/' . $fileName;
        if (!Storage::exists($file)) {
            throw new \Illuminate\Contracts\Filesystem\FileNotFoundException("File does not exist at path {$file}.");
        }

        return response(Storage::get($file) ?? '', 200, [
            'Content-Type' => 'text/plain; charset=UTF-8', // 本文を HTML として解釈させない
            'X-Content-Type-Options' => 'nosniff', // 中身から種類を推測させない
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox", // Content-Type が失われたときの備え
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0', // ブラウザや中継に残さない
        ]);
    }

    /* for debug
    public function versionUpdate(){
        (new App\ApplicationVersion())->fetchAllVersion();
    }
    */

}

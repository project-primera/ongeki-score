<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getSetting(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $display = [];
        $display['private'] = $user->private;

        return view('setting', compact('display'));
    }

    /**
     * 切り替えは CSRF トークン付きの POST でだけ受け付ける。
     * 旧版は GET だったので、他サイトのリンクを踏ませるだけでログイン中の利用者の公開/非公開を変えられた。
     * GET で直接開かれたときは設定ページへ戻す
     */
    public function redirectToSetting(){
        return redirect("/setting");
    }

    public function postSettingPrivate(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $user->private = 1;
        $user->save();

        return redirect("/setting");
    }

    public function postSettingPublic(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $user->private = 0;
        $user->save();

        return redirect("/setting");
    }

}

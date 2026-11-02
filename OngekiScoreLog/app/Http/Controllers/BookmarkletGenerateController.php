<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class BookmarkletGenerateController extends Controller
{
    function getIndex(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        // 生成は CSRF トークン付きの POST でだけ受け付ける。
        // 旧版は GET /bookmarklet/agree を Referer のパスだけで守っていたので、他サイトの /bookmarklet からの遷移で勝手に再生成できた
        $content = '<form method="post" action="/bookmarklet/agree">' . csrf_field() . '<button type="submit" class="button is-info">生成する</button></form>';
        return view('bookmarklet', compact('content'));
    }

    /** GET で直接開かれたときは、旧版の Referer 不一致時と同じく同意ページへ戻す */
    function getAgree(){
        return redirect('/bookmarklet');
    }

    function postBookmarklet(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $userTokens = $user->tokens;
        foreach($userTokens as $token) {
            $token->revoke();
        }

        $tokenobj = $user->createToken('OngekiScoreLog Personal Access Client', ['*']);
        $token = $tokenobj->accessToken;
        $token_id = $tokenobj->token->id;
        $content = config('app.url') . '/bookmarklets/main.js';
        $content = "javascript:(function(d,s){s=d.createElement('script');s.src='" . $content . "?t=" . $token . "';d.getElementsByTagName('head')[0].appendChild(s);})(document);";
        $content = '<p>ブックマークレットを生成しました。</p><button class="btn button is-primary" data-clipboard-target="#bookmarklet">クリップボードにコピー</button><p class="is-small">※環境やブラウザ設定によってはコピーが行えない場合があります。</p><div class="control"><textarea id="bookmarklet" class="textarea is-info is-small" type="text" readonly>' . $content . '</textarea></div></p>';

        return view('bookmarklet', compact('content'));
    }
}

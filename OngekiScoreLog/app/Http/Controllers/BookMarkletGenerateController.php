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

        $content = '<a href="/bookmarklet/agree" class="button is-info">生成する</a>';
        return view('bookmarklet', compact('content'));
    }

    function getBookmarklet(){
        if(!isset($_SERVER['HTTP_REFERER']) || parse_url($_SERVER['HTTP_REFERER'])['path'] !== "/bookmarklet"){
            return redirect('/bookmarklet');
        }
        
        $user = \Auth::user();

        $userTokens = $user->tokens;
        foreach($userTokens as $token) {
            $token->revoke();   
        }
        
        $tokenobj = $user->createToken('OngekiScoreLog Personal Access Client', ['*']);
        $token = $tokenobj->accessToken;
        $token_id = $tokenobj->token->id;
        $content = 'https://ongeki-score.net/bookmarklets/main.js';
        $content = "javascript:(function(d,s){s=d.createElement('script');s.src='" . $content . "?t=" . $token . "';d.getElementsByTagName('head')[0].appendChild(s);})(document);";
        $content = 'ブックマークレットを生成しました。<div class="control"><textarea class="textarea is-info is-small" type="text" readonly>' . $content . '</textarea></div>';
        
        return view('bookmarklet', compact('content'));
    }
}

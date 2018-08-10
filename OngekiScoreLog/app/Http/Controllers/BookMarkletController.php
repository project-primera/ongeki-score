<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class BookmarkletController extends Controller
{
    //
    function getIndex(){
        $user = \Auth::user();

        $userTokens = $user->tokens;
        foreach($userTokens as $token) {
            $token->revoke();   
        }
        

        $tokenobj = $user->createToken('OngekiScoreLog Personal Access Client');
        $token = $tokenobj->accessToken;
        $token_id = $tokenobj->token->id;
        return "javascript:(function(d,s){s=d.createElement('script');s.src='http://127.0.0.1:8001/main.js?" . $token . "';d.getElementsByTagName('head')[0].appendChild(s);})(document);";
        
    }
}

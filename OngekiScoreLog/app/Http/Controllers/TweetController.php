<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;
use App;
use Log;
use App\AdminTweet;

class TweetController extends Controller
{
    public function getUserAccountRequest(){
        session_start();
        
        try {
            if (!isset($_SESSION['state'])) {
                // Step.1 Start
                $_SESSION['client'] = new Client([
                    env("TWITTER_CONSUMER_KEY"),
                    env("TWITTER_CONSUMER_SECRET"),
                ], [CURLOPT_CAINFO => __DIR__ . '/../../../../../resources/cacert.pem']);
                    
                $_SESSION['client'] = $_SESSION['client']->oauthForRequestToken('http://127.0.0.1:8000/twitter/login');
                // $_SESSION['client'] = $_SESSION['client']->oauthForRequestToken('https://ongeki-score.net/twitter/login');
                
                $_SESSION['state'] = 'pending';

                return redirect($_SESSION['client']->getAuthorizeUrl());

            } else {
                $_SESSION['client'] = $_SESSION['client']->oauthForAccessToken(filter_input(INPUT_GET, 'oauth_verifier'));
                $_SESSION['state'] = 'logged-in';
        
                // Redirect to index page
                var_dump($_SESSION['client']);
                /*
                $out["token"] = $_SESSION['client']['credential']['token'];
                $out["token_secret"] = $_SESSION['client']['credential']['token_secret'];
                */
                return "";
            }
        
        } catch (\RuntimeException $e) {
            session_destroy();

            Log::debug($e->getMessage());
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
            echo $e->getMessage();
        }
    }
}

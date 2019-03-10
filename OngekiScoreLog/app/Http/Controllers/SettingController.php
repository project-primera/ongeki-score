<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;
use App\Extensions\Cowitter\LooseClient;
use App\ExternalServiceCoordination;
use App\ExternalServiceStatus;
use Log;

class SettingController extends Controller
{
    public function getSetting(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $external = new ExternalServiceCoordination();
        $ret = $external->get($user->id);

        $display = [];
        if(count($ret) === 0){
            $display['screenName'] = "認証していません";
        }else{
            $twitter = $external->getTwitter($ret[0]->twitter_access_token, $ret[0]->twitter_access_token_secret);
            if(is_null($twitter)){
                $display['screenName'] = "認証していません";
            }else{
                $display['screenName'] = $twitter->screen_name;
            }
        }
        return view('setting', compact('display'));
    }

    public function getTwitterAuthentication(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        session_start();
        try {
            if (!isset($_SESSION['state'])) {
                // Step.1 Start
                $_SESSION['client'] = new LooseClient([
                    config('env.twitter-consumer-key'),
                    config('env.twitter-consumer-secret'),
                ], [CURLOPT_CAINFO => __DIR__ . '/../../../resources/cacert.pem']);
                    
                // $_SESSION['client'] = $_SESSION['client']->oauthForRequestToken('http://127.0.0.1:8000/setting/twitter');
                $_SESSION['client'] = $_SESSION['client']->oauthForRequestToken('https://ongeki-score.net/setting/twitter');
                
                $_SESSION['state'] = 'pending';

                return redirect($_SESSION['client']->getAuthorizeUrl());

            } else {
                $_SESSION['client'] = $_SESSION['client']->oauthForAccessToken(filter_input(INPUT_GET, 'oauth_verifier'));
                $_SESSION['state'] = 'logged-in';
        
                $external = ExternalServiceCoordination::updateOrCreate(
                    [
                        'id' => $user->id
                    ],[
                        'twitter_access_token' => $_SESSION['client']->getToken(),
                        'twitter_access_token_secret' => $_SESSION['client']->getTokenSecret()
                    ]
                );

                $verifyCredentials = $_SESSION['client']->get('account/verify_credentials');
                $external = ExternalServiceStatus::updateOrCreate(
                    [
                        'id' => $user->id
                    ],[
                        'twitter_id' => $verifyCredentials->id_str,
                        'twitter_screen_name' => $verifyCredentials->screen_name
                    ]
                );

                session_destroy();
                return redirect("/setting");
            }
        
        } catch (\RuntimeException $e) {
            session_destroy();

            Log::debug($e->getMessage());
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
            echo $e->getMessage();
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;

use App\ExternalServiceCoordination;

use Log;

class TweetController extends Controller
{
    function postTweetImage(Request $request){
        $user = \Auth::user();

        $external = new ExternalServiceCoordination();
        $ret = $external->get($user->id);
        try{
            $client = new Client([
                env("TWITTER_CONSUMER_KEY"),
                env("TWITTER_CONSUMER_SECRET"),
                $ret[0]->twitter_access_token,
                $ret[0]->twitter_access_token_secret
            ], [CURLOPT_CAINFO => __DIR__ . '/../../../resources/cacert.pem']);
            $ids = [
                $client->postMultipart('media/upload', ['media_data' => $request->img])->media_id_string,
            ];
            $client->post('statuses/update', [
                'status' => $request->status,
                'media_ids' => implode(',', $ids),
            ]);
        }catch(\RuntimeException $e){
            Log::debug($e);
            $result = "ツイートに失敗しました。この画面を添えてご報告いただけますと幸いです。 id: " . $user->id . " / time: " . date(DATE_ATOM);
            return view("tweet_result", compact('result'));
        }
        
        $result = "ツイートに成功しました！";
        return view("tweet_result", compact('result'));
    }
}

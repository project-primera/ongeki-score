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

        $firstTweetID = null;
        $tweetID = null;

        $external = new ExternalServiceCoordination();
        $ret = $external->get($user->id);
        try{
            $client = new Client([
                env("TWITTER_CONSUMER_KEY"),
                env("TWITTER_CONSUMER_SECRET"),
                $ret[0]->twitter_access_token,
                $ret[0]->twitter_access_token_secret
            ], [CURLOPT_CAINFO => __DIR__ . '/../../../resources/cacert.pem']);

            $count = count($request->img);
            for ($i = 0; $i < $count; $i += 4) { 
                $ids = [
                    (isset($request->img[$i + 0])) ? $client->postMultipart('media/upload', ['media_data' => $request->img[$i + 0]])->media_id_string : null,
                    (isset($request->img[$i + 1])) ? $client->postMultipart('media/upload', ['media_data' => $request->img[$i + 1]])->media_id_string : null,
                    (isset($request->img[$i + 2])) ? $client->postMultipart('media/upload', ['media_data' => $request->img[$i + 2]])->media_id_string : null,
                    (isset($request->img[$i + 3])) ? $client->postMultipart('media/upload', ['media_data' => $request->img[$i + 3]])->media_id_string : null,
                ];

                if($tweetID == null){
                    $response = $client->post('statuses/update', [
                        'status' => $request->status,
                        'media_ids' => implode(',', $ids),
                    ]);
                }else{
                    $response = $client->post('statuses/update', [
                        'media_ids' => implode(',', $ids),
                        'in_reply_to_status_id' => $tweetID
                    ]);
                }

                $tweetID = $response->id_str;
                if($firstTweetID == null){
                    $firstTweetID = $response->id_str;
                }
            }
        }catch(\RuntimeException $e){
            Log::debug($e);
            $result = "ツイートに失敗しました。この画面を添えてご報告いただけますと幸いです。 id: " . $user->id . " / time: " . date(DATE_ATOM);
            return view("tweet_result", compact('result'));
        }
        
        $result = "以下の内容をツイートしました！画像が４枚を超える場合はインリプライに続きます。(鍵アカウントの場合は表示されませんが正常にツイートされています。)";
        return view("tweet_result", compact('result', 'firstTweetID'));
    }
}

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
use App\Facades\Slack;

use Log;

set_time_limit(180);

class TweetController extends Controller
{
    function getIndex(){
        return redirect("/");
    }

    function postTweetImage(Request $request){
        $user = \Auth::user();

        $url = null;
        $firstTweetID = null;
        $tweetID = null;

        $external = new ExternalServiceCoordination();
        $ret = $external->get($user->id);
        try{
            $client = new Client([
                config('env.twitter-consumer-key'),
                config('env.twitter-consumer-secret'),
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
                    $url = "https://twitter.com/" . $response->user->screen_name . "/status/" . $response->id_str;
                    $firstTweetID = $response->id_str;
                }
            }
        }catch(\RuntimeException $e){
            Log::debug($e);
            $result = "<p>ツイートに失敗しました。この画面を添えてご報告いただけますと幸いです。<br>id: " . $user->id . " / time: " . date(DATE_ATOM) . "</p>";
            return view("tweet_result", compact('result'));
        }

        Slack::Info("ツイート: (" . $user->id . ") " . $url);
        
        $result = "<p>以下の内容をツイートしました！画像が４枚を超える場合はインリプライに続きます。(鍵アカウントの場合は表示されませんが正常にツイートされています。)<br><a href='" . $url . "' target='_blank'>Twitterで確認する</a></p>";
        
        return view("tweet_result", compact('result', 'firstTweetID'));
    }
}

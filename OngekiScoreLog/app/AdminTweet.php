<?php

namespace App;

use Illuminate\Http\Request;
use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;
use Illuminate\Database\Eloquent\Model;
use App\ApplicationVersion;
use Log;

class AdminTweet extends Model
{
    public function tweet(string $status, $inReplyToStatusID = null){
        try{
            $client = new Client([
                config('env.twitter-consumer-key'),
                config('env.twitter-consumer-secret'),
                config('env.twitter-admin-account-access-token'),
                config('env.twitter-admin-account-access-token-secret'),
            ], [CURLOPT_CAINFO => __DIR__ . '/../resources/cacert.pem']);
            if(is_null($inReplyToStatusID)){
                $response = $client->post('statuses/update', ['status' => $status]);
            }else{
                $response = $client->post('statuses/update', ['status' => $status, "in_reply_to_status_id" => $inReplyToStatusID]);
            }
            return $response;

        } catch (\RuntimeException $e) {
            Log::debug($e->getMessage());
        }
    }

    public function tweetMusicUpdate($musicTitles){
        $tweets = ["#楽曲情報追加\n"];

        foreach ($musicTitles as $key => $value) {
            if(mb_strlen($tweets[count($tweets) - 1] . $value . "\n") <= 140){
                $tweets[count($tweets) - 1] .= ($value . "\n");
            }else{
                $tweets[] = ("#楽曲情報追加\n" . $value . "\n");
            }
        }

        $id = null;
        foreach ($tweets as $key => $value) {
            $response = $this->tweet($value, $id);
            if(!is_null($response) && array_key_exists("id_str", $response)){
                $id = $response->id_str;
            }
        }
    }


    public function tweetLatestVersion(){
        $v = new ApplicationVersion();
        $v->fetchAllVersion();
        $version = $v->getLatestVersion();
        $status = "#更新履歴 " . $version[0]->name . " (" .$version[0]->tag_name . ")" . "\n" . $version[0]->body;
        if(mb_strlen($status) >= 120){
            $status = mb_substr($status, 0, 120);
            $status .= "...\nhttps://ongeki-score.net/changelog";
        }else{
            $status .= "\nhttps://ongeki-score.net/changelog";
        }

        $this->tweet($status);
    }
}

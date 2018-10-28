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
    public function tweetLatestVersion(){
        try{
            $client = new Client([
                env("TWITTER_CONSUMER_KEY"),
                env("TWITTER_CONSUMER_SECRET"),
                env("TWITTER_ADMIN_ACCOUNT_ACCESS_TOKEN"),
                env("TWITTER_ADMIN_ACCOUNT_ACCESS_TOKEN_SECRET")         
            ], [CURLOPT_CAINFO => __DIR__ . '/../resources/cacert.pem']);
            
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
            $client->post('statuses/update', ['status' => $status]);

        } catch (\RuntimeException $e) {
            Log::debug($e->getMessage());
        }
    }
}

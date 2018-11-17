<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;
use Log;

class ExternalServiceCoordination extends Model{
    
    protected $table = "external_service_coordinations";
    protected $fillable = ['id', 'twitter_access_token', 'twitter_access_token_secret'];

    function get($id){
        $value = DB::table($this->table)->select('*')->from($this->table)->where('id', $id)->get();
        return $value;
    }

    function getTwitter($twitter_access_token, $twitter_access_token_secret){
        try{
            $cowitter = new Client([
                env("TWITTER_CONSUMER_KEY"),
                env("TWITTER_CONSUMER_SECRET"),
                $twitter_access_token,
                $twitter_access_token_secret
            ], [CURLOPT_CAINFO => __DIR__ . '/../resources/cacert.pem']);
            return $cowitter->get('account/verify_credentials');
        }catch(\RuntimeException $ignore){
            return null;
        }
    }
}
<?php

namespace App;

use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApplicationVersion extends Model
{
    protected $table = "application_versions";
    protected $guarded = ['id'];

    public function getLatestVersion(){
        return DB::table("application_versions")->orderBy('id', 'desc')->limit(1)->get();
    }

    public function fetchAllVersion(){
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                        'User-Agent: PHP'
                ]
            ]
        ];
        $context = stream_context_create($opts);
        $response = file_get_contents('https://api.github.com/repos/Slime-hatena/ProjectPrimera/releases', false, stream_context_create($opts));
        $response = json_decode($response, true);
        $response = array_reverse($response);
        
        foreach ($response as $key => $value) {
            $result = DB::table("application_versions")->where('tag_name', $value['tag_name'])->get();
            if(count($result) != 0){
                continue;
            }

            $applicationVersion = new ApplicationVersion();
            $applicationVersion->tag_name = $value['tag_name'];
            $applicationVersion->name = $value['name'];
            $applicationVersion->body = $value['body'];
            $applicationVersion->published_at = (new DateTime($value['published_at']))->setTimeZone(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s');
            $applicationVersion->save();
        }
    }
}

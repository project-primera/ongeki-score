<?php

namespace App;

use App\Http\Controllers;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\AdminTweet;

class ApplicationVersion extends Model
{
    protected $table = "application_versions";
    protected $guarded = ['id'];

    public function getLatestVersion(){
        return DB::table("application_versions")->orderBy('id', 'desc')->limit(1)->get();
    }

    public function getAllVersion(){
        return DB::table("application_versions")->orderBy('id', 'desc')->get();
    }

    public function fetchAllVersion(){
        //
    }
}

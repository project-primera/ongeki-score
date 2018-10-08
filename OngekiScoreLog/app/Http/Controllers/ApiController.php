<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\UserStatus;
use App\CharacterFriendly;
use App\RatingRecentMusic;
use App\UserTrophy;
use App\UniqueIDForRequest;
use App\MusicData;
use App\ScoreData;

use Log;

class ApiController extends Controller
{
    function getUserMusic($id){
        $scoreData = new ScoreData();
        $value = $scoreData->getRecentUserScore($id);
        return $value;
    }

    function getRecentGenerationOfScoreData($id, $songID, $difficulty){
        $scoreData = new ScoreData();
        $value = $scoreData->getRecentGenerationOfScoreData($id, $songID, $difficulty);
        return json_encode($value);
    }

    function getRecentGenerationOfScoreDataAll($id){
        $scoreData = new ScoreData();
        $value = $scoreData->getRecentGenerationOfScoreDataAll($id);
        return $value;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ScoreData;
class ViewUserMusicController extends Controller
{
    function getIndex(int $id, int $music){
        
        
        $score = ScoreData::where('user_id', $id)->where('song_id', $music)->get();



        return $score;
    }
}

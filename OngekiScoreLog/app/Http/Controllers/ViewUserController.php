<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\UserStatus;
use App\ScoreData;

class ViewUserController extends Controller
{
    public function getUserPage($id, $mode = null){
        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);

        $scoreData = new ScoreData();
        $scoreData->getRecentUserScore($id);
        $scoreData->addMusicData();
        $scoreData->addDetailedData();
        $score = $scoreData->value;

        switch (true) {
            case ($mode === "battle"):
                $mode = "song_status_battle";
                break;
            default:
                $mode = "song_status";
                break;
        }

        return view('user', compact('id', 'status', 'score', 'mode'));
    }
}

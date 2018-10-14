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

        $submenuActive = [0 => "", 1 => "", 2 => "", 3 => ""];

        switch (true) {
            case ($mode === "technical"):
                $mode = "song_status_technical";
                $submenuActive[3] = "is-active";
                break;
            case ($mode === "battle"):
                $mode = "song_status_battle";
                $submenuActive[2] = "is-active";
                break;
            case ($mode === "details"):
                $mode = "song_status_details";
                $submenuActive[1] = "is-active";
                break;
            default:
                $mode = "song_status";
                $submenuActive[0] = "is-active";
                break;
        }

        return view('user', compact('id', 'status', 'score', 'mode', 'submenuActive'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\UserStatus;
use App\ScoreData;

class ViewUserController extends Controller
{
    public function getUserPage($id){
        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);

        $scoreData = new ScoreData();
        $score = $scoreData->getRecentUserScore($id);

        return view('user', compact('id', 'status', 'score'));
    }
}

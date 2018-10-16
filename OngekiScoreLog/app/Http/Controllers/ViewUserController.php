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

        $stat = [
            "Basic" => [],
            "Advanced" => [],
            "Expert" => [],
            "Master" => [],
            "Lunatic" => [],
            "1" => [],
            "2" => [],
            "3" => [],
            "4" => [],
            "5" => [],
            "6" => [],
            "7" => [],
            "7+" => [],
            "8" => [],
            "8+" => [],
            "9" => [],
            "9+" => [],
            "10" => [],
            "10+" => [],
            "11" => [],
            "11+" => [],
            "12" => [],
            "12+" => [],
            "13" => [],
            "13+" => [],
            "14" => [],
        ];

		

        foreach ($score as $key => $value) {
            if($value->technical_high_score == 0){
                $key = "NP";
            }else if($value->technical_high_score < 850000){
                $key = "B";
            }else{
                $key = $value->technical_high_score_rank;
            }
            if(!isset($stat[$value->difficulty_str][$key])){
				$stat[$value->difficulty_str][$key] = 0;
            }
            $stat[$value->difficulty_str][$key] += 1;
            
            if(!isset($stat[$value->difficulty_str][$value->over_damage_high_score_rank])){
				$stat[$value->difficulty_str][$value->over_damage_high_score_rank] = 0;
            }
            $stat[$value->difficulty_str][$value->over_damage_high_score_rank] += 1;

            if(!isset($stat[$value->difficulty_str]["fc"])){
				$stat[$value->difficulty_str]["fc"] = 0;
            }
            $stat[$value->difficulty_str]["fc"] += $value->full_combo;
            
            if(!isset($stat[$value->difficulty_str]["ab"])){
				$stat[$value->difficulty_str]["ab"] = 0;
            }
            $stat[$value->difficulty_str]["ab"] += $value->all_break;
            
            if(!isset($stat[$value->difficulty_str]["fb"])){
				$stat[$value->difficulty_str]["fb"] = 0;
            }
			$stat[$value->difficulty_str]["fb"] += $value->full_bell;
            
            
            if($value->technical_high_score == 0){
                $key = "NP";
            }else if($value->technical_high_score < 850000){
                $key = "B";
            }else{
                $key = $value->technical_high_score_rank;
            }
            if(!isset($stat[$value->level_str][$key])){
				$stat[$value->level_str][$key] = 0;
            }
            $stat[$value->level_str][$key] += 1;

            if(!isset($stat[$value->level_str][$value->over_damage_high_score_rank])){
				$stat[$value->level_str][$value->over_damage_high_score_rank] = 0;
            }
            $stat[$value->level_str][$value->over_damage_high_score_rank] += 1;

            if(!isset($stat[$value->level_str]["fc"])){
				$stat[$value->level_str]["fc"] = 0;
            }
            $stat[$value->level_str]["fc"] += $value->full_combo;
            
            if(!isset($stat[$value->level_str]["ab"])){
				$stat[$value->level_str]["ab"] = 0;
            }
            $stat[$value->level_str]["ab"] += $value->all_break;
            
            if(!isset($stat[$value->level_str]["fb"])){
				$stat[$value->level_str]["fb"] = 0;
            }
			$stat[$value->level_str]["fb"] += $value->full_bell;
        }

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

        return view('user', compact('id', 'status', 'score', 'stat', 'mode', 'submenuActive'));
    }
}

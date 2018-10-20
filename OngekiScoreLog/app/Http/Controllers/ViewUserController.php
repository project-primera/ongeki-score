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

        array_multisort(array_column($score, 'updated_at'), SORT_DESC, $score);

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

        
        $stat = [
            "Basic" => [],
            "Advanced" => [],
            "Expert" => [],
            "Master" => [],
            "Lunatic" => [],

        ];
        if($mode == "song_status_details"){
            $stat += [
                "Lv.1" => [],
                "Lv.2" => [],
                "Lv.3" => [],
                "Lv.4" => [],
                "Lv.5" => [],
                "Lv.6" => [],
                "Lv.7" => [],
                "Lv.7+" => [],
                "Lv.8" => [],
                "Lv.8+" => [],
                "Lv.9" => [],
                "Lv.9+" => [],
                "Lv.10" => [],
                "Lv.10+" => [],
                "Lv.11" => [],
                "Lv.11+" => [],
                "Lv.12" => [],
                "Lv.12+" => [],
                "Lv.13" => [],
                "Lv.13+" => [],
                "Lv.14" => [],
            ];
        }


		

        foreach ($score as $key => $value) {
            if($value->full_bell && $value->all_break){
                $score[$key]->rawLamp = "FB+FC+AB";
            }else if($value->full_bell && $value->full_combo){
                $score[$key]->rawLamp = "FB+FC";
            }else if($value->all_break){
                $score[$key]->rawLamp = "FC+AB";
            }else if($value->full_combo){
                $score[$key]->rawLamp = "FC";
            }else if($value->full_bell){
                $score[$key]->rawLamp = "FB";
            }else{
                $score[$key]->rawLamp = "-";
            }
            

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
            

            if($mode == "song_status_details"){
                if($value->technical_high_score == 0){
                    $key = "NP";
                }else if($value->technical_high_score < 850000){
                    $key = "B";
                }else{
                    $key = $value->technical_high_score_rank;
                }
                if(!isset($stat["Lv." . $value->level_str][$key])){
                    $stat["Lv." . $value->level_str][$key] = 0;
                }
                $stat["Lv." . $value->level_str][$key] += 1;

                if(!isset($stat["Lv." . $value->level_str][$value->over_damage_high_score_rank])){
                    $stat["Lv." . $value->level_str][$value->over_damage_high_score_rank] = 0;
                }
                $stat["Lv." . $value->level_str][$value->over_damage_high_score_rank] += 1;

                if(!isset($stat["Lv." . $value->level_str]["fc"])){
                    $stat["Lv." . $value->level_str]["fc"] = 0;
                }
                $stat["Lv." . $value->level_str]["fc"] += $value->full_combo;
                
                if(!isset($stat["Lv." . $value->level_str]["ab"])){
                    $stat["Lv." . $value->level_str]["ab"] = 0;
                }
                $stat["Lv." . $value->level_str]["ab"] += $value->all_break;
                
                if(!isset($stat["Lv." . $value->level_str]["fb"])){
                    $stat["Lv." . $value->level_str]["fb"] = 0;
                }
                $stat["Lv." . $value->level_str]["fb"] += $value->full_bell;
            }
        }

        return view('user', compact('id', 'status', 'score', 'stat', 'mode', 'submenuActive'));
    }
}

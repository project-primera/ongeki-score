<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserStatus;
use App\ScoreData;
use App\ApplicationVersion;

class ViewUserProgressController extends Controller
{
    function getIndex($id){
        function shapingKeys($array){
            $ret = [];
            foreach ($array as $key => $value) {
                $ret[$value->song_id][$value->difficulty] = $value;
            }
            return $ret;
        }

        $version = (new ApplicationVersion())->getLatestVersion();
        $version = isset($version[0]->tag_name) ? $version[0]->tag_name : "";

        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);

        $oldScoreData = new ScoreData();
        $newScoreData = new ScoreData();
        $progress = [];

        $date = ['new' => 0, 'old' => 0];
        $score = [
            'new' =>[
                'Basic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Advanced' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Expert' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Master' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Lunatic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
            ],
            'old' =>[
                'Basic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Advanced' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Expert' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Master' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Lunatic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
            ]
        ];
        $difficultyToStr = [
            0 => 'Basic',
            1 => 'Advanced',
            2 => 'Expert',
            3 => 'Master',
            10 => 'Lunatic',
        ];

        $oldScoreData->getSpecifiedGenerationUserScore($id, $oldScoreData->getMaxGeneration($id));
        $oldScoreData->addDetailedData();
        $old = shapingKeys($oldScoreData->value);

        $newScoreData->getRecentUserScore($id);
        $newScoreData->addMusicData();
        $newScoreData->addDetailedData();
        $new = shapingKeys($newScoreData->value);

        foreach ($new as $music => $temp) {
            foreach ($temp as $difficulty => $value) {
                $score['new'][$difficultyToStr[$difficulty]]['battle_high_score'] += $value->battle_high_score;
                $score['new'][$difficultyToStr[$difficulty]]['technical_high_score'] += $value->technical_high_score;

                if(!array_key_exists($music, $old) || !array_key_exists($difficulty, $old[$music])){
                    if($value->battle_high_score !== 0){
                        // not implemented → played
                        // echo "[new] " . $value->title . " / " . $value->difficulty_str . "<br>";
                        $progress[$music][$difficulty]["new"] = $value;
                        $progress[$music][$difficulty]["difference"]['battle_high_score'] = "+" . number_format($value->battle_high_score);
                        $progress[$music][$difficulty]["difference"]['technical_high_score'] = "+" . number_format($value->technical_high_score);
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                        $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = "N" . " → " . $value->technical_high_score_rank;
                        $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = "update";
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = "不可" . " → " . $value->over_damage_high_score_rank;
                        $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = "update";
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                        $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = "not-light";
                        $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = "not-light";
                        $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = "not-light";
                        $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                        $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                        $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";
                        
                    }
                }else{
                    $score['old'][$difficultyToStr[$difficulty]]['battle_high_score'] += $old[$music][$difficulty]->battle_high_score;
                    $score['old'][$difficultyToStr[$difficulty]]['technical_high_score'] += $old[$music][$difficulty]->technical_high_score;

                    if($old[$music][$difficulty]->over_damage_high_score < $value->over_damage_high_score
                    || $old[$music][$difficulty]->battle_high_score < $value->battle_high_score
                    || $old[$music][$difficulty]->technical_high_score < $value->technical_high_score
                    || $old[$music][$difficulty]->full_bell < $value->full_bell
                    || $old[$music][$difficulty]->full_combo < $value->full_combo
                    || $old[$music][$difficulty]->all_break < $value->all_break){
                        if($old[$music][$difficulty]->battle_high_score === 0){
                            // noplay → played
                            // echo "[new*] " . $value->title . " / " . $value->difficulty_str . "<br>";
                            $progress[$music][$difficulty]["new"] = $value;
                            $progress[$music][$difficulty]["difference"]['battle_high_score'] = "+" . number_format($value->battle_high_score);
                            $progress[$music][$difficulty]["difference"]['technical_high_score'] = "+" . number_format($value->technical_high_score);
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                            $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = "N" . " → " . $value->technical_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = "update";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = "N" . " → " . $value->over_damage_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = "update";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";

                        }else{
                            // played → played
                            // echo "[update] " . $value->title . " / " . $value->difficulty_str . "<br>";
                            $progress[$music][$difficulty]["difference"]['battle_high_score'] = ($value->battle_high_score - $old[$music][$difficulty]->battle_high_score) != 0 ? "+" . number_format($value->battle_high_score - $old[$music][$difficulty]->battle_high_score) : "";
                            $progress[$music][$difficulty]["difference"]['technical_high_score'] = ($value->technical_high_score - $old[$music][$difficulty]->technical_high_score) != 0 ? "+" . number_format($value->technical_high_score - $old[$music][$difficulty]->technical_high_score) : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) != 0 ? "+" . ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) . "%" : "";
                            $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = $old[$music][$difficulty]->technical_high_score_rank . " → " . $value->technical_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = ($old[$music][$difficulty]->technical_high_score_rank != $value->technical_high_score_rank) ? "update" : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = $old[$music][$difficulty]->over_damage_high_score_rank . " → " . $value->over_damage_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = ($old[$music][$difficulty]->over_damage_high_score_rank != $value->over_damage_high_score_rank) ? "update" : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) != 0 ? "+" . ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) . "%" : "";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = $old[$music][$difficulty]->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = $old[$music][$difficulty]->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = $old[$music][$difficulty]->all_break ? "all-break" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";
                            


                            $progress[$music][$difficulty]["new"] = $value;
                            if($date['new'] < strtotime($value->updated_at)){
                                $date['new'] = strtotime($value->updated_at);
                            }
                            if($date['old'] < strtotime($old[$music][$difficulty]->updated_at)){
                                $date['old'] = strtotime($old[$music][$difficulty]->updated_at);
                            }
                        }
                    }
                }
            }
        }
        $score['new']['Total']['battle_high_score'] = $score['new']['Basic']['battle_high_score'] + $score['new']['Advanced']['battle_high_score'] + $score['new']['Expert']['battle_high_score'] + $score['new']['Master']['battle_high_score'] + $score['new']['Lunatic']['battle_high_score'];
        $score['new']['Total']['technical_high_score'] = $score['new']['Basic']['technical_high_score'] + $score['new']['Advanced']['technical_high_score'] + $score['new']['Expert']['technical_high_score'] + $score['new']['Master']['technical_high_score'] + $score['new']['Lunatic']['technical_high_score'];

        $score['old']['Total']['battle_high_score'] = $score['old']['Basic']['battle_high_score'] + $score['old']['Advanced']['battle_high_score'] + $score['old']['Expert']['battle_high_score'] + $score['old']['Master']['battle_high_score'] + $score['old']['Lunatic']['battle_high_score'];
        $score['old']['Total']['technical_high_score'] = $score['old']['Basic']['technical_high_score'] + $score['old']['Advanced']['technical_high_score'] + $score['old']['Expert']['technical_high_score'] + $score['old']['Master']['technical_high_score'] + $score['old']['Lunatic']['technical_high_score'];

        $score['difference'] = [
            'Total' => [
                'battle_high_score' => $score['new']['Total']['battle_high_score'] - $score['old']['Total']['battle_high_score'],
                'technical_high_score' => $score['new']['Total']['technical_high_score'] - $score['old']['Total']['technical_high_score'],
            ],
            'Basic' => [
                'battle_high_score' => $score['new']['Basic']['battle_high_score'] - $score['old']['Basic']['battle_high_score'],
                'technical_high_score' => $score['new']['Basic']['technical_high_score'] - $score['old']['Basic']['technical_high_score'],
            ],
            'Advanced' => [
                'battle_high_score' => $score['new']['Advanced']['battle_high_score'] - $score['old']['Advanced']['battle_high_score'],
                'technical_high_score' => $score['new']['Advanced']['technical_high_score'] - $score['old']['Advanced']['technical_high_score'],
            ],
            'Expert' => [
                'battle_high_score' => $score['new']['Expert']['battle_high_score'] - $score['old']['Expert']['battle_high_score'],
                'technical_high_score' => $score['new']['Expert']['technical_high_score'] - $score['old']['Expert']['technical_high_score'],
            ],
            'Master' => [
                'battle_high_score' => $score['new']['Master']['battle_high_score'] - $score['old']['Master']['battle_high_score'],
                'technical_high_score' => $score['new']['Master']['technical_high_score'] - $score['old']['Master']['technical_high_score'],
            ],
            'Lunatic' => [
                'battle_high_score' => $score['new']['Lunatic']['battle_high_score'] - $score['old']['Lunatic']['battle_high_score'],
                'technical_high_score' => $score['new']['Lunatic']['technical_high_score'] - $score['old']['Lunatic']['technical_high_score'],
            ]
        ];

        $date['new'] = date("Y/m/d H:i" ,$date['new']);
        $date['old'] = date("Y/m/d H:i" ,$date['old']);

        var_dump($version);
        return view('user_progress', compact('status', 'progress', 'date', 'score', 'version'));
    }
}

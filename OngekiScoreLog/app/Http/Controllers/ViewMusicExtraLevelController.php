<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MusicData;

class ViewMusicExtraLevelController extends Controller
{
    function getIndex(){
        $music = (new MusicData())->getEstimateExtraLevel();
        
        $view = [];
        $keys = [
            'basic' => ['basic_level', 'basic_extra_level', 'basic_extra_level_estimated', 0],
            'advanced' => ['advanced_level', 'advanced_extra_level', 'advanced_extra_level_estimated', 1],
            'expert' => ['expert_level', 'expert_extra_level', 'expert_extra_level_estimated', 2],
            'master' => ['master_level', 'master_extra_level', 'master_extra_level_estimated', 3],
            'lunatic' => ['lunatic_level', 'lunatic_extra_level', 'lunatic_extra_level_estimated', 4],
        ];

        foreach ($music as $value) {
            foreach ($keys as $k => $v) {
                if($value[$v[0]] !== null){
                    $temp['title'] = $value['title'];
                    $temp['difficulty'] = ucwords(strtolower($k));
                    if(strpos($value[$v[0]], ".5") !== false){
                        $temp['level'] = substr($value[$v[0]], 0, strcspn($value[$v[0]],'.')) . "+";
                    }else{
                        $temp['level'] = substr($value[$v[0]], 0, strcspn($value[$v[0]],'.'));
                    }
                    if($value[$v[2]]){
                        $temp['extra_level'] = "<i><span class='estimated-rating'>" . sprintf('%.1f', $value[$v[1]]) . "</span></i>";
                    }else{
                        $temp['extra_level'] = sprintf('%.1f', $value[$v[1]]);
                    }
                    $temp['extra_level_raw'] = sprintf('%.1f', $value[$v[1]]);
                    $temp['difficulty_raw'] = $v[3];

                    $view[] = $temp;
                }
            }
        }

        return view("music_list", compact('view'));
    }
}

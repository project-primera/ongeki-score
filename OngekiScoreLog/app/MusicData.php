<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MusicData extends Model
{
    protected $table = "music_datas";
    protected $guarded = ['id'];

    function getEstimateExtraLevel(){
        $music = MusicData::all();
        
        $keys = [
            ['basic_level', 'basic_extra_level'],
            ['advanced_level', 'advanced_extra_level'],
            ['expert_level', 'expert_extra_level'],
            ['master_level', 'master_extra_level'],
            ['lunatic_level', 'lunatic_extra_level'],
        ];

        // 表示用データに加工
        foreach ($music as $key => $value) {
            // 譜面定数が未定義なら暫定定数を入れる
            foreach ($keys as $k) {
                $music[$key][$k[1]."_estimated"] = false;

                if(is_null($value[$k[1]]) && !is_null($value[$k[0]])){
                    $music[$key][$k[1]] = floor($value[$k[0]]);
                    if(strpos($value[$k[0]], ".5") !== false){
                        $music[$key][$k[1]] += 0.7;
                    }
                    $music[$key][$k[1]."_estimated"] = true;
                }else if(is_null($value[$k[0]])){
                    $music[$key][$k[0]] = null;
                    $music[$key][$k[1]] = null;
                    $music[$key][$k[1]."_estimated"] = false;
                }
            }
        }
        
        return $music;
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MusicData extends Model
{
    protected $table = "music_datas";
    protected $guarded = ['id'];

    private $sameMusicList = [];
    private $firstDraftMusicList = [];

    function getSameMusicList()
    {
        if (count($this->sameMusicList) === 0) {
            $array = MusicData::groupBy('title')->having(DB::raw('count(*)'), '>', 1)->get(['title'])->toArray();
            foreach ($array as $value) {
                $this->sameMusicList[] = $value['title'];
            }
        }
        return $this->sameMusicList;
    }

    function getFirstDraftMusicList()
    {
        if (count($this->firstDraftMusicList) === 0) {
            $array = MusicData::where('artist', '初稿')->get();
            foreach ($array as $value) {
                $this->firstDraftMusicList[] = $value['title'];
            }
        }
        return $this->firstDraftMusicList;
    }

    function getEstimateExtraLevel(){
        $music = MusicData::all();

        $keys = [
            ['basic_level', 'basic_extra_level', 'basic_level_str'],
            ['advanced_level', 'advanced_extra_level', 'advanced_level_str'],
            ['expert_level', 'expert_extra_level', 'expert_level_str'],
            ['master_level', 'master_extra_level', 'master_level_str'],
            ['lunatic_level', 'lunatic_extra_level', 'lunatic_level_str'],
        ];

        // 表示用データに加工
        foreach ($music as $key => $value) {
            foreach ($keys as $k) {
                // 譜面定数が未定義なら暫定定数を入れる
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

                // レベルのstr版を増やす
                if(is_null($value[$k[0]])){
                    $music[$key][$k[2]] = null;
                }else if(strpos($value[$k[0]], ".5") !== false){
                    $music[$key][$k[2]] = substr($music[$key][$k[0]], 0, strpos($value[$k[0]], ".")) . "+";
                }else{
                    $music[$key][$k[2]] = substr($music[$key][$k[0]], 0, strpos($value[$k[0]], "."));
                }
            }
        }
        return $music;
    }

}

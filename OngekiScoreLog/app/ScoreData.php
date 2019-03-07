<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\MusicData;

class ScoreData extends Model
{
    //
    protected $table = "score_datas";
    protected $guarded = ['id', 'user_id'];
    public $value;

    function addMusicData(){
        $temp = MusicData::all();
        
        foreach ($temp as $key => $value) {
            $title[$value->id] = $value;
        }

        foreach ($this->value as $key => $value) {
            // song_id
            $this->value[$key]->title = $title[$value->song_id]->title;
            $this->value[$key]->genre = $title[$value->song_id]->genre;

            switch (true) {
                case ($this->value[$key]->difficulty === 0):
                    $this->value[$key]->difficulty_str = "Basic";
                    $this->value[$key]->level_str = $title[$value->song_id]->basic_level;
                    $this->value[$key]->level = (float)$title[$value->song_id]->basic_level;
                    break;
                case ($this->value[$key]->difficulty === 1):
                    $this->value[$key]->difficulty_str = "Advanced";
                    $this->value[$key]->level_str = $title[$value->song_id]->advanced_level;
                    $this->value[$key]->level = (float)$title[$value->song_id]->advanced_level;
                    break;
                case ($this->value[$key]->difficulty === 2):
                    $this->value[$key]->difficulty_str = "Expert";
                    $this->value[$key]->level_str = $title[$value->song_id]->expert_level;
                    $this->value[$key]->level = (float)$title[$value->song_id]->expert_level;
                    break;
                case ($this->value[$key]->difficulty === 3):
                    $this->value[$key]->difficulty_str = "Master";
                    $this->value[$key]->level_str = $title[$value->song_id]->master_level;
                    $this->value[$key]->level = (float)$title[$value->song_id]->master_level;
                    break;
                case ($this->value[$key]->difficulty === 10):
                    $this->value[$key]->difficulty_str = "Lunatic";
                    $this->value[$key]->level_str = $title[$value->song_id]->lunatic_level;
                    $this->value[$key]->level = (float)$title[$value->song_id]->lunatic_level;
                    break;
            }

            // $this->value[$key]->level % 1.0 === 0.0
            if(strpos($this->value[$key]->level_str, '.5') === false){
                $this->value[$key]->level_str = (string)$this->value[$key]->level;
            }else{
                $this->value[$key]->level_str = (string)(int)$this->value[$key]->level . "+";
            }
        }

        return $this->value;
    }

    function addDetailedData(){
        foreach ($this->value as $key => $value) {
            $this->value[$key]->over_damage_high_score_next = floor(fmod($this->value[$key]->over_damage_high_score, 100.0) - 100.0 * pow(10, 3)) / pow(10, 3);
            
            if($this->value[$key]->battle_high_score == 0){
                $this->value[$key]->over_damage_high_score_rank = "-";
            }else if($this->value[$key]->over_damage_high_score <= 0){
                $this->value[$key]->over_damage_high_score_rank = "不可";
            }else if($this->value[$key]->over_damage_high_score < 100){
                $this->value[$key]->over_damage_high_score_rank = "可";
            }else if($this->value[$key]->over_damage_high_score < 200){
                $this->value[$key]->over_damage_high_score_rank = "良";
            }else if($this->value[$key]->over_damage_high_score < 300){
                $this->value[$key]->over_damage_high_score_rank = "優";
            }else if($this->value[$key]->over_damage_high_score < 400){
                $this->value[$key]->over_damage_high_score_rank = "秀";
            }else if($this->value[$key]->over_damage_high_score < 500){
                $this->value[$key]->over_damage_high_score_rank = "極";
            }else{
                $this->value[$key]->over_damage_high_score_rank = "極+";
                $this->value[$key]->over_damage_high_score_next = 0.0;
            }

            // TODO: A以下未検証
            if($this->value[$key]->technical_high_score == 0){
                $this->value[$key]->technical_high_score_rank = "-";
            $this->value[$key]->technical_high_score_next = 0;

            }else if($this->value[$key]->technical_high_score < 850000){
                $this->value[$key]->technical_high_score_rank = "B";
                $this->value[$key]->technical_high_score_next = $this->value[$key]->technical_high_score - 850000;


            }else if($this->value[$key]->technical_high_score < 900000){
                $this->value[$key]->technical_high_score_rank = "A";
                $this->value[$key]->technical_high_score_next = $this->value[$key]->technical_high_score - 900000;

            }else if($this->value[$key]->technical_high_score < 940000){
                $this->value[$key]->technical_high_score_rank = "AA";
                $this->value[$key]->technical_high_score_next = $this->value[$key]->technical_high_score - 940000;

            }else if($this->value[$key]->technical_high_score < 970000){
                $this->value[$key]->technical_high_score_rank = "AAA";
                $this->value[$key]->technical_high_score_next = $this->value[$key]->technical_high_score - 970000;

            }else if($this->value[$key]->technical_high_score < 990000){
                $this->value[$key]->technical_high_score_rank = "S";
                $this->value[$key]->technical_high_score_next = $this->value[$key]->technical_high_score - 990000;

            }else if($this->value[$key]->technical_high_score < 1000000){
                $this->value[$key]->technical_high_score_rank = "SS"; 
                $this->value[$key]->technical_high_score_next = $this->value[$key]->technical_high_score - 1000000;

            }else if($this->value[$key]->technical_high_score < 1007500){
                $this->value[$key]->technical_high_score_rank = "SSS";
                $this->value[$key]->technical_high_score_next = $this->value[$key]->technical_high_score - 1007500;

            }else if($this->value[$key]->technical_high_score < 1010000){
                $this->value[$key]->technical_high_score_rank = "SSS+";
                $this->value[$key]->technical_high_score_next = 0;

            }else{
                $this->value[$key]->technical_high_score_rank = "P";
                $this->value[$key]->technical_high_score_next = 0;

            }
        }

        return $this->value;
    }

    function getRecentGenerationOfScoreData($id, $songID, $difficulty){
        $sql = DB::table($this->table)->select('*')
        ->from($this->table . ' AS t1')->where('user_id', $id)->where('song_id', $songID)->where('difficulty', $difficulty)->whereNotExists(function ($query) {
			$query->select(DB::raw('1'))->from($this->table . ' AS t2')->whereRaw('t1.user_id = t2.user_id')->whereRaw('t1.song_id = t2.song_id')->whereRaw('t1.difficulty = t2.difficulty')->whereRaw('t1.generation < t2.generation');
        });

        $this->value = $sql->first();
        return $this->value;
    }

    function getRecentGenerationOfScoreDataAll($id){
        $sql = DB::table($this->table)->
            select('user_id', 'song_id', 'difficulty')->
            selectRaw('MAX(generation)')->
            where('user_id', $id)->
            groupBy('user_id', 'song_id', 'difficulty');

        $this->value = $sql->get();

        return $this->value;
    }

    function getRecentUserScore($id){
        $this->value = DB::select('SELECT * FROM score_datas AS t1
        WHERE user_id = ? AND NOT EXISTS (
            SELECT * FROM score_datas AS t2
            WHERE t1.user_id = t2.user_id
                AND t1.song_id = t2.song_id
                AND t1.difficulty = t2.difficulty
                AND t1.updated_at < t2.updated_at
        );', [$id]);

        return $this->value;
    }

    function getSpecifiedGenerationUserScore($id, $generation){
        $this->value = DB::select('SELECT * FROM score_datas AS t1 
            WHERE user_id = ? AND generation < ? AND NOT EXISTS (
            SELECT * FROM score_datas AS t2
                WHERE generation < ?
                    AND t1.user_id = t2.user_id
                    AND t1.song_id = t2.song_id
                    AND t1.difficulty = t2.difficulty
                    AND t1.updated_at < t2.updated_at
        );', [$id, $generation, $generation]);

        return $this->value;
    }

    function getMaxGeneration($id){
        return DB::select('SELECT MAX(generation) FROM score_datas WHERE user_id = ?;', [$id])[0]->{"MAX(generation)"};
    }
    
    /**
     * ユーザーのスコアデータのうち Rating新曲枠対象曲をすべて取得します。
     * @param integer $id 取得するユーザーid
     * @return 取得したデータ
     */
    function getRatingNewUserScore(int $id){
        $version = env("ONGEKI_VERSION");

        $this->value = DB::select("SELECT * FROM score_datas AS t1 INNER JOIN music_datas ON t1.song_id = music_datas.id
        WHERE user_id = ? AND (
            CASE 
                WHEN difficulty = '10' THEN lunatic_added_version
                ELSE normal_added_version
            END
        ) = ? AND 
        NOT EXISTS (
            SELECT * FROM score_datas AS t2
            WHERE t1.user_id = t2.user_id
                AND t1.song_id = t2.song_id
                AND t1.difficulty = t2.difficulty
                AND t1.updated_at < t2.updated_at
        )", [$id, $version]);
        return $this->value;
    }

    /**
     * ユーザーのスコアデータのうち、Rating旧曲枠対象曲をすべて取得します。
     *
     * @param integer $id 取得するユーザーid
     * @return 取得したデータ
     */
    function getRatingOldUserScore(int $id){
        $version = env("ONGEKI_VERSION");

        $this->value = DB::select("SELECT * FROM score_datas AS t1 INNER JOIN music_datas ON t1.song_id = music_datas.id
        WHERE user_id = ? AND (
            CASE 
                WHEN difficulty = '10' THEN lunatic_added_version
                ELSE normal_added_version
            END
        ) < ? AND 
        NOT EXISTS (
            SELECT * FROM score_datas AS t2
            WHERE t1.user_id = t2.user_id
                AND t1.song_id = t2.song_id
                AND t1.difficulty = t2.difficulty
                AND t1.updated_at < t2.updated_at
        )", [$id, $version]);
        return $this->value;
    }
}

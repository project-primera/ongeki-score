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
    protected $guarded = ['id'];
    private $value;

    function getValue(){
        return $this->value;
    }

    function addMusicData(){
        $temp = MusicData::all();

        foreach ($temp as $key => $value) {
            $title[$value->id] = $value;
        }

        foreach ($this->value as $key => $value) {
            // song_id
            $this->value[$key]->title = $title[$value->song_id]->title;
            $this->value[$key]->genre = $title[$value->song_id]->genre;
            $this->value[$key]->artist = $title[$value->song_id]->artist;
            $this->value[$key]->deleted_normal = (bool)$title[$value->song_id]->deleted_normal;
            $this->value[$key]->deleted_lunatic = (bool)$title[$value->song_id]->deleted_lunatic;

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

        return $this;
    }

    function addDetailedData(){
        foreach ($this->value as $key => $value) {
            if($this->value[$key]->over_damage_high_score >= 500){
                $this->value[$key]->over_damage_high_score_next = "";
            }else{
                $this->value[$key]->over_damage_high_score_next = floor((fmod($this->value[$key]->over_damage_high_score, 100.0) - 100.0) * pow(10, 2)) / pow(10, 2);
            }

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

        return $this;
    }

    /**
     * 削除済みフラグが立っている楽曲データを取り除きます。
     *
     * @return ScoreData
     */
    function exclusionDeletedMusic(){
        foreach ($this->value as $key => $value) {
            if ($value->difficulty === 10) {
                if($value->deleted_lunatic){
                    unset($this->value[$key]);
                }
            }else{
                if($value->deleted_normal){
                    unset($this->value[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * 削除済みフラグが立っていない楽曲データを取り除きます。
     *
     * @return ScoreData
     */
    function exclusionNotDeletedMusic(){
        foreach ($this->value as $key => $value) {
            if ($value->difficulty === 10) {
                if(!!!$value->deleted_lunatic){
                    unset($this->value[$key]);
                }
            }else{
                if(!!!$value->deleted_normal){
                    unset($this->value[$key]);
                }
            }
        }
        return $this;
    }

    function getRecentGenerationOfScoreData($id, $songID, $difficulty){
        $sql = DB::table($this->table)->select('*')
        ->from($this->table . ' AS t1')->where('user_id', $id)->where('song_id', $songID)->where('difficulty', $difficulty)->whereNotExists(function ($query) {
			$query->select(DB::raw('1'))->from($this->table . ' AS t2')->whereRaw('t1.user_id = t2.user_id')->whereRaw('t1.song_id = t2.song_id')->whereRaw('t1.difficulty = t2.difficulty')->whereRaw('t1.generation < t2.generation');
        });

        $this->value = $sql->first();
        return $this;
    }

    function getRecentOfAllUserScoreData($songID, $difficulty){
        $sql = DB::table($this->table)->select('*')
            ->from($this->table . ' AS t1')
            ->where('song_id', $songID)
            ->where('difficulty', $difficulty)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('1'))->from($this->table . ' AS t2')->whereRaw('t1.user_id = t2.user_id')->whereRaw('t1.song_id = t2.song_id')->whereRaw('t1.difficulty = t2.difficulty')->whereRaw('t1.id < t2.id');
            }
        );
        $this->value = $sql->get();
        return $this;
    }

    function getRecentGenerationOfScoreDataAll($id){
        $sql = DB::table($this->table)->
            select('user_id', 'song_id', 'difficulty')->
            selectRaw('MAX(generation)')->
            where('user_id', $id)->
            groupBy('user_id', 'song_id', 'difficulty');

        $this->value = $sql->get();

        return $this;
    }

    function getRecentUserScore($id){
        $this->value = DB::select('SELECT * FROM score_datas AS t1
        WHERE user_id = ? AND NOT EXISTS (
            SELECT * FROM score_datas AS t2
            WHERE t1.user_id = t2.user_id
                AND t1.song_id = t2.song_id
                AND t1.difficulty = t2.difficulty
                AND t1.id < t2.id
        );', [$id]);

        return $this;
    }

    /**
     * 指定した世代のスコアデータを取得します。
     *
     * @param integer $id ユーザーid
     * @param integer $generation 世代
     * @return object this
     */
    function getSpecifiedGenerationUserScore(int $id, int $generation){
        $this->value = DB::select('SELECT * FROM score_datas AS t1
            WHERE user_id = ? AND generation <= ? AND NOT EXISTS (
            SELECT * FROM score_datas AS t2
                WHERE generation <= ?
                    AND t1.user_id = t2.user_id
                    AND t1.song_id = t2.song_id
                    AND t1.difficulty = t2.difficulty
                    AND t1.id < t2.id
        );', [$id, $generation, $generation]);

        return $this;
    }

    /**
     * 特定のユーザーのスコアデータ世代一覧を取得します
     *
     * @param integer $id 取得するユーザーidを取得します
     * @return object this
     */
    function getAllGenerationUserScore(int $id){
        $this->value = DB::select('SELECT * FROM score_datas AS t1
            WHERE user_id = ? AND NOT EXISTS (
            SELECT * FROM score_datas AS t2
                WHERE t1.generation = t2.generation
                AND t1.user_id = t2.user_id
                AND t1.id > t2.id
        ) order by generation asc;', [$id]);

        return $this;
    }

    function getMaxGeneration($id){
        return DB::select('SELECT MAX(generation) FROM score_datas WHERE user_id = ?;', [$id])[0]->{"MAX(generation)"};
    }

    /**
     * ユーザーのスコアデータのうち Rating新曲枠対象曲をすべて取得します。
     * @param integer $id 取得するユーザーid
     * @return this
     */
    function getRatingNewUserScore(int $id){
        $version = config('env.ongeki-version');

        $this->value = DB::select("SELECT * FROM score_datas AS t1 INNER JOIN music_datas ON t1.song_id = music_datas.id
        WHERE user_id = ?
        AND (unrated IS NULL OR unrated = 0)
        AND (
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
                AND t1.id < t2.id
        )", [$id, $version]);
        return $this;
    }

    /**
     * ユーザーのスコアデータのうち、Rating旧曲枠対象曲をすべて取得します。
     *
     * @param integer $id 取得するユーザーid
     * @return this
     */
    function getRatingOldUserScore(int $id){
        $version = config('env.ongeki-version');

        $this->value = DB::select("SELECT * FROM score_datas AS t1 INNER JOIN music_datas ON t1.song_id = music_datas.id
        WHERE user_id = ?
        AND (unrated IS NULL OR unrated = 0)
        AND (
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
                AND t1.id < t2.id
        )", [$id, $version]);
        return $this;
    }
}

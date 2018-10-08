<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ScoreData extends Model
{
    //
    protected $table = "score_datas";
    protected $guarded = ['id', 'user_id'];

    function getRecentGenerationOfScoreData($id, $songID, $difficulty){
        $sql = DB::table($this->table)->select('*')
        ->from($this->table . ' AS t1')->where('user_id', $id)->where('song_id', $songID)->where('difficulty', $difficulty)->whereNotExists(function ($query) {
			$query->select(DB::raw('1'))->from($this->table . ' AS t2')->whereRaw('t1.user_id = t2.user_id')->whereRaw('t1.song_id = t2.song_id')->whereRaw('t1.difficulty = t2.difficulty')->whereRaw('t1.generation < t2.generation');
        });
        return $sql->first();
    }

    function getRecentGenerationOfScoreDataAll($id){
        $sql = DB::table($this->table)->
        select('user_id', 'song_id', 'difficulty')->
        selectRaw('MAX(generation)')->
        where('user_id', $id)->
        groupBy('user_id', 'song_id', 'difficulty');
        return $sql->get();
    }

    function getRecentUserScore($id){
        /*
        特定ユーザーの最新データを取得する
        ScoreDataを複数持つクラスを作成してそれのメソッドとかで取得したら良さげ
        https://qiita.com/zaburo/items/e8e786624f88c253c87d

        SELECT *
        FROM ongeki_score_tool.score_datas AS t1
        WHERE user_id = 1
        AND NOT EXISTS (
            SELECT *
            FROM ongeki_score_tool.score_datas AS t2
            WHERE t1.user_id = t2.user_id
            AND t1.song_id = t2.song_id
            AND t1.difficulty = t2.difficulty
            AND t1.updated_at < t2.updated_at
        );

        この方法だと世代取得が厳しいのでこのテーブルに世代をint値で保存すればいいと想う
        取得もめっちゃ楽だし
        */
        DB::enableQueryLog();

        /*
        $value = DB::table($this->table)->select('*')->from($this->table . ' AS t1')->where('user_id', $id)->whereNotExists(function ($query) {
            $query->select(DB::raw('1'))->from($this->table . ' AS t2')->whereRaw('t1.user_id = t2.user_id')->whereRaw('t1.song_id = t2.song_id')->whereRaw('t1.difficulty = t2.difficulty')->whereRaw('t1.updated_at < t2.updated_at');
        });
        */
		
        $value = DB::select('SELECT * FROM score_datas AS t1
        WHERE user_id = ? AND NOT EXISTS (
            SELECT * FROM score_datas AS t2
            WHERE t1.user_id = t2.user_id
                AND t1.song_id = t2.song_id
                AND t1.difficulty = t2.difficulty
                AND t1.updated_at < t2.updated_at
        );', [$id]);
        
        return $value;
    }
}

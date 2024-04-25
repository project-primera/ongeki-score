<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AggregateBattleScore extends Model
{
    protected $table = "aggregate_battlescore";
    protected $guarded = [
        //
    ];
    public $incrementing = false;

    public static function execute(){
        \App\Facades\Slack::Debug("Max Battle Scoreの集計を開始します。");

        $musics = \App\MusicData::all();
        $normalDifficulties = [0, 1, 2, 3];
        $lunaticDifficulty = 10;

        foreach ($musics as $music) {
            if($music->basic_level !== null){
                foreach ($normalDifficulties as $difficulty) {
                    \App\AggregateBattleScore::record($music->id, $difficulty);
                }
            }

            if($music->lunatic_level !== null){
                \App\AggregateBattleScore::record($music->id, $lunaticDifficulty);
            }
        }

        \App\Facades\Slack::Debug("Max Battle Scoreの集計を行いました。");
    }

    public static function record($song_id, $difficulty){
        $max = AggregateBattleScore::calculation($song_id, $difficulty);

        $key = $song_id . "_" . $difficulty;
        AggregateBattleScore::updateOrCreate(
            ['id' => $key],
            ['song_id' => $song_id, 'difficulty' => $difficulty, 'max' => $max]
        );
    }

    private static function calculation($song_id, $difficulty){
        $sql = DB::table('score_datas')
            ->select('song_id', 'difficulty', DB::raw('MAX(battle_high_score) as max_battle_high_score'))
            ->where('song_id', $song_id)
            ->where('difficulty', $difficulty)
            ->groupBy('song_id', 'difficulty')
        ;
        try {
            return $sql->get()[0]->max_battle_high_score;
        } catch (\Throwable $th) {
            return 0;
        }
    }
}

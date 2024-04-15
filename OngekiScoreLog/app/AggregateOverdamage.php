<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AggregateOverdamage extends Model
{
    protected $table = "aggregate_overdamage";
    protected $guarded = [
        //
    ];
    public $incrementing = false;

    public static function execute(){
        \App\Facades\Slack::Debug("Max Over Damageの集計を開始します。");

        $musics = \App\MusicData::all();
        $normalDifficulties = [0, 1, 2, 3];
        $lunaticDifficulty = 10;

        foreach ($musics as $music) {
            if($music->basic_level !== null){
                foreach ($normalDifficulties as $difficulty) {
                    \App\AggregateOverdamage::record($music->id, $difficulty);
                }
            }

            if($music->lunatic_level !== null){
                \App\AggregateOverdamage::record($music->id, $lunaticDifficulty);
            }
        }

        \App\Facades\Slack::Debug("Max Over Damageの集計を行いました。");
    }

    public static function record($song_id, $difficulty){
        $max = AggregateOverdamage::calculation($song_id, $difficulty);

        $key = $song_id . "_" . $difficulty;
        AggregateOverdamage::updateOrCreate(
            ['id' => $key],
            ['song_id' => $song_id, 'difficulty' => $difficulty, 'max' => $max]
        );
    }

    private static function calculation($song_id, $difficulty){
        $sql = DB::table('score_datas')
        ->select('song_id', 'difficulty', DB::raw('MAX(over_damage_high_score) as max_over_damage_high_score'))
        ->where('song_id', $song_id)
        ->where('difficulty', $difficulty)
        ->groupBy('song_id', 'difficulty')
    ;
    try {
        return $sql->get()[0]->max_over_damage_high_score;
    } catch (\Throwable $th) {
        return 0;
    }
    }
}

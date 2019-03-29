<?php

namespace App\Http\Controllers;

use App\UserStatus;
use App\ScoreData;
use App\OngekiScoreLog\Highcharts;
use App\MusicData;

class ViewUserMusicController extends Controller
{
    function getRedirect(int $id, int $music){
        return redirect("/user/$id/music/$music/master");
    }
    function getIndex(int $id, int $music, string $difficulty){
        $status = (new UserStatus)->getRecentUserData($id);

        $difficultyToInt = [
            'basic' => 0,
            'advanced' => 1,
            'expert' => 2,
            'master' => 3,
            'lunatic' => 10,
        ];
        $dif = 0;
        try {
            $dif = $difficultyToInt[$difficulty];
        } catch (\ErrorException $ignore){
            abort(404);
        }

        $musicData = MusicData::find($music);
        if(is_null($musicData)){
            abort(404);
        }

        $isExist = new \stdClass;
        $isExist->normal = !is_null($musicData->normal_added_version);
        $isExist->lunatic = !is_null($musicData->lunatic_added_version); 

        $score = ScoreData::join('music_datas','score_datas.song_id','=','music_datas.id')->where(['user_id' => $id,'song_id' => $music, 'difficulty' => $dif])->get();


        $technical = [];
        $battle = [];
        $damage = [];
        $date = [];
        
        $prev = new \stdClass();
        $prev->technical = 0;
        $prev->battle = 0;
        $prev->damage = 0;

        foreach ($score as $key => $value) {
            $technical[] = (int)($value->technical_high_score / 100) / 10;
            $battle[] = (int)($value->battle_high_score / 1000) / 1000;
            $damage[] = (float)$value->over_damage_high_score;
            $date[] = date('n/j', strtotime($value->updated_at));

            $value->differenceTechnical = $value->technical_high_score - $prev->technical;
            $value->differenceBattle = $value->battle_high_score - $prev->battle;
            $value->differenceDamage = round(($value->over_damage_high_score - $prev->damage) * 100) / 100;
            $prev->technical = $value->technical_high_score;
            $prev->battle = $value->battle_high_score;
            $prev->damage = $value->over_damage_high_score;
        }

        $highcharts = new Highcharts();
        $highcharts->id("graph")
            ->addXAxis("", $date)
            ->addYAxis("TechnicalScore", [], false, 1, "this.value + 'k'")
            ->addYAxis("BattleScore", [], true, 0, "this.value + 'm'")
            ->addYAxis("OverDamage", [], true, 0, "this.value + '%'")
            ->addSeries("TechnicalScore", $technical)
            ->addSeries("BattleScore", $battle, 1)
            ->addSeries("OverDamage", $damage, 2)
            ->zoomType("x")
            ->isTooltipCrosshairs(true)
            ->isTooltipShared(true)
            ->isPlotOptionsDataLabelsEnabled(true)
            ->isPlotOptionsEnableMouseTracking(true);

        $highcharts_sp = new Highcharts();
        $highcharts_sp->id("sp-graph")
            ->addXAxis("", $date)
            ->addYAxis("", [], false, 1, "this.value + 'k'", true)
            ->addYAxis("", [], true, 0, "this.value + 'm'", true)
            ->addYAxis("", [], true, 0, "this.value + '%'", true)
            ->addSeries("TechnicalScore(k)", $technical)
            ->addSeries("BattleScore(m)", $battle, 1)
            ->addSeries("OverDamage(%)", $damage, 2)
            ->zoomType("x")
            ->isTooltipCrosshairs(true)
            ->isTooltipShared(true)
            ->isPlotOptionsDataLabelsEnabled(true)
            ->isPlotOptionsEnableMouseTracking(true);
            
        return view('user_music', compact('status', 'id', 'music', 'musicData', 'isExist', 'highcharts', 'highcharts_sp', 'score', 'difficulty'));
    }
}

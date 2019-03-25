<?php

namespace App\Http\Controllers;

use App\UserStatus;
use App\ScoreData;
use App\OngekiScoreLog\Highcharts;

class ViewUserMusicController extends Controller
{
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

        $score = ScoreData::join('music_datas','score_datas.song_id','=','music_datas.id')->where(['user_id' => $id,'song_id' => $music, 'difficulty' => $dif])->get();

        $technical = [];
        $battle = [];
        $damage = [];
        $date = [];
        foreach ($score as $key => $value) {
            $technical[] = (int)($value->technical_high_score / 100) / 10;
            $battle[] = (int)($value->battle_high_score / 1000) / 1000;
            $damage[] = (float)$value->over_damage_high_score;
            $date[] = date('n/j', strtotime($value->updated_at));
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
            
        return view('user_music', compact('status', 'id', 'highcharts', 'highcharts_sp', 'score', 'difficulty'));
    }
}

<?php

namespace App\Http\Controllers;

use App\UserStatus;
use App\ScoreData;
use App\OngekiScoreLog\Highcharts;
use App\MusicData;

class ViewUserMusicController extends Controller
{
    function getRedirect(int $id, int $music){
        $musicData = MusicData::find($music);
        if(is_null($musicData)){
            abort(404);
        }else if (!is_null($musicData->normal_added_version)){
            return redirect("/user/$id/music/$music/master");
        }else{
            return redirect("/user/$id/music/$music/lunatic");
        }
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
        if(count($status) === 0 || is_null($musicData)){
            abort(404);
        }

        $isExist = new \stdClass;
        $isExist->normal = !is_null($musicData->normal_added_version);
        $isExist->lunatic = !is_null($musicData->lunatic_added_version);

        $score = ScoreData::join('music_datas','score_datas.song_id','=','music_datas.id')
            ->where(['user_id' => $id,'song_id' => $music, 'difficulty' => $dif])
            ->select('score_datas.technical_high_score', 'score_datas.battle_high_score', 'score_datas.over_damage_high_score', 'score_datas.created_at')
            ->get();

        $technical = [];
        $battle = [];
        $damage = [];
        $date = [];

        $prev = new \stdClass();
        $prev->technical = 0;
        $prev->battle = 0;
        $prev->damage = 0;

        foreach ($score as $key => $value) {
            $technical[] = (int)($value->technical_high_score);
            $battle[] = (int)($value->battle_high_score);
            $damage[] = (float)$value->over_damage_high_score;
            $date[] = date('y/n/j', strtotime($value->created_at));

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
            ->addYAxis("TechnicalScore", [], false, 1, null)
            ->addYAxis("BattleScore", [], true, 0, null)
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
            ->addYAxis("", [], false, 1, null, true)
            ->addYAxis("", [], true, 0, null, true)
            ->addYAxis("", [], true, 0, "this.value + '%'", true)
            ->addSeries("TechnicalScore", $technical)
            ->addSeries("BattleScore", $battle, 1)
            ->addSeries("OverDamage(%)", $damage, 2)
            ->zoomType("x")
            ->isTooltipCrosshairs(true)
            ->isTooltipShared(true)
            ->isPlotOptionsDataLabelsEnabled(true)
            ->isPlotOptionsEnableMouseTracking(true);

        return view('user_music', compact('status', 'id', 'music', 'musicData', 'isExist', 'highcharts', 'highcharts_sp', 'score', 'difficulty'));
    }
}

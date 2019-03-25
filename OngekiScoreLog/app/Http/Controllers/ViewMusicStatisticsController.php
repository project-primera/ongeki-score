<?php

namespace App\Http\Controllers;

use App\UserStatus;
use App\ScoreData;
use App\OngekiScoreLog\Highcharts;
use App\MusicData;

class ViewMusicStatisticsController extends Controller
{
    function getIndex(int $music, string $difficulty){
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
        $isExist = new \stdClass;
        $isExist->normal = !is_null($musicData->normal_added_version);
        $isExist->lunatic = !is_null($musicData->lunatic_added_version);

        $users = (new UserStatus)->getRecentAllUserData();
        $users = array_column($users, null, 'user_id');

        $scoreData = new ScoreData;
        $scoreData = $scoreData->getRecentOfAllUserScoreData($music, $dif)->getValue();

        $statistics = new \stdClass;
        foreach ($scoreData as $key => $value) {
            $rateKey = $users[$value->user_id]->rating;
            if($rateKey < 10.0 || strtotime($users[$value->user_id]->updated_at) < strtotime(config('env.ongeki-version-date'))){
                continue;
            }
            $rateKey = floor(floor($rateKey * 40) / 10);
            if($rateKey % 4 == 0){
                $rateKey = floor($rateKey / 4) . ".00";
            }else if($rateKey % 4 == 1){
                $rateKey = floor($rateKey / 4) . ".25";
            }else if($rateKey % 4 == 2){
                $rateKey = floor($rateKey / 4) . ".50";
            }else{
                $rateKey = floor($rateKey / 4) . ".75";
            }

            $battleKey = $users[$value->user_id]->battle_point;
            switch (true) {
                case ($battleKey >= 15000): $battleKey = "奏伝"; break;
                case ($battleKey >= 14000): $battleKey = "十段"; break;
                case ($battleKey >= 13000): $battleKey = "九段"; break;
                case ($battleKey >= 12000): $battleKey = "八段"; break;
                case ($battleKey >= 14000): $battleKey = "七段"; break;
                case ($battleKey >= 10000): $battleKey = "六段"; break;
                case ($battleKey >= 9000):  $battleKey = "五段"; break;
                case ($battleKey >= 8000):  $battleKey = "四段"; break;
                case ($battleKey >= 7000):  $battleKey = "三段"; break;
                case ($battleKey >= 6000):  $battleKey = "二段"; break;
                case ($battleKey >= 5000):  $battleKey = "初段"; break;
                case ($battleKey >= 4500):  $battleKey = "一級"; break;
                case ($battleKey >= 4000):  $battleKey = "二級"; break;
                case ($battleKey >= 3500):  $battleKey = "三級"; break;
                case ($battleKey >= 3000):  $battleKey = "四級"; break;
                case ($battleKey >= 2500):  $battleKey = "五級"; break;
                case ($battleKey >= 2000):  $battleKey = "六級"; break;
                case ($battleKey >= 1500):  $battleKey = "七級"; break;
                case ($battleKey >= 1000):  $battleKey = "八級"; break;
                case ($battleKey >= 500):   $battleKey = "九級"; break;
                case ($battleKey >= 200):   $battleKey = "十級"; break;
                default:                    $battleKey = "新入生"; break;
            }

            $statistics->technical[$rateKey][] = $value->technical_high_score;
            $statistics->battle[$battleKey][] = $value->battle_high_score;
            $statistics->damage[$battleKey][] = $value->over_damage_high_score;
        }

        return json_encode($statistics, true);

        $highcharts = new Highcharts();
        /*
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
        */

        $highcharts_sp = new Highcharts();
        /*
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
        */ 
            
        return view("statistics_music", compact('music', 'difficulty', 'musicData', 'isExist', 'highcharts', 'highcharts_sp'));
        return view('user_music', compact('status', 'id', 'music', 'isExist', 'highcharts', 'highcharts_sp', 'score', 'difficulty'));
    }
}

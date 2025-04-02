<?php

namespace App\Http\Controllers;

use Auth;
use App\UserStatus;
use App\ScoreData;
use App\OngekiScoreLog\Highcharts;
use App\MusicData;

class ViewMusicStatisticsController extends Controller
{
    function getRedirect(int $music){
        $musicData = MusicData::find($music);
        if(is_null($musicData)){
            abort(404);
        }else if (!is_null($musicData->normal_added_version)){
            return redirect("/music/$music/master");
        }else{
            return redirect("/music/$music/lunatic");
        }
    }

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

        // 存在しない曲ページ
        if($musicData === null){
            abort(404);
        }

        $isExist = new \stdClass;
        $isExist->normal = !is_null($musicData->normal_added_version);
        $isExist->lunatic = !is_null($musicData->lunatic_added_version);

        $users = (new UserStatus)->getRecentAllUserData();
        $users = array_column($users, null, 'user_id');

        $statistics = new \stdClass;
        $scoreData = new ScoreData;
        $scoreData = $scoreData->getRecentOfAllUserScoreData($music, $dif)->getValue();

        $battleKeys = ["新入生", "十級", "九級", "八級", "七級", "六級", "五級", "四級", "三級", "二級", "一級",  "初段", "二段", "三段", "四段", "五段", "六段", "七段", "八段", "九段", "十段", "奏伝"];

        $technicalGrades = ["AB+", "SSS+", "SSS", "SS", "S", "AAA", "AA", "A", "B"];

        $rateKeys = [];
        for ($i = 0; $i <= 25; ++$i) {
            $rateKeys[] = $i . ".00";
            $rateKeys[] = $i . ".50";
        }

        foreach ($rateKeys as $value) {
            $statistics->technicalTotalScore[$value] = 0;
            $statistics->technicalTotalCount[$value] = 0;
            foreach ($technicalGrades as $v) {
                $statistics->technicalGradeCountGraph[$v][$value] = 0;
                $statistics->technicalGradeCount[$value][$v] = 0;
            }
        }

        foreach ($scoreData as $key => $value) {
            $rateKey = $users[$value->user_id]->rating;
            $rateKey = floor(floor($rateKey * 20) / 10);
            switch (true) {
                case ($rateKey % 2 == 0): $rateKey = floor($rateKey / 2) . ".00"; break;
                case ($rateKey % 2 == 1): $rateKey = floor($rateKey / 2) . ".50"; break;
            }

            $battleKey = $users[$value->user_id]->battle_point;
            switch (true) {
                case ($battleKey >= 15000): $battleKey = $battleKeys[21]; break;
                case ($battleKey >= 14000): $battleKey = $battleKeys[20]; break;
                case ($battleKey >= 13000): $battleKey = $battleKeys[19]; break;
                case ($battleKey >= 12000): $battleKey = $battleKeys[18]; break;
                case ($battleKey >= 14000): $battleKey = $battleKeys[17]; break;
                case ($battleKey >= 10000): $battleKey = $battleKeys[16]; break;
                case ($battleKey >= 9000):  $battleKey = $battleKeys[15]; break;
                case ($battleKey >= 8000):  $battleKey = $battleKeys[14]; break;
                case ($battleKey >= 7000):  $battleKey = $battleKeys[13]; break;
                case ($battleKey >= 6000):  $battleKey = $battleKeys[12]; break;
                case ($battleKey >= 5000):  $battleKey = $battleKeys[11]; break;
                case ($battleKey >= 4500):  $battleKey = $battleKeys[10]; break;
                case ($battleKey >= 4000):  $battleKey = $battleKeys[9]; break;
                case ($battleKey >= 3500):  $battleKey = $battleKeys[8]; break;
                case ($battleKey >= 3000):  $battleKey = $battleKeys[7]; break;
                case ($battleKey >= 2500):  $battleKey = $battleKeys[6]; break;
                case ($battleKey >= 2000):  $battleKey = $battleKeys[5]; break;
                case ($battleKey >= 1500):  $battleKey = $battleKeys[4]; break;
                case ($battleKey >= 1000):  $battleKey = $battleKeys[3]; break;
                case ($battleKey >= 500):   $battleKey = $battleKeys[2]; break;
                case ($battleKey >= 200):   $battleKey = $battleKeys[1]; break;
                default:                    $battleKey = $battleKeys[0]; break;
            }

            // technical系は特定レート以上を参考値にする
            if($value->technical_high_score !== 0 && strtotime($users[$value->user_id]->updated_at) >= strtotime(config('env.ongeki-version-date'))){
                $grade = "";
                switch (true) {
                    case ($value->technical_high_score < 850000): $grade = $technicalGrades[8]; break;
                    case ($value->technical_high_score < 900000): $grade = $technicalGrades[7]; break;
                    case ($value->technical_high_score < 940000): $grade = $technicalGrades[6]; break;
                    case ($value->technical_high_score < 970000): $grade = $technicalGrades[5]; break;
                    case ($value->technical_high_score < 990000): $grade = $technicalGrades[4]; break;
                    case ($value->technical_high_score < 1000000): $grade = $technicalGrades[3]; break;
                    case ($value->technical_high_score < 1007500): $grade = $technicalGrades[2]; break;
                    case ($value->technical_high_score < 1010000): $grade = $technicalGrades[1]; break;
                    default: $grade = $technicalGrades[0]; break;
                }

                $statistics->technicalTotalScore[$rateKey] += $value->technical_high_score;
                ++$statistics->technicalTotalCount[$rateKey];
                ++$statistics->technicalGradeCountGraph[$grade][$rateKey];
                ++$statistics->technicalGradeCount[$rateKey][$grade];
            }
        }

        $myScore = null;
        if(Auth::check()){
            $myScore = (new ScoreData)->getRecentGenerationOfScoreData(Auth::user()->id, $music, $dif)->getValue();
        }

        $line = [];
        foreach ($statistics->technicalTotalScore as $key => $value) {
            if($statistics->technicalTotalCount[$key] !== 0){
                $line[] = floor($value / $statistics->technicalTotalCount[$key]);
            }else{
                $line[] = null;
            }
        }

        $data = [];
        foreach ($statistics->technicalGradeCountGraph as $key => $value) {
            if($statistics->technicalTotalCount[key($value)] !== 0 || true){
                foreach ($value as $v) {
                    $data[$key][] = $v;
                }
            }
        }

        foreach ($rateKeys as $key => $value) {
            if($statistics->technicalTotalCount[$rateKeys[$key]] === 0){
                foreach ($technicalGrades as $k => $v) {
                    unset($data[$v][$key]);
                }
                unset($line[$key]);
                unset($rateKeys[$key]);
            }
        }
        foreach ($technicalGrades as $k => $v) {
            $data[$v] = array_values($data[$v]);
        }
        $line = array_values($line);
        $rateKeys = array_values($rateKeys);


        $highcharts = new \stdClass;
        $highcharts->technical = new Highcharts();
        $highcharts->technical
            ->type("column")
            ->addXAxis("Rate", $rateKeys)
            ->zoomType("x")
            ->tooltipPointFormat('<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>')
            ->isPlotOptionsStackingPercent(true)
            ->isTooltipCrosshairs(true)
            ->isTooltipShared(true);

        $colors = ["#f35987", "#ba801c", "#df9c2a", "#e6b156", "#ecc583", "#1fb2f8", "#50c3f9", "#82d4fb", "#b3e5fd"];
        $cnt = 0;
        foreach ($data as $key => $value) {
            $highcharts->technical->addSeries($key, $value, 0, null, $colors[$cnt++]);
        }
        $highcharts->technical->addSeries("レート別平均", $line, 1, "line", "#333333");

        $highcharts->technical_sp = clone $highcharts->technical;
        $highcharts->technical->id("graph")->addYAxis("", [], false, null, "this.value + '%'", false, 0, 100, 10)
            ->addYAxis("", [], true, 0, null, false, 810000, 1010000, 10000);
        $highcharts->technical_sp->id("sp-graph")->addYAxis("", [], false, null, "this.value + '%'", true, 0, 100, 10)
            ->addYAxis("", [], true, 0, null, true, 800000, 1010000, 10000);

        foreach ($statistics->technicalTotalScore as $key => $value) {
            if($statistics->technicalTotalCount[$key] !== 0){
                $statistics->technicalAverageScore[$key] = floor($value / $statistics->technicalTotalCount[$key]);
                if(!is_null($myScore)){
                    $statistics->technicalDifferenceScore[$key] = number_format($myScore->technical_high_score - $statistics->technicalAverageScore[$key]);
                }else{
                    $statistics->technicalDifferenceScore[$key] = "";
                }
            }else{
                $statistics->technicalAverageScore[$key] = 0;
                $statistics->technicalDifferenceScore[$key] = "";
            }
        }
        return view("music_statistics", compact('music', 'difficulty', 'musicData', 'isExist', 'highcharts', 'statistics', 'myScore'));
    }
}

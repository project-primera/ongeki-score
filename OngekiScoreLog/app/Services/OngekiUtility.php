<?php
namespace App\Services;

use App\MusicData;

class OngekiUtility {

    private static $MusicList = null;

    function __construct()	{
        $temp = (new MusicData())->getEstimateExtraLevel();
        $sameNameList = array_flip((new MusicData())->getSameMusicList());
        foreach ($temp as $value) {
            $title = $value['title'];
            if (array_key_exists($title, $sameNameList)) {
                $title .= "." . $value['genre'];
                var_dump($title);
            }
            $this::$MusicList[$title] = $value;
            unset($this::$MusicList[$title]['title']);
        }
    }

    public function IsEstimatedRateValueFromTitle(string $title, $difficulty){
        if(is_int($difficulty)){
            $keys = [
                0 => "basic_extra_level_estimated",
                1 => "advanced_extra_level_estimated",
                2 => "expert_extra_level_estimated",
                3 => "master_extra_level_estimated",
                10 => "lunatic_extra_level_estimated",
            ];
            $difficulty = $keys[$difficulty];
        }else if(!is_string($difficulty)){
            throw new InvalidArgumentException();
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / difficulty:" . $difficulty);
        }
        return $this::$MusicList[$title][$difficulty];
    }

    /**
     * 楽曲タイトルと難易度からレート値を返します。
     *
     * @param string $title 楽曲タイトル
     * @param integer|string $difficulty 難易度
     * @return float 譜面定数
     */
    public function ExtraLevelFromTitle(string $title, $difficulty): float{
        if(is_int($difficulty)){
            $keys = [
                0 => "basic_extra_level",
                1 => "advanced_extra_level",
                2 => "expert_extra_level",
                3 => "master_extra_level",
                10 => "lunatic_extra_level",
            ];
            $difficulty = $keys[$difficulty];
        }else if(!is_string($difficulty)){
            throw new InvalidArgumentException();
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / difficulty:" . $difficulty);
        }
        return $this::$MusicList[$title][$difficulty];
    }

    public function RateValueFromTitle(string $title, $difficulty, int $technicalScore)
    {
        if(is_int($difficulty)){
            $keys = [
                0 => "basic_extra_level",
                1 => "advanced_extra_level",
                2 => "expert_extra_level",
                3 => "master_extra_level",
                10 => "lunatic_extra_level",
            ];
            $difficulty = $keys[$difficulty];
        }else if(!is_string($difficulty)){
            throw new InvalidArgumentException();
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / difficulty:" . $difficulty);
        }
        return $this->RateValue($this::$MusicList[$title][$difficulty], $technicalScore);
    }

    private function RateValue(float $extraLevel, int $technicalScore)
    {
        if($technicalScore >= 1007500){
            // >= 1007500   定数+2.0    理論値
            return $extraLevel + 2.0;
        }else if($technicalScore >= 1000000){
            // >= 1000000   定数+1.5    1000000を超えた150点毎に+0.01
            return floor(($extraLevel * 100 + 150) + (floor(($technicalScore - 1000000) / 150))) / 100;
        }else if($technicalScore >= 970000){
            // >= 970000    定数+0.0    970000を超えた200点毎に+0.01
            return floor($extraLevel * 100 + (floor(($technicalScore - 970000) / 200))) / 100;
        }else{
            // < 970000     定数+0.0    970000から175点を割るごとに-0.01
            $v = floor($extraLevel * 100 - (floor((970000 - $technicalScore) / 175 + 1))) / 100;
            if($v < 0){
                $v = 0;
            }
            return $v;
        }
    }

    /**
     * 指定した譜面定数と目標レート値から、必要なスコアを予測して返します。
     *
     * @param float $extraLevel 譜面定数
     * @param float $targetExtraLevel 目指すレート値
     * @return mixed 必要スコア 到達不能であればfalseを返します
     */
    public function ExpectedScoreFromExtraLevel(float $extraLevel, float $targetExtraLevel){
        $differenceMaxRate = ($extraLevel + 2) - $targetExtraLevel;
        if($differenceMaxRate < 0){
            // 譜面定数+2超過 到達不可
            return false;
        }else if($differenceMaxRate == 0){
            // 譜面定数+2 レート値理論値
            return 1007500;
        }else if($differenceMaxRate <= 0.5){
            // 譜面定数+1.5 1007500からレート値0.01毎に150点減点
            return 1007500 - ($differenceMaxRate / 0.01 * 150);
        }else if($differenceMaxRate <= 2){
            // 譜面定数+0.0 1000000からレート値0.01毎に200点減点
            return 1000000 - (($differenceMaxRate - 0.5) / 0.01 * 200);
        }else{
            // 譜面定数+0未満 970000からレート値0.01毎に175点減点
            return 970000 - (($differenceMaxRate - 2.0) / 0.01 * 175);
        }
    }

    public function GetMusicLevel(string $title, $difficulty, bool $isStr = false){
        if(is_int($difficulty)){
            $keys = [
                0 => "basic_level",
                1 => "advanced_level",
                2 => "expert_level",
                3 => "master_level",
                10 => "lunatic_level",
            ];
            $difficulty = $keys[$difficulty];
        }
        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / difficulty:" . $difficulty);
        }
        if($isStr){
            return $this::$MusicList[$title][$difficulty . "_str"];
        }else{
            return $this::$MusicList[$title][$difficulty];
        }
    }

    public function GetIDFromTitle(string $title){
        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title);
        }
        return $this::$MusicList[$title]["id"];
    }
}

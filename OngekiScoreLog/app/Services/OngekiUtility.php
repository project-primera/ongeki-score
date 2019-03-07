<?php
namespace App\Services;

use App\MusicData;

class OngekiUtility {

    private static $MusicData = null;

    function __construct()	{
		$temp = (new MusicData())->getEstimateExtraLevel();
        foreach ($temp as $key => $value) {
            $this::$MusicData[$value['title']] = $value;
            unset($this::$MusicData[$value['title']]['title']);
        }
}

    public function IsEstimatedRateValueFromTitle(string $title, $difficulty, int $technicalScore){
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

        if(!array_key_exists($title, $this::$MusicData)){
            throw new \OutOfBoundsException();
        }
        return $this::$MusicData[$title][$difficulty];
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

        if(!array_key_exists($title, $this::$MusicData)){
            throw new \OutOfBoundsException();
        }
        return $this->RateValue($this::$MusicData[$title][$difficulty], $technicalScore);
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
        if(!array_key_exists($title, $this::$MusicData)){
            throw new \OutOfBoundsException();
        }
        if($isStr){
            return $this::$MusicData[$title][$difficulty . "_str"];
        }else{
            return $this::$MusicData[$title][$difficulty];
        }
    }
}
<?php
namespace App\Services;

use App\MusicData;

class OngekiUtility {

    private static $MusicData = null;
    private static $MusicList = null;

    function __construct()
    {
        $this::$MusicData = new MusicData;
        $temp = $this::$MusicData->getEstimateExtraLevel();
        $sameNameList = array_flip($this::$MusicData->getSameMusicList());
        foreach ($temp as $value) {
            $title = $value['title'];
            if (array_key_exists($title, $sameNameList)) {
                $title .= "." . $value['artist'] . "." . $value['genre'];
            }
            $this::$MusicList[$title] = $value;
            unset($this::$MusicList[$title]['title']);
        }
    }

    /**
     * 譜面定数が存在しない場合推定、譜面定数を返します
     *
     * @param string $title
     * @param [type] $difficulty
     * @param [type] $genre
     * @param [type] $artist
     * @return void
     */
    public function IsEstimatedRateValueFromTitle(string $title, $difficulty, $genre, $artist)
    {
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

        $sameNameList = array_flip($this::$MusicData->getSameMusicList());
        if (array_key_exists($title, $sameNameList)) {
            $title .= "." . $artist . "." . $genre;
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / artist:" . $artist . " / difficulty:" . $difficulty);
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
    public function ExtraLevelFromTitle(string $title, $difficulty, $genre, $artist): float
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

        $sameNameList = array_flip($this::$MusicData->getSameMusicList());
        if (array_key_exists($title, $sameNameList)) {
            $title .= "." . $artist . "." . $genre;
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / artist:" . $artist . " / difficulty:" . $difficulty);
        }
        return $this::$MusicList[$title][$difficulty];
    }

    public function RateValueFromTitle(string $title, $difficulty,
        int $technicalScore, string $lampForRating, $genre, $artist
    ) {
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

        $sameNameList = array_flip($this::$MusicData->getSameMusicList());
        if (array_key_exists($title, $sameNameList)) {
            $title .= "." . $artist . "." . $genre;
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / artist:" . $artist . " / difficulty:" . $difficulty);
        }

        if($this::$MusicList[$title][$difficulty] === null){
            // なんか存在しない難易度の定数値取ろうとしてる
            // 既に入ってる曲に後からlunatic追加されると起きがち
            return 0;
        }

        $result = 0;
        if($this::$MusicList[$title][$difficulty] != 0){
            $result += $this->calcRatingValue($this::$MusicList[$title][$difficulty], $technicalScore);
            $result += $this->calcRankRatingValue($technicalScore);
            $result += $this->calcLampRatingValue($lampForRating);
        }
        return $result;
    }

    private function calcRatingValue(float $extraLevel, int $technicalScore)
    {
        // flort問題のためすべての値を1000倍して整数で計算する
        $extra = $extraLevel * 1000;
        $result = 0;

        if($technicalScore == 1010000){ // 理論値: 2.0
            $result = $extra + 2000;
        }elseif($technicalScore >= 1007500){ // SSS+: 1.75 / 10点ごとに+0.001
            $result = $extra + 1750 + (floor(($technicalScore - 1007500) / 10));
        }elseif($technicalScore >= 1000000){ // SSS: 1.25 / 15点ごとに+0.001
            $result = $extra + 1250 + (floor(($technicalScore - 1000000) / 15));
        }elseif($technicalScore >= 990000){ // SS: 0.75 / 20点ごとに+0.001
            $result = $extra + 750 + (floor(($technicalScore - 990000) / 20));
        }elseif($technicalScore >= 970000){ // S: 0.00 / 26点ごとに+0.001
            $result = $extra + (floor(($technicalScore - 970000) / 26.666));
        }else{ // それ以下: -18点ごとに-0.001
            $result = $extra - (floor((970000 - $technicalScore) / 18));
        }
        if($result < 0 ){
            $result = 0;
        }
        return $result / 1000;
    }

    private function calcRankRatingValue(int $technicalScore)
    {
        if($technicalScore >= 1007500){
            return 0.3;
        }elseif($technicalScore >= 1000000){
            return 0.2;
        }elseif($technicalScore >= 990000){
            return 0.1;
        }
        return 0;
    }

    private function calcLampRatingValue(string $lampForRating)
    {
        if ($lampForRating == "FB/AB+") {
            return 0.4;
        } elseif ($lampForRating == "AB+") {
            return 0.35;
        } elseif ($lampForRating == "FB/AB") {
            return 0.35;
        } elseif ($lampForRating == "AB") {
            return 0.3;
        } elseif ($lampForRating == "FB/FC") {
            return 0.15;
        } elseif ($lampForRating == "FC") {
            return 0.1;
        } elseif ($lampForRating == "FB") {
            return 0.05;
        }
        return 0;
    }

    public function RateValueFromTitleForPlatinum(string $title, $difficulty, int $platinuScore, int $starCount, $genre, $artist)
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
        }elseif(!is_string($difficulty)){
            throw new InvalidArgumentException();
        }

        // ☆6以上は5として扱う
        if($starCount >= 6){
            $starCount = 5;
        }

        $sameNameList = array_flip($this::$MusicData->getSameMusicList());
        if (array_key_exists($title, $sameNameList)) {
            $title .= "." . $artist . "." . $genre;
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / artist:" . $artist . " / difficulty:" . $difficulty);
        }

        if($this::$MusicList[$title][$difficulty] === null){
            // なんか存在しない難易度の定数値取ろうとしてる
            // 既に入ってる曲に後からlunatic追加されると起きがち
            return 0;
        }

        return $this->calcPlatinumRatingValue($this::$MusicList[$title][$difficulty], $platinuScore, $starCount);
    }

    private function calcPlatinumRatingValue(float $extraLevel, int $platinumScore, int $starCount)
    {
        if($starCount > 5){
            $starCount = 5;
        }
        return $extraLevel * $extraLevel * $starCount / 1000;
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

    public function GetIDFromTitle(string $title, $genre, $artist)
    {
        $sameNameList = array_flip($this::$MusicData->getSameMusicList());
        if (array_key_exists($title, $sameNameList)) {
            $title .= "." . $artist . "." . $genre;
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title);
        }
        return $this::$MusicList[$title]["id"];
    }
}

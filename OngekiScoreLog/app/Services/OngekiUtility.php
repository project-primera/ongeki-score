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
            if (array_key_exists($value['title'], $sameNameList)) {
                // 同名曲があるやつ: 主にSingularity / アーティストとジャンルが入る
                $title = $value['title'] . "." . $value['artist'] . "." . $value['genre'];
                $this::$MusicList[$title] = $value;

                // 同名曲があるやつ: 主にPerfect Shining!! / アーティストのみが入る
                $title = $value['title'] . "." . $value['artist'] . "." . "";
                $this::$MusicList[$title] = $value;
            }else{
                // 同名曲がないやつ: そのまま
                $title = $value['title'];
                $this::$MusicList[$title] = $value;
            }

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
            throw new \InvalidArgumentException();
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
            throw new \InvalidArgumentException();
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
     * テクニカルスコアから求める単曲のレート値を、1/1000 単位の整数で返します（17.400 なら 17400）。
     * その難易度の譜面定数が無いときは 0 を返します。
     *
     * @param string $title 楽曲タイトル
     * @param integer|string $difficulty 難易度
     * @param string $lampForRating getLampForRating() の戻り値
     * @throws \OutOfBoundsException 楽曲が登録されていない
     */
    public function technicalRatingMilli(string $title, $difficulty, int $technicalScore, string $lampForRating, $genre, $artist): int
    {
        $extraLevel = $this->extraLevelCenti($title, $difficulty, $genre, $artist);
        if($extraLevel === null){
            // 既に入ってる曲に後からlunatic追加されると起きがち
            return 0;
        }
        return RatingCalculator::technical($extraLevel, $technicalScore, $lampForRating);
    }

    /**
     * プラチナスコア枠の単曲のレート値を、1/1000 単位の整数で返します（1.065 なら 1065）。
     * その難易度の譜面定数が無いときは 0 を返します。
     *
     * @param string $title 楽曲タイトル
     * @param integer|string $difficulty 難易度
     * @param integer $starCount ☆の数
     * @throws \OutOfBoundsException 楽曲が登録されていない
     */
    public function platinumRatingMilli(string $title, $difficulty, int $starCount, $genre, $artist): int
    {
        $extraLevel = $this->extraLevelCenti($title, $difficulty, $genre, $artist);
        if($extraLevel === null){
            return 0;
        }
        return RatingCalculator::platinum($extraLevel, $starCount);
    }

    /**
     * 譜面定数を 1/100 単位の整数で返します（14.7 なら 1470）。その難易度が無いときは null を返します。
     *
     * @param integer|string $difficulty 難易度
     * @throws \OutOfBoundsException 楽曲が登録されていない
     */
    private function extraLevelCenti(string $title, $difficulty, $genre, $artist): ?int
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
            throw new \InvalidArgumentException();
        }

        $sameNameList = array_flip($this::$MusicData->getSameMusicList());
        if (array_key_exists($title, $sameNameList)) {
            $title .= "." . $artist . "." . $genre;
        }

        if(!array_key_exists($title, $this::$MusicList)){
            throw new \OutOfBoundsException("title: " . $title . " / artist:" . $artist . " / difficulty:" . $difficulty);
        }

        return RatingCalculator::extraLevelToCenti($this::$MusicList[$title][$difficulty]);
    }

    public function getLampForRating(int $technicalScore, bool $fullBell, bool $fullCombo, bool $allBreak): string
    {
        if ($technicalScore == 1010000) {
            if ($fullBell) {
                return "FB/AB+";
            } else {
                return "AB+";
            }
        } elseif ($allBreak) {
            if ($fullBell) {
                return "FB/AB";
            } else {
                return "AB";
            }
        } elseif ($fullCombo) {
            if ($fullBell) {
                return "FB/FC";
            } else {
                return "FC";
            }
        } else {
            if ($fullBell) {
                return "FB";
            }
        }
        return "";
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

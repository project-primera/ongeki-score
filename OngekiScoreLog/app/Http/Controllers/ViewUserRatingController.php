<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserStatus;
use App\ScoreData;
use App\RatingRecentMusic;
use App\Facades\OngekiUtility;

class ViewUserRatingController extends Controller
{
    private $difficultyToStr = [
        0 => 'Basic',
        1 => 'Advanced',
        2 => 'Expert',
        3 => 'Master',
        10 => 'Lunatic',
    ];

    private function editMusicStdClass(\stdClass $stdClass, int $totalMusicCount){
        // 譜面定数の取得
        $stdClass->ratingValue = sprintf("%.2f", OngekiUtility::RateValueFromTitle($stdClass->title, $stdClass->difficulty, $stdClass->technical_high_score));
        $stdClass->rawRatingValue = $stdClass->ratingValue;

        // レート値上昇推定スコア計算
        $stdClass->extraLevel = OngekiUtility::ExtraLevelFromTitle($stdClass->title, $stdClass->difficulty);
        $stdClass->targetMusicRateMusic = OngekiUtility::ExpectedScoreFromExtraLevel($stdClass->extraLevel, $stdClass->rawRatingValue + 0.01);
        if($stdClass->targetMusicRateMusic !== false){
            $stdClass->targetMusicRateMusic = number_format($stdClass->technical_high_score - $stdClass->targetMusicRateMusic);
        }
        $stdClass->targetMusicRateUser = OngekiUtility::ExpectedScoreFromExtraLevel($stdClass->extraLevel, $stdClass->rawRatingValue + sprintf("%.2f", $totalMusicCount / 100));
        if($stdClass->targetMusicRateUser !== false){
            $stdClass->targetMusicRateUser = number_format($stdClass->technical_high_score - $stdClass->targetMusicRateUser);
        }

        // レート値が理論値 / 推定値なら文字装飾
        if(OngekiUtility::IsEstimatedRateValueFromTitle($stdClass->title, $stdClass->difficulty, $stdClass->technical_high_score)){
            $stdClass->ratingValue = "<i><span class='estimated-rating'>" . $stdClass->ratingValue . "</span></i>";
        }else if($stdClass->technical_high_score >= 1007500){
            $stdClass->ratingValue = "<i><span class='max-rating'>" . $stdClass->ratingValue . "</span></i>";
        }

        $stdClass->difficulty_str = $this->difficultyToStr[$stdClass->difficulty];
    }

    function getIndex($id){
        $user = User::where('id' ,$id)->first();

        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);

        if(count($status) === 0){
            if(is_null(User::where('id' ,$id)->first())){
                abort(404);
            }else{
                return view("user_error", ['message' => '<p>このユーザーはOngekiScoreLogに登録していますが、オンゲキNETからスコア取得を行っていません。(UserID: ' . $id . ')</p><p>スコアの取得方法は<a href="/howto">こちら</a>をお読みください。</p>']);
            }
        }

        $sidemark = null;
        if(Auth::check() && \Auth::user()->id == $id){
            $sidemark = "sidemark_mypage_rating";
        }

        if($user->role < 2){
            return view("user_rating_error", compact('id', 'status', 'sidemark'));
        }

        $statistics = new \stdClass;
        $statistics->newBestRatingCount = 15;
        $statistics->newBestRatingTotal = 0;
        $statistics->newBestRatingTop = 0;
        $statistics->newBestRatingMin = null;
        $statistics->oldBestRatingCount = 30;
        $statistics->oldBestRatingTotal = 0;
        $statistics->oldBestRatingTop = 0;
        $statistics->oldBestRatingMin = null;
        $statistics->recentRatingCount = 10;
        $statistics->recentRatingTotal = 0;
        $statistics->recentRatingTop = 0;
        $statistics->recentRatingMin = null;
        $statistics->totalRatingCount = $statistics->newBestRatingCount + $statistics->oldBestRatingCount + $statistics->recentRatingCount;
        $statistics->totalRatingTotal = 0;
        $statistics->totalRatingTop = 0;
        $statistics->totalRatingMin = null;
        
        $notExistMusic = new \stdClass;
        $notExistMusic->title = "-";
        $notExistMusic->difficulty_str = "-";
        $notExistMusic->level_str = "-";
        $notExistMusic->technical_high_score = 0;
        $notExistMusic->technical_score = 0;
        $notExistMusic->ratingValue = "-";
        $notExistMusic->rawRatingValue = 0;
        $notExistMusic->targetMusicRateMusic = "";
        $notExistMusic->targetMusicRateUser = "";
        $notExistMusic->updated_at = date("Y/m/d");

        $newScore = (new ScoreData())->getRatingNewUserScore($id)->addMusicData()->getValue();
        $oldScore = (new ScoreData())->getRatingOldUserScore($id)->addMusicData()->getValue();
        $recentScore = json_decode(json_encode(RatingRecentMusic::where('user_id', $id)->get()), true);

        // 新曲枠のレート計算
        foreach ($newScore as $key => $value) {
            self::editMusicStdClass($value, $statistics->totalRatingCount);
        }
        array_multisort(array_column($newScore, 'rawRatingValue'), SORT_DESC, $newScore);

        // 旧曲枠のレート計算
        foreach ($oldScore as $key => $value) {
            self::editMusicStdClass($value, $statistics->totalRatingCount);
        }
        array_multisort(array_column($oldScore, 'rawRatingValue'), SORT_DESC, $oldScore);

        // 新曲枠対象曲 統計情報の処理
        for ($i = 0; $i < $statistics->newBestRatingCount; ++$i) {
            if(!array_key_exists($i, $newScore)){
                $newScore[] = $notExistMusic;
            }else{
                $statistics->newBestRatingTotal += $newScore[$i]->rawRatingValue;
                if($statistics->newBestRatingTop < $newScore[$i]->rawRatingValue){
                    $statistics->newBestRatingTop = $newScore[$i]->rawRatingValue;
                }
                if(is_null($statistics->newBestRatingMin) || $statistics->newBestRatingMin > $newScore[$i]->rawRatingValue){
                    $statistics->newBestRatingMin = $newScore[$i]->rawRatingValue;
                }
            }
        }

        // 旧曲枠対象曲 統計情報の処理
        for ($i = 0; $i < $statistics->oldBestRatingCount; ++$i) { 
            if(!array_key_exists($i, $oldScore)){
                $oldScore[] = $notExistMusic;
            }else{
                $statistics->oldBestRatingTotal += $oldScore[$i]->rawRatingValue;
                if($statistics->oldBestRatingTop < $oldScore[$i]->rawRatingValue){
                    $statistics->oldBestRatingTop = $oldScore[$i]->rawRatingValue;
                }
                if(is_null($statistics->oldBestRatingMin) || $statistics->oldBestRatingMin > $oldScore[$i]->rawRatingValue){
                    $statistics->oldBestRatingMin = $oldScore[$i]->rawRatingValue;
                }
            }
        }

        // 新曲枠対象外曲 統計情報の処理
        for ($i = $statistics->newBestRatingCount; $i < count($newScore); ++$i) {
            $newScore[$i]->minDifferenceRate = $newScore[$i]->rawRatingValue - $statistics->newBestRatingMin;
            $newScore[$i]->minDifferenceScore = OngekiUtility::ExpectedScoreFromExtraLevel($newScore[$i]->extraLevel, $statistics->newBestRatingMin + 0.01);
            if($newScore[$i]->minDifferenceScore !== false){
                $newScore[$i]->minDifferenceScore = number_format($newScore[$i]->technical_high_score - $newScore[$i]->minDifferenceScore);
            }else{
                $newScore[$i]->minDifferenceScore = "";
            }
        }

        // 旧曲枠対象外曲 統計情報の処理
        for ($i = $statistics->oldBestRatingCount; $i < count($oldScore); ++$i) {
            $oldScore[$i]->minDifferenceRate = $oldScore[$i]->rawRatingValue - $statistics->oldBestRatingMin;
            $oldScore[$i]->minDifferenceScore = OngekiUtility::ExpectedScoreFromExtraLevel($oldScore[$i]->extraLevel, $statistics->oldBestRatingMin + 0.01);
            if($oldScore[$i]->minDifferenceScore !== false){
                $oldScore[$i]->minDifferenceScore = number_format($oldScore[$i]->technical_high_score - $oldScore[$i]->minDifferenceScore);
            }else{
                $oldScore[$i]->minDifferenceScore = "";
            }
        }

        // リーセント枠のレート計算
        $notExistMusic = json_decode(json_encode($notExistMusic), true);
        for ($i = 0; $i < $statistics->recentRatingCount; ++$i) { 
            if(!array_key_exists($i, $recentScore)){
                $recentScore[] = $notExistMusic;
            }else{
                $recentScore[$i]['ratingValue'] = sprintf("%.2f", OngekiUtility::RateValueFromTitle($recentScore[$i]['title'], $recentScore[$i]['difficulty'], $recentScore[$i]['technical_score']));
                $recentScore[$i]['rawRatingValue'] = $recentScore[$i]['ratingValue'];
                if(OngekiUtility::IsEstimatedRateValueFromTitle($recentScore[$i]['title'], $recentScore[$i]['difficulty'], $recentScore[$i]['technical_score'])){
                    $recentScore[$i]['ratingValue'] = "<i><span class='estimated-rating'>" . $recentScore[$i]['ratingValue'] . "</span></i>";
                }else if($recentScore[$i]['technical_score'] >= 1007500){
                    $recentScore[$i]['ratingValue'] = "<i><span class='max-rating'>" . $recentScore[$i]['ratingValue'] . "</span></i>";
                }
                $recentScore[$i]['difficulty_str'] = $this->difficultyToStr[$recentScore[$i]['difficulty']];
                $recentScore[$i]['level_str'] = OngekiUtility::GetMusicLevel($recentScore[$i]['title'], $recentScore[$i]['difficulty'], true);

                $statistics->recentRatingTotal += $recentScore[$i]['rawRatingValue'];
                if($statistics->recentRatingTop < $recentScore[$i]['rawRatingValue']){
                    $statistics->recentRatingTop = $recentScore[$i]['rawRatingValue'];
                }
                if(is_null($statistics->recentRatingMin) || $statistics->recentRatingMin > $recentScore[$i]['rawRatingValue']){
                    $statistics->recentRatingMin = $recentScore[$i]['rawRatingValue'];
                }
            }
        }

        $statistics->totalRatingTotal = $statistics->newBestRatingTotal + $statistics->oldBestRatingTotal + $statistics->recentRatingTotal;
        $statistics->totalRatingTop = max([$statistics->newBestRatingTop, $statistics->oldBestRatingTop, $statistics->recentRatingTop]);
        $statistics->totalRatingMin = min([$statistics->newBestRatingMin, $statistics->oldBestRatingMin, $statistics->recentRatingMin]);

        $statistics->maxRatingTotal = $statistics->newBestRatingTotal + $statistics->oldBestRatingTotal + ($statistics->totalRatingTop * $statistics->recentRatingCount);

        return view("user_rating", compact('status', 'id', 'sidemark', 'statistics', 'newScore', 'oldScore', 'recentScore'));
    }
}

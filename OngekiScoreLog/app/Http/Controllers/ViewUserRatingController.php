<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserStatus;
use App\ScoreData;
use App\RatingRecentMusic;
use App\Facades\OngekiUtility;
use App\Facades\Slack;
use App\RatingPlatinumMusic;

class ViewUserRatingController extends Controller
{
    private $difficultyToStr = [
        0 => 'Basic',
        1 => 'Advanced',
        2 => 'Expert',
        3 => 'Master',
        10 => 'Lunatic',
    ];

    private function editMusic($scores, int $totalMusicCount){
        for ($index = 0; $index < count($scores); $index++) {
            if (isset($scores[$index])) {
                    // ランプ情報追加
                $scores[$index]->lampForRating = "";
                if ($scores[$index]->technical_high_score == 1010000){
                    if ($scores[$index]->full_bell == 1) {
                        $scores[$index]->lampForRating = "FB/AB+";
                    } else {
                        $scores[$index]->lampForRating = "AB+";
                    }
                } elseif ($scores[$index]->all_break == 1) {
                    if ($scores[$index]->full_bell == 1) {
                        $scores[$index]->lampForRating = "FB/AB";
                    } else {
                        $scores[$index]->lampForRating = "AB";
                    }
                } elseif ($scores[$index]->full_combo == 1) {
                    if ($scores[$index]->full_bell == 1) {
                        $scores[$index]->lampForRating = "FB/FC";
                    } else {
                        $scores[$index]->lampForRating = "FC";
                    }
                } else {
                    if ($scores[$index]->full_bell == 1) {
                        $scores[$index]->lampForRating = "FB";
                    }
                }

                // 単極レート値の取得
                $scores[$index]->ratingValue = sprintf("%.3f", OngekiUtility::RateValueFromTitle($scores[$index]->title, $scores[$index]->difficulty, $scores[$index]->technical_high_score, $scores[$index]->lampForRating, $scores[$index]->genre, $scores[$index]->artist));
                $scores[$index]->rawRatingValue = $scores[$index]->ratingValue;

                // レート値上昇推定スコア計算
                $scores[$index]->extraLevel = OngekiUtility::ExtraLevelFromTitle($scores[$index]->title, $scores[$index]->difficulty, $scores[$index]->genre, $scores[$index]->artist);
                $scores[$index]->extraLevelStr = sprintf("%.1f", $scores[$index]->extraLevel);

                $scores[$index]->targetMusicRateMusic = OngekiUtility::ExpectedScoreFromExtraLevel($scores[$index]->extraLevel, $scores[$index]->rawRatingValue + 0.01);
                if($scores[$index]->targetMusicRateMusic !== false){
                    $scores[$index]->targetMusicRateMusic = number_format($scores[$index]->technical_high_score - $scores[$index]->targetMusicRateMusic);
                }

                // この曲のレート値が0.xのしきい値になるために必要なスコア 例) 15.25→15.30
                $targetRating = ceil(($scores[$index]->rawRatingValue + 0.01) * 10) / 10;
                $scores[$index]->targetMusicRateBorder = OngekiUtility::ExpectedScoreFromExtraLevel($scores[$index]->extraLevel, $targetRating);
                if($scores[$index]->targetMusicRateBorder !== false){
                    $scores[$index]->targetMusicRateBorder = number_format($scores[$index]->technical_high_score - $scores[$index]->targetMusicRateBorder);
                }

                $scores[$index]->targetMusicRateUser = OngekiUtility::ExpectedScoreFromExtraLevel($scores[$index]->extraLevel, $scores[$index]->rawRatingValue + sprintf("%.2f", $totalMusicCount / 100));
                if($scores[$index]->targetMusicRateUser !== false){
                    $scores[$index]->targetMusicRateUser = number_format($scores[$index]->technical_high_score - $scores[$index]->targetMusicRateUser);
                }

                // 新曲枠専用 レート寄与値 (/5する)
                // 小数点3桁で四捨五入する
                $singleRatingValue =  sprintf("%.3f", floor($scores[$index]->ratingValue / 5 * 1000) / 1000);

                if (OngekiUtility::IsEstimatedRateValueFromTitle($scores[$index]->title, $scores[$index]->difficulty, $scores[$index]->genre, $scores[$index]->artist)) {
                    $scores[$index]->singleRatingValue = "<i><span class='estimated-rating'>" . $singleRatingValue . "</span></i>";
                }elseif($scores[$index]->technical_high_score == 1010000){
                    $scores[$index]->singleRatingValue = "<i><span class='max-rating'>" . $singleRatingValue . "</span></i>";
                }elseif($scores[$index]->technical_high_score >= 1007500){
                    $scores[$index]->singleRatingValue = "<i><span class='upper-rating'>" . $singleRatingValue . "</span></i>";
                }

                // レート値が理論値 / 推定値なら文字装飾
                if (OngekiUtility::IsEstimatedRateValueFromTitle($scores[$index]->title, $scores[$index]->difficulty, $scores[$index]->genre, $scores[$index]->artist)) {
                    $scores[$index]->extraLevelStr = "<i><span class='estimated-rating'>" . $scores[$index]->extraLevelStr . "?</span></i>";
                    $scores[$index]->ratingValue = "<i><span class='estimated-rating'>" . $scores[$index]->ratingValue . "</span></i>";
                }elseif($scores[$index]->technical_high_score == 1010000){
                    $scores[$index]->ratingValue = "<i><span class='max-rating'>" . $scores[$index]->ratingValue . "</span></i>";
                }elseif($scores[$index]->technical_high_score >= 1007500){
                    $scores[$index]->ratingValue = "<i><span class='upper-rating'>" . $scores[$index]->ratingValue . "</span></i>";
                }

                $scores[$index]->difficulty_str = $this->difficultyToStr[$scores[$index]->difficulty];
            }
        }

        return $scores;
    }

    public function getIndex($id){
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

        if(!\App\UserInformation::IsPremiumPlan($user->id)){
            $message = "この機能を利用するにはオンゲキNETのプレミアムプランに課金する必要があります。課金情報は毎月1日にリセットされます。もし課金している場合は再度ブックマークを実行してください。";
            return view("user_rating_error", compact('id', 'status', 'sidemark', 'message'));
        }

        $messages = [];

        $statistics = new \stdClass;
        $statistics->newBestRatingCount = 10;
        $statistics->newBestRatingTotal = 0;
        $statistics->newBestRatingTop = 0;
        $statistics->newBestRatingMin = null;

        $statistics->oldBestRatingCount = 50;
        $statistics->oldBestRatingTotal = 0;
        $statistics->oldBestRatingTop = 0;
        $statistics->oldBestRatingMin = null;

        $statistics->platinumRatingCount = 50;
        $statistics->platinumRatingTotal = 0;
        $statistics->platinumRatingTop = 0;
        $statistics->platinumRatingMin = null;

        $statistics->totalRatingCount = $statistics->newBestRatingCount + $statistics->oldBestRatingCount + $statistics->platinumRatingCount;
        $statistics->totalRatingTotal = 0;
        $statistics->totalRatingTop = 0;
        $statistics->totalRatingMin = null;
        $statistics->potentialRatingTop = null;

        $notExistMusic = new \stdClass;
        $notExistMusic->title = "-";
        $notExistMusic->difficulty_str = "-";
        $notExistMusic->level_str = "-";
        $notExistMusic->technical_high_score = 0;
        $notExistMusic->technical_score = 0;
        $notExistMusic->platinum_score = 0;
        $notExistMusic->star = 0;
        $notExistMusic->extraLevelStr = "-";
        $notExistMusic->ratingValue = "-";
        $notExistMusic->rawRatingValue = 0;
        $notExistMusic->lamp = "";
        $notExistMusic->lampForRating = "";
        $notExistMusic->targetMusicRateMusic = "";
        $notExistMusic->targetMusicRateBorder = "";
        $notExistMusic->targetMusicRateUser = "";
        $notExistMusic->updated_at = date("Y/m/d");


        $newScore = (new ScoreData())->getRatingNewUserScore($id)->addMusicData()->getValue();
        $oldScore = (new ScoreData())->getRatingOldUserScore($id)->addMusicData()->getValue();
        $platinumMusic = json_decode(json_encode(RatingPlatinumMusic::where('user_id', $id)->get()), true);

        try {
            // 新曲枠のレート計算
            $newScore = $this->editMusic($newScore, $statistics->totalRatingCount);
            array_multisort(array_column($newScore, 'rawRatingValue'), SORT_DESC, $newScore);

            // 旧曲枠のレート計算
            $oldScore = $this->editMusic($oldScore, $statistics->totalRatingCount);
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

                    if($statistics->potentialRatingTop < $newScore[$i]->rawRatingValue && $newScore[$i]->difficulty != 10){
                        $statistics->potentialRatingTop = $newScore[$i]->rawRatingValue;
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

                    if($statistics->potentialRatingTop < $oldScore[$i]->rawRatingValue && $oldScore[$i]->difficulty != 10){
                        $statistics->potentialRatingTop = $oldScore[$i]->rawRatingValue;
                    }
                }
            }

            // 新曲枠対象外曲 統計情報の処理
            // for ($i = $statistics->newBestRatingCount; $i < count($newScore); ++$i) {
            //     $newScore[$i]->minDifferenceRate = $newScore[$i]->rawRatingValue - $statistics->newBestRatingMin;
            //     $newScore[$i]->minDifferenceScore = OngekiUtility::ExpectedScoreFromExtraLevel($newScore[$i]->extraLevel, $statistics->newBestRatingMin + 0.01);
            //     if($newScore[$i]->minDifferenceScore !== false){
            //         $newScore[$i]->minDifferenceScore = number_format($newScore[$i]->technical_high_score - $newScore[$i]->minDifferenceScore);
            //     }else{
            //         $newScore[$i]->minDifferenceScore = "";
            //     }
            // }

            // 旧曲枠対象外曲 統計情報の処理
            // for ($i = $statistics->oldBestRatingCount; $i < count($oldScore); ++$i) {
            //     $oldScore[$i]->minDifferenceRate = $oldScore[$i]->rawRatingValue - $statistics->oldBestRatingMin;
            //     $oldScore[$i]->minDifferenceScore = OngekiUtility::ExpectedScoreFromExtraLevel($oldScore[$i]->extraLevel, $statistics->oldBestRatingMin + 0.01);
            //     if($oldScore[$i]->minDifferenceScore !== false){
            //         $oldScore[$i]->minDifferenceScore = number_format($oldScore[$i]->technical_high_score - $oldScore[$i]->minDifferenceScore);
            //     }else{
            //         $oldScore[$i]->minDifferenceScore = "";
            //     }
            // }

            // プラチナスコア枠のレート計算
            $notExistMusic = json_decode(json_encode($notExistMusic), true);
            for ($i = 0; $i < $statistics->platinumRatingCount; ++$i) {
                if(!array_key_exists($i, $platinumMusic)){
                    $platinumMusic[] = $notExistMusic;
                }else{
                    try {
                        $platinumMusic[$i]['ratingValue'] = sprintf("%.3f", OngekiUtility::RateValueFromTitleForPlatinum($platinumMusic[$i]['title'], $platinumMusic[$i]['difficulty'], $platinumMusic[$i]['platinum_score'], $platinumMusic[$i]['star'], $platinumMusic[$i]['genre'], $platinumMusic[$i]['artist']));
                        $platinumMusic[$i]['rawRatingValue'] = $platinumMusic[$i]['ratingValue'];
                        $platinumMusic[$i]['song_id'] = OngekiUtility::GetIDFromTitle($platinumMusic[$i]['title'], $platinumMusic[$i]['genre'], $platinumMusic[$i]['artist']);
                        $platinumMusic[$i]['difficulty_str'] = $this->difficultyToStr[$platinumMusic[$i]['difficulty']];
                        $platinumMusic[$i]['level_str'] = sprintf("%.1f", OngekiUtility::ExtraLevelFromTitle($platinumMusic[$i]['title'], $platinumMusic[$i]['difficulty'], $platinumMusic[$i]['genre'], $platinumMusic[$i]['artist']));

                        $statistics->platinumRatingTotal += $platinumMusic[$i]['rawRatingValue'];
                        if($statistics->platinumRatingTop < $platinumMusic[$i]['rawRatingValue']){
                            $statistics->platinumRatingTop = $platinumMusic[$i]['rawRatingValue'];
                        }
                        if(is_null($statistics->platinumRatingMin) || $statistics->platinumRatingMin > $platinumMusic[$i]['rawRatingValue']){
                            $statistics->platinumRatingMin = $platinumMusic[$i]['rawRatingValue'];
                        }

                        // レート値が理論値 / 推定値なら文字装飾
                        if (OngekiUtility::IsEstimatedRateValueFromTitle($platinumMusic[$i]['title'], $platinumMusic[$i]['difficulty'], $platinumMusic[$i]['genre'], $platinumMusic[$i]['artist'])) {
                            $platinumMusic[$i]['level_str'] = "<i><span class='estimated-rating'>" . $platinumMusic[$i]['level_str'] . "?</span></i>";
                            $platinumMusic[$i]['ratingValue'] = "<i><span class='estimated-rating'>" . $platinumMusic[$i]['ratingValue'] . "</span></i>";
                        } elseif ($platinumMusic[$i]['star'] >= 5){
                            $platinumMusic[$i]['ratingValue'] = "<i><span class='max-rating'>" . $platinumMusic[$i]['ratingValue'] . "</span></i>";
                        } elseif($platinumMusic[$i]['star'] >= 4){
                            $platinumMusic[$i]['ratingValue'] = "<i><span class='upper-rating'>" . $platinumMusic[$i]['ratingValue'] . "</span></i>";
                        }
                    } catch (\OutOfBoundsException $e) {
                        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "N/A";
                        Slack::Notice("プラチナスコア枠に未知の楽曲が含まれているユーザーがいます。". $e->getMessage() . "\n" . get_class($e) . "\n" . url()->full(), "ip: " . \Request::ip() . "\nUser agent: " . $ua . "\nReferer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "N/A") . "\n\n", ["File" => $e->getFile(), "Line" => $e->getLine(), "IP Address" => \Request::ip(), "User page id" => $user->id], "warning");

                        $recent = $platinumMusic[$i];
                        $platinumMusic[$i] = $notExistMusic;
                        $platinumMusic[$i]['title'] = "(未知の楽曲) " . $recent['title'];
                        $platinumMusic[$i]['platinum_score'] = $recent['platinum_score'];
                        $platinumMusic[$i]['star'] = $recent['star'];
                        $platinumMusic[$i]['ratingValue'] = "<i><span class='estimated-rating'>0.000</span></i>";
                        $statistics->platinumRatingMin = 0;
                        $messages[] = "プラチナスコア枠に未知の楽曲が含まれるため、正常に計算を行えませんでした。この画面の情報は間違っている可能性があります。 対象: " . $recent['title'];
                    }
                }
            }
        } catch (\OutOfBoundsException $e) {
            $message = "レーティング枠に未知の楽曲が含まれるため、正常に計算を行えませんでした。ブックマークレットでのデータ取得をお試しください。解消しない場合はこちらの情報を添えてご報告いただけますと幸いです。" . $e->getMessage();
            $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "N/A";
            Slack::Warning("レーティング枠に未知の楽曲が含まれているユーザーがいます。" . $e->getMessage() . "\n" . get_class($e) . "\n" . url()->full(), "ip: " . \Request::ip() . "\nUser agent: " . $ua . "\nReferer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "N/A") . "\n\n", ["File" => $e->getFile(), "Line" => $e->getLine(), "IP Address" => \Request::ip(), "User page id" => $user->id], "warning");
            return view("user_rating_error", compact('id', 'status', 'sidemark', 'message'));
        }

        // トータルの計算
        $statistics->totalRatingCount = $statistics->newBestRatingCount + $statistics->oldBestRatingCount; // TODO: 上で代入してるんだけど間違っているので入れ直し そのへんの処理なおしたら統合してください
        $statistics->totalRatingTotal = $statistics->newBestRatingTotal + $statistics->oldBestRatingTotal;
        $statistics->totalRatingTop = max([$statistics->newBestRatingTop, $statistics->oldBestRatingTop]);
        $statistics->totalRatingMin = min([$statistics->newBestRatingMin, $statistics->oldBestRatingMin]);

        // レート統計 平均値計算
        $statistics->newRatingAverage = floor($statistics->newBestRatingTotal / $statistics->newBestRatingCount * 1000) / 1000;
        $statistics->oldBestRatingAverage = floor($statistics->oldBestRatingTotal / $statistics->oldBestRatingCount * 1000) / 1000;
        $statistics->totalRatingAverage = floor(($statistics->newBestRatingTotal + $statistics->oldBestRatingTotal) / $statistics->totalRatingCount * 1000) / 1000;
        $statistics->platinumRatingAverage = floor($statistics->platinumRatingTotal / $statistics->platinumRatingCount * 1000) / 1000;

        // レート統計 レート寄与値
        $statistics->newRatingContribute = floor($statistics->newRatingAverage / 5 * 1000) / 1000;
        $statistics->oldBestRatingContribute = $statistics->oldBestRatingAverage;
        $statistics->totalRatingContribute = $statistics->newRatingContribute + $statistics->oldBestRatingContribute;
        $statistics->platinumRatingContribute = $statistics->platinumRatingAverage;

        // レーティング
        $statistics->ratingCalc = floor(($statistics->newRatingContribute + $statistics->oldBestRatingContribute + $statistics->platinumRatingContribute) * 1000) / 1000;

        return view("user_rating", compact('messages', 'status', 'id', 'sidemark', 'statistics', 'newScore', 'oldScore', 'platinumMusic'));
    }
}

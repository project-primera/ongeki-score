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
use App\Services\RatingCalculator;

class ViewUserRatingController extends Controller
{
    private $difficultyToStr = [
        0 => 'Basic',
        1 => 'Advanced',
        2 => 'Expert',
        3 => 'Master',
        10 => 'Lunatic',
    ];

    private function editMusic($scores){
        for ($index = 0; $index < count($scores); $index++) {
            if (isset($scores[$index])) {
                // ランプ情報追加
                $scores[$index]->lampForRating = OngekiUtility::getLampForRating($scores[$index]->technical_high_score, $scores[$index]->full_bell == 1, $scores[$index]->full_combo == 1, $scores[$index]->all_break == 1);

                // 単曲レート値の取得 並べ替えと集計には整数（1/1000 単位）の ratingMilli を使う
                $scores[$index]->ratingMilli = OngekiUtility::technicalRatingMilli($scores[$index]->title, $scores[$index]->difficulty, $scores[$index]->technical_high_score, $scores[$index]->lampForRating, $scores[$index]->genre, $scores[$index]->artist);
                $scores[$index]->ratingValue = RatingCalculator::formatMilli($scores[$index]->ratingMilli);

                // 譜面定数
                $scores[$index]->extraLevel = OngekiUtility::ExtraLevelFromTitle($scores[$index]->title, $scores[$index]->difficulty, $scores[$index]->genre, $scores[$index]->artist);
                $scores[$index]->extraLevelStr = sprintf("%.1f", $scores[$index]->extraLevel);

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

    /**
     * 枠に入った曲の単曲レート値から、統計表の 1 行ぶんの値を表示用の文字列で返す
     * 平均値・合計・寄与値は小数 5 桁、最大・最小は単曲レート値そのものなので小数 3 桁
     *
     * @param int[] $ratings 枠に入った曲の単曲レート値（1/1000 単位） 曲が足りない枠は、ある分だけ
     * @param int $count 枠の曲数
     * @return array{average: string, top: string, min: string, total: string, contribute: string}
     */
    private function summarizeFrame(array $ratings, int $count): array
    {
        $total = array_sum($ratings);

        return [
            'average' => RatingCalculator::formatAggregate(RatingCalculator::average($total, $count)),
            'top' => RatingCalculator::formatMilli($ratings === [] ? 0 : max($ratings)),
            'min' => RatingCalculator::formatMilli($ratings === [] ? 0 : min($ratings)),
            'total' => RatingCalculator::formatAggregate(RatingCalculator::milliToAggregate($total)),
            'contribute' => RatingCalculator::formatAggregate(RatingCalculator::contribution($total)),
        ];
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
        $statistics->oldBestRatingCount = 50;
        $statistics->platinumRatingCount = 50;

        // 各枠に入った曲の単曲レート値（1/1000 単位の整数）
        $newRatings = [];
        $oldRatings = [];
        $platinumRatings = [];

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
        $notExistMusic->lamp = "";
        $notExistMusic->lampForRating = "";
        $notExistMusic->updated_at = date("Y/m/d");


        $newScore = (new ScoreData())->getRatingNewUserScore($id)->addMusicData()->getValue();
        $oldScore = (new ScoreData())->getRatingOldUserScore($id)->addMusicData()->getValue();
        $platinumMusic = json_decode(json_encode(RatingPlatinumMusic::where('user_id', $id)->get()), true);

        try {
            // 新曲枠のレート計算
            $newScore = $this->editMusic($newScore);
            array_multisort(array_column($newScore, 'ratingMilli'), SORT_DESC, $newScore);

            // 旧曲枠のレート計算
            $oldScore = $this->editMusic($oldScore);
            array_multisort(array_column($oldScore, 'ratingMilli'), SORT_DESC, $oldScore);

            // 新曲枠対象曲 統計情報の処理
            for ($i = 0; $i < $statistics->newBestRatingCount; ++$i) {
                if(!array_key_exists($i, $newScore)){
                    $newScore[] = $notExistMusic;
                }else{
                    $newRatings[] = $newScore[$i]->ratingMilli;
                }
            }

            // 旧曲枠対象曲 統計情報の処理
            for ($i = 0; $i < $statistics->oldBestRatingCount; ++$i) {
                if(!array_key_exists($i, $oldScore)){
                    $oldScore[] = $notExistMusic;
                }else{
                    $oldRatings[] = $oldScore[$i]->ratingMilli;
                }
            }

            // プラチナスコア枠のレート計算
            $notExistMusic = json_decode(json_encode($notExistMusic), true);
            for ($i = 0; $i < $statistics->platinumRatingCount; ++$i) {
                if(!array_key_exists($i, $platinumMusic)){
                    $platinumMusic[] = $notExistMusic;
                }else{
                    try {
                        $ratingMilli = OngekiUtility::platinumRatingMilli($platinumMusic[$i]['title'], $platinumMusic[$i]['difficulty'], $platinumMusic[$i]['star'], $platinumMusic[$i]['genre'], $platinumMusic[$i]['artist']);
                        $platinumMusic[$i]['ratingValue'] = RatingCalculator::formatMilli($ratingMilli);
                        $platinumMusic[$i]['song_id'] = OngekiUtility::GetIDFromTitle($platinumMusic[$i]['title'], $platinumMusic[$i]['genre'], $platinumMusic[$i]['artist']);
                        $platinumMusic[$i]['difficulty_str'] = $this->difficultyToStr[$platinumMusic[$i]['difficulty']];
                        $platinumMusic[$i]['level_str'] = sprintf("%.1f", OngekiUtility::ExtraLevelFromTitle($platinumMusic[$i]['title'], $platinumMusic[$i]['difficulty'], $platinumMusic[$i]['genre'], $platinumMusic[$i]['artist']));

                        $platinumRatings[] = $ratingMilli;

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
                        // 未知の楽曲は 0 として数える
                        $platinumRatings[] = 0;
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

        // レート統計 表示用の文字列にする
        $newFrame = $this->summarizeFrame($newRatings, $statistics->newBestRatingCount);
        $statistics->newRatingAverage = $newFrame['average'];
        $statistics->newBestRatingTop = $newFrame['top'];
        $statistics->newBestRatingMin = $newFrame['min'];
        $statistics->newBestRatingTotal = $newFrame['total'];
        $statistics->newRatingContribute = $newFrame['contribute'];

        $oldFrame = $this->summarizeFrame($oldRatings, $statistics->oldBestRatingCount);
        $statistics->oldBestRatingAverage = $oldFrame['average'];
        $statistics->oldBestRatingTop = $oldFrame['top'];
        $statistics->oldBestRatingMin = $oldFrame['min'];
        $statistics->oldBestRatingTotal = $oldFrame['total'];
        $statistics->oldBestRatingContribute = $oldFrame['contribute'];

        $platinumFrame = $this->summarizeFrame($platinumRatings, $statistics->platinumRatingCount);
        $statistics->platinumRatingAverage = $platinumFrame['average'];
        $statistics->platinumRatingTop = $platinumFrame['top'];
        $statistics->platinumRatingMin = $platinumFrame['min'];
        $statistics->platinumRatingTotal = $platinumFrame['total'];
        $statistics->platinumRatingContribute = $platinumFrame['contribute'];

        // レーティング 3 枠の合計を 50 で割る 枠ごとの寄与値を足した値と一致する
        $statistics->ratingCalc = RatingCalculator::formatAggregate(RatingCalculator::rating(array_sum($newRatings), array_sum($oldRatings), array_sum($platinumRatings)));

        return view("user_rating", compact('messages', 'status', 'id', 'sidemark', 'statistics', 'newScore', 'oldScore', 'platinumMusic'));
    }
}

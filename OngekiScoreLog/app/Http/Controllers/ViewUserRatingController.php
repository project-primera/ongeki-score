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
        $notExistMusic->updated_at = date("Y/m/d");

        $newScore = (new ScoreData())->getRatingNewUserScore($id)->addMusicData();
        $oldScore = (new ScoreData())->getRatingOldUserScore($id)->addMusicData();
        $recentScore = RatingRecentMusic::where('user_id', $id)->get();
        $recentScore = json_decode(json_encode($recentScore), true);

        $difficultyToStr = [
            0 => 'Basic',
            1 => 'Advanced',
            2 => 'Expert',
            3 => 'Master',
            10 => 'Lunatic',
        ];

        foreach ($newScore as $key => $value) {
            $newScore[$key]->ratingValue = sprintf("%.2f", OngekiUtility::RateValueFromTitle($newScore[$key]->title, $newScore[$key]->difficulty, $newScore[$key]->technical_high_score));
            $newScore[$key]->rawRatingValue = $newScore[$key]->ratingValue;
            if(OngekiUtility::IsEstimatedRateValueFromTitle($newScore[$key]->title, $newScore[$key]->difficulty, $newScore[$key]->technical_high_score)){
                $newScore[$key]->ratingValue = "<i><span class='estimated-rating'>" . $newScore[$key]->ratingValue . "</span></i>";
            }else if($newScore[$key]->technical_high_score >= 1007500){
                $newScore[$key]->ratingValue = "<i><span class='max-rating'>" . $newScore[$key]->ratingValue . "</span></i>";
            }
            $newScore[$key]->difficulty_str = $difficultyToStr[$value->difficulty];
        }
        array_multisort(array_column($newScore, 'rawRatingValue'), SORT_DESC, $newScore);

        foreach ($oldScore as $key => $value) {
            $oldScore[$key]->ratingValue = sprintf("%.2f", OngekiUtility::RateValueFromTitle($oldScore[$key]->title, $oldScore[$key]->difficulty, $oldScore[$key]->technical_high_score));
            $oldScore[$key]->rawRatingValue = $oldScore[$key]->ratingValue;
            if(OngekiUtility::IsEstimatedRateValueFromTitle($oldScore[$key]->title, $oldScore[$key]->difficulty, $oldScore[$key]->technical_high_score)){
                $oldScore[$key]->ratingValue = "<i><span class='estimated-rating'>" . $oldScore[$key]->ratingValue . "</span></i>";
            }else if($oldScore[$key]->technical_high_score >= 1007500){
                $oldScore[$key]->ratingValue = "<i><span class='max-rating'>" . $oldScore[$key]->ratingValue . "</span></i>";
            }
            $oldScore[$key]->difficulty_str = $difficultyToStr[$value->difficulty];
        }
        array_multisort(array_column($oldScore, 'rawRatingValue'), SORT_DESC, $oldScore);

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
                $recentScore[$i]['difficulty_str'] = $difficultyToStr[$recentScore[$i]['difficulty']];
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
        $statistics->totalRatingMin = max([$statistics->newBestRatingMin, $statistics->oldBestRatingMin, $statistics->recentRatingMin]);

        return view("user_rating", compact('status', 'id', 'sidemark', 'statistics', 'newScore', 'oldScore', 'recentScore'));
    }
}

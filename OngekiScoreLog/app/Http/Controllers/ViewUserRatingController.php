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
        if($user->role < 2){
            echo "todo: みせない";
        }

        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);

        if(count($status) === 0){
            if(is_null(User::where('id' ,$id)->first())){
                abort(404);
            }else{
                return view("user_error", ['id' => $id]);
            }
        }

        $sidemark = null;
        if(Auth::check() && \Auth::user()->id == $id){
            $sidemark = "sidemark_mypage_rating";
        }

        $statistics = new \stdClass;
        $statistics->newBestRatingCount = 15;
        $statistics->newBestRatingTotal = 0;
        $statistics->oldBestRatingCount = 30;
        $statistics->oldBestRatingTotal = 0;
        $statistics->recentRatingCount = 10;
        $statistics->recentRatingTotal = 0;
        $statistics->topRating = 0;
        
        $notExistMusic = new \stdClass;
        $notExistMusic->title = "-";
        $notExistMusic->difficulty_str = "-";
        $notExistMusic->level_str = "-";
        $notExistMusic->technical_high_score = "-";
        $notExistMusic->technical_high_score = "-";
        $notExistMusic->ratingValue = "-";
        $notExistMusic->updated_at = "-";

        $newScore = (new ScoreData())->getRatingNewUserScore($id)->addMusicData();
        $oldScore = (new ScoreData())->getRatingOldUserScore($id)->addMusicData();
        $recentScore = RatingRecentMusic::where('user_id', $id)->get();

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

        foreach ($recentScore as $key => $value) {
            $recentScore[$key]->ratingValue = sprintf("%.2f", OngekiUtility::RateValueFromTitle($recentScore[$key]->title, $recentScore[$key]->difficulty, $recentScore[$key]->technical_score));
            $recentScore[$key]->rawRatingValue = $recentScore[$key]->ratingValue;
            if(OngekiUtility::IsEstimatedRateValueFromTitle($recentScore[$key]->title, $recentScore[$key]->difficulty, $recentScore[$key]->technical_score)){
                $recentScore[$key]->ratingValue = "<i><span class='estimated-rating'>" . $recentScore[$key]->ratingValue . "</span></i>";
            }else if($recentScore[$key]->technical_high_score >= 1007500){
                $recentScore[$key]->ratingValue = "<i><span class='max-rating'>" . $recentScore[$key]->ratingValue . "</span></i>";
            }
            $recentScore[$key]->difficulty_str = $difficultyToStr[$value->difficulty];
            $recentScore[$key]->level_str = OngekiUtility::GetMusicLevel($recentScore[$key]->title, $recentScore[$key]->difficulty, true);
        }

        for ($i = 0; $i < $statistics->newBestRatingCount; ++$i) { 
            $statistics->newBestRatingTotal += $newScore[$i]->rawRatingValue;
        }

        for ($i = 0; $i < $statistics->oldBestRatingCount; ++$i) { 
            $statistics->oldBestRatingTotal += $oldScore[$i]->rawRatingValue;
        }

        return view("user_rating", compact('status', 'id', 'sidemark', 'statistics', 'newScore', 'oldScore', 'recentScore'));
    }
}

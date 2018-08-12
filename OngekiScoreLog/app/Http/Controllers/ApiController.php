<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\UserStatus;
use App\CharacterFriendly;
use App\RatingRecentMusic;
use App\UserTrophy;

use Log;

class ApiController extends Controller
{
    //
    public function getUserUpdate()
    {
    }

    public function postUserUpdate(Request $request)
    {
        try{
            $userStatus = UserStatus::find(Auth::id());
            if(is_null($userStatus)){
                $userStatus = new UserStatus();
                $userStatus->id = Auth::id();
            }
            $userStatus->fill($request['PlayerData']);
            $userStatus->save();


            foreach ($request['CharacterFriendlyData']['friendly'] as $key => $value) {
                $characterFriendly = new CharacterFriendly();
                $characterFriendly->user_id = Auth::id();
                $characterFriendly->character_id = $key;
                $characterFriendly->value = $value;
                $characterFriendly->save();
            }

            DB::table('rating_recent_musics')->where('user_id', '=', Auth::id())->delete();
            foreach ($request['RatingRecentMusicData']['ratingRecentMusicObject'] as $key => $value) {
                $ratingRecentMusic = new RatingRecentMusic();
                $ratingRecentMusic->user_id = Auth::id();
                $ratingRecentMusic->rank = $key;
                $ratingRecentMusic->title = $value['title'];
                $ratingRecentMusic->difficulty = $value['difficulty'];
                $ratingRecentMusic->technical_score = $value['technicalScore'];
                $ratingRecentMusic->save();
            }

            $trophyGrade = ["normalTrophyInfos" => 0,"bronzeTrophyInfo" => 1, "silverTrophyInfos" => 2, "goldTrophyInfos" => 3, "platinumTrophyInfo" => 4];
            foreach ($request['TrophyData'] as $key => $value) {
                foreach ($value as $k => $v) {
                    $record = DB::table('user_trophies')->where([
                        ['user_id', '=', Auth::id()],
                        ['name', '=', $v['name']],
                    ])->get();
                    if(count($record) > 0){
                        continue;
                    }
  
                    $userTrophy = new UserTrophy();
                    $userTrophy->user_id = Auth::id();
                    $userTrophy->grade = $trophyGrade[$key];
                    $userTrophy->name = $v['name'];
                    $userTrophy->detail = $v['detail'];
                    $userTrophy->save();
                }
            }

            return "saved";

        }catch(\PDOException $e){
            Log::error($e);
            return "error";
        }
        


    }
}

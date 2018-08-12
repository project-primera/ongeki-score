<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\UserStatus;
use App\CharacterFriendly;
use App\RatingRecentMusic;
use App\UserTrophy;
use App\UniqueIDForRequest;

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
            $uniqueID = md5(uniqid(rand(),1));

            $uniqueIDForRequest = new UniqueIDForRequest();
            $uniqueIDForRequest->ip_address = \Request::ip();
            $uniqueIDForRequest->unique_id =$uniqueID;
            $uniqueIDForRequest->save();


            $userStatus = new UserStatus();
            $userStatus->user_id = Auth::id();
            $userStatus->fill($request['PlayerData']);
            $userStatus->unique_id =$uniqueID;
            $userStatus->save();


            foreach ($request['CharacterFriendlyData']['friendly'] as $key => $value) {
                $characterFriendly = new CharacterFriendly();
                $characterFriendly->user_id = Auth::id();
                $characterFriendly->character_id = $key;
                $characterFriendly->value = $value;
                $characterFriendly->unique_id =$uniqueID;
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
                $ratingRecentMusic->unique_id =$uniqueID;
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
                    $userTrophy->unique_id =$uniqueID;
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

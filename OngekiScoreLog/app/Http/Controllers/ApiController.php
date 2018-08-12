<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\UserStatus;
use App\CharacterFriendly;
use App\RatingRecentMusic;

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

            return "saved";

        }catch(\PDOException $e){
            return "error";
        }
        


    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\UserStatus;
use App\CharacterFriendly;

use Log;

class ApiController extends Controller
{
    //
    public function getUserUpdate()
    {
    }

    public function postUserUpdate(Request $request)
    {
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

        

        return "saved";
    }
}

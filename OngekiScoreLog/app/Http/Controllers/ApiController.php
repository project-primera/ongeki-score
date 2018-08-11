<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\UserStatus;
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

        return "saved";
    }
}

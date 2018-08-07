<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\UserStatus;

class ApiController extends Controller
{
    //
    public function getUserUpdate()
    {
    }

    public function postUserUpdate(Request $request)
    {
        if (!Auth::check()) {
            return [
                "status" => "error",
                "message" => env("APP_NAME") . "にログインを行ってからブックマークレットを実行してください。",
                "location" => "https://" . $_SERVER['SERVER_NAME'],
            ];
        }
        
        $userStatus = new UserStatus();
        $userStatus->fill($request['playerData']);
        $userStatus->id = Auth::id();
        $userStatus->save();

        return "saved";
    }
}

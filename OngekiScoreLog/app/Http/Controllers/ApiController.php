<?php

namespace App\Http\Controllers;

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
        $userStatus = new UserStatus();
        $userStatus->fill($request['playerData'])->save();

        return "saved";
    }
}

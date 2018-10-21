<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserStatus;

class ViewAllUserController extends Controller
{
    public function getIndex(){
        $userStatus = new UserStatus();
        $users = $userStatus->getRecentAllUserData();
        return view('alluser', compact('users'));
    }
}
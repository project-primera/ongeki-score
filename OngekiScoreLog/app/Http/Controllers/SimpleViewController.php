<?php

namespace App\Http\Controllers;

use App;
use Auth;
use Illuminate\Http\Request;

class SimpleViewController extends Controller
{
    public function getIndex(){
        return view('top');
    }

    public function getHowto(){
        return view('howto');
    }

    public function getEula(){
        return view('eula');
    }

    public function getChangelog(){
        $v = new App\ApplicationVersion();
        $version = $v->getAllVersion();
        return view('changelog', compact('version'));
    }

    public function getLogout(){
        Auth::logout();
        return view('logout');
    }
}

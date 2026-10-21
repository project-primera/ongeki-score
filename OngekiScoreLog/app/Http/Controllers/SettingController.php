<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getSetting(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $display = [];
        $display['private'] = $user->private;

        return view('setting', compact('display'));
    }

    public function getSettingPrivate(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $user->private = 1;
        $user->save();

        return redirect("/setting");
    }

    public function getSettingPublic(){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        $user->private = 0;
        $user->save();

        return redirect("/setting");
    }

}

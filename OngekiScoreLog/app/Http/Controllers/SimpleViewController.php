<?php

namespace App\Http\Controllers;

use App;
use Auth;
use Illuminate\Support\Facades\Storage;

class SimpleViewController extends Controller
{
    public function getIndex(){
        return view('top');
    }

    public function getHowto(){
        return view('howto');
    }

    public function getFAQ(){
        return view('faq');
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

    public function getLogFile($path, $fileName){
        return "<html><body><div style='font-size: 0.25rem'>\n" . str_replace("\n", "<br>\n", Storage::get('log/' . $path . '/' . $fileName)) . "\n</div></body></html>";
    }

    public function getApiLive(){
        return 'live';
    }

}

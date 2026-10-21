<?php

namespace App\Http\Controllers;

use App;
use Auth;
use Illuminate\Http\Request;
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

    public function getDataPolicy(){
        return view('data-policy');
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
        $file = 'log/' . $path . '/' . $fileName;
        if (!Storage::exists($file)) {
            throw new \Illuminate\Contracts\Filesystem\FileNotFoundException("File does not exist at path {$file}.");
        }
        return "<html><body><div style='font-size: 0.25rem'>\n" . str_replace("\n", "<br>\n", Storage::get($file)) . "\n</div></body></html>";
    }

    /* for debug
    public function versionUpdate(){
        (new App\ApplicationVersion())->fetchAllVersion();
    }
    */

}

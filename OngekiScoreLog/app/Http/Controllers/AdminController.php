<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use \Artisan;

class AdminController extends Controller{

    public function GetIndex(Request $request){
        $message = $request->input('message', null);
        return view('admin/index', compact(['message']));
    }
}

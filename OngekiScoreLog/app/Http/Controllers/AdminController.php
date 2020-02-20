<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use \Artisan;

class AdminController extends Controller{

    public function GetIndex(Request $request){
        $message = $request->input('message', null);
        return view('admin/index', compact(['message']));
    }

    public function GetApply($type, $action = null){
        $message = "パスが間違っています。";

        if($type === 'config'){
            if($action === "clear"){
                Artisan::call('config:clear');
                $message = "config:clearを実行しました。本番環境の場合は、必ず生成を行ってください。";
            }else if($action === "cache"){
                Artisan::call('config:cache');
                $message = "config:cacheを実行しました。";
            }
        }else if($type === 'route'){
            if($action === "clear"){
                Artisan::call('route:clear');
                $message = "route:clearを実行しました。本番環境の場合は、必ず生成を行ってください。";
            }else if($action === "cache"){
                Artisan::call('route:cache');
                $message = "route:cacheを実行しました。";
            }
        }else if($type === 'view'){
            if($action === "clear"){
                Artisan::call('view:clear');
                $message = "view:clearを実行しました。本番環境の場合は、必ず生成を行ってください。";
            }else if($action === "cache"){
                Artisan::call('view:cache');
                $message = "view:cacheを実行しました。";
            }
        }else if($type === 'cache'){
            Artisan::call('cache:clear');
            $message = "cache:clearを実行しました。";
        }

        return redirect('/admin?message=' . $message);
    }
}

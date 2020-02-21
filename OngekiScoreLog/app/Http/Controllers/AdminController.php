<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use \Artisan;

class AdminController extends Controller{

    public function GetIndex(Request $request){
        $message = $request->input('message', null);
        return view('admin/index', compact(['message']));
    }

    private function parseConfig($key, $value){
        if(is_array($value)){
            $result = [];
            foreach ($value as $k => $v) {
                $result = $result + $this->parseConfig($key . "->" . $k , $v);
            }
            return $result;
        }
        $secret_keys = [
            'key',
            'secret',
            'mysql->password',
            'pgsql->password',
            'sqlsrv->password',
            'access-token',
            'slack-webhook-url',
            'mail->host',
            'mail->port',
            'mail->username',
            'mail->password'
        ];
        foreach ($secret_keys as $item) {
            if(strpos($key, $item) !== false && $value !== ''){
                return [$key => '************'];
            }
        }

        return [$key => $value];
    }

    public function GetConfig(){
        $config = \Config::all();
        $result = [];
        foreach ($config as $key => $value) {
            $result = $result + $this->parseConfig($key, $value);
        }
        return view('admin/config', compact(['result']));
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

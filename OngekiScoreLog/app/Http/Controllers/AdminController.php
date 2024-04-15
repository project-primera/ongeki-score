<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use \Artisan;

class AdminController extends Controller{

    /**
     * '/'にgetリクエストがあったときに呼び出されます。
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function GetIndex(Request $request){
        $message = $request->input('message', null);
        return view('admin/index', compact(['message']));
    }

    /**
     * 受け取った$valueを解析し表示します。
     * 配列を受け取った場合は再帰的に解析します。
     *
     * @param string $key configのkey
     * @param Array|string $value configのvalue
     * @return Array[string] 解析結果
     */
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

    /**
     * '/config'にgetリクエストがあったときに呼び出されます。
     * configに設定された全値を出力し、表示します。
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function GetConfig(){
        $config = \Config::all();
        $result = [];
        foreach ($config as $key => $value) {
            $result = $result + $this->parseConfig($key, $value);
        }
        return view('admin/config', compact(['result']));
    }

    public function GetAggregate(){
        $result = \App\AggregateOverdamage::all();
        return view('admin/aggregate', compact(['result']));
    }

    /**
     * '/config'にgetリクエストがあったときに呼び出されます。
     * パラメータに合致する処理を行い、'/'にリダイレクトします。
     *
     * @param string $type 処理内容
     * @param string $action 処理内容
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function GetApply($type, $action = null){
        $message = "パスが間違っています。";

        if($type === 'all'){
            if($action === "clear"){
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Artisan::call('cache:clear');
                $message = "all:clearを実行しました。本番環境の場合は、必ず生成を行ってください。";
            }else if($action === "cache"){
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
                $message = "all:cacheを実行しました。";
            }
        }else if($type === 'config'){
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

    public function GetGenerateOverDamage(){
        ini_set("max_execution_time", 0);
        \App\AggregateOverdamage::execute();
        return redirect('/admin?message=' . "Generate Over Damage: max. 実行しました！");
    }
}

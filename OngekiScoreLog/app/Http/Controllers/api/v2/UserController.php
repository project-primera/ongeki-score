<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Facades\Slack;
use Carbon\Carbon;
use RuntimeException;

class UserController extends Controller{
    public function GetUpdateStatus(Request $request){
        $result = [];
        $result['id'] = null;
        $result['name'] = null;
        $result['message'] = [];
        $result['hash'] = '';

        if(Auth::user() === null){
            $result['message'][] = "ログイン情報が取得できませんでした。ブックマークレットの再生成をお試しください。";
            return $result;
        }

        $result['id'] = Auth::id();
        $result['name'] = Auth::user()->name;

        if(config('env.is-maintenance-api-user-update')){
            $result['message'][] = "<p>只今新バージョン対応に向けたメンテナンスを行っています。メンテナンス中はスコアデータの登録は行なえません。</p><p>詳細は<a href='https://twitter.com/ongeki_score' target='_blank' style='color:#222'>Twitter@ongeki_score</a>にてお知らせします。</p>";

            $user = Auth::user();
            if($request->input('PlayerData') !== null){
                $name = $request->input('PlayerData')['name'];
            }else{
                $name = "<Unknown>";
            }
            $content = "スコア登録: " . $name . "(" . $user->id . ")\n" . url("/user/" . $user->id);
            $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n\nUser:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\nCookie:\n" . var_export(Cookie::get(), true) . "\n\nRequest:\n" . var_export($request, true);
            $fields = ["IP Address" => \Request::ip(), "User id" => $user->id];
            Slack::Warning($content, $fileContent, $fields, "success");
            return $result;
        }
        $result['message'] = 'ok';
        $result['hash'] = hash('sha256', $result['id'] . '_' . microtime(true));

        \App\UserUpdateStatus::create([
            'user_id' => $result['id'],
            'hash' => $result['hash'],
            'begin_at' => Carbon::now(),
        ]);
        return $result;
    }

    private function setPlayer($data, $dateTime, $uniqueID){
        $userId = Auth::id();
        \App\UserStatus::create([
            'user_id' => $userId,
            'trophy' => $data['trophy'],
            'level' => $data['level'],
            'name' => $data['name'],
            'battle_point' => $data['battle_point'],
            'rating' => $data['rating'],
            'rating_max' => $data['rating_max'],
            'money' => $data['money'],
            'total_money' => $data['total_money'],
            'total_play' => $data['total_play'],
            'comment' => $data['comment'],
            'friend_code' => $data['friend_code'],
            'unique_id' => $uniqueID,
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
        ]);
    }
    }
}

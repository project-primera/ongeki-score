<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Facades\Slack;
use App\UserStatus;
use Carbon\Carbon;
use RuntimeException;

class UserController extends Controller{
    public function GetUpdateStatus(Request $request){
        $result = [];
        $result['id'] = null;
        $result['name'] = null;
        $result['message'] = [];
        $result['hash'] = '';
        $result['updateAt'] = null;

        if(Auth::user() === null){
            $result['message'][] = "ログイン情報が取得できませんでした。ブックマークレットの再生成をお試しください。";
            return $result;
        }

        $result['id'] = Auth::id();
        $result['name'] = Auth::user()->name;

        if(config('env.is-maintenance-api-user-update') && Auth::user()->role < 7){
            $result['message'][] = "<p>只今メンテナンスを行っています。スコアデータの登録は行なえません。</p><p>詳細は<a href='https://twitter.com/ongeki_score' target='_blank' style='color:#222'>Twitter@ongeki_score</a>にてお知らせします。</p>";

            $user = Auth::user();
            if(Auth::user()->name !== null){
                $name = Auth::user()->name;
            }else{
                $name = "<Unknown>";
            }
            $content = "スコア登録: " . $name . "(" . $user->id . ")\n" . url("/user/" . $user->id);
            $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n\nUser:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\nRequest:\n" . var_export($request, true);
            $fields = ["IP Address" => \Request::ip(), "User id" => $user->id];
            \App\Facades\Slack::Warning($content, $fileContent, $fields, "success");
            return $result;
        }
        $userStatus = new UserStatus();
        $status = $userStatus -> getRecentUserData(Auth::id());
        // 最終更新日時を取得
        if(count($status) === 0){
            $result['updateAt'] = '1970-01-01 00:00:01';
        }else {
            $result['updateAt'] = $status[0]->updated_at;
        }
        $result['message'] = 'ok';
        $result['hash'] = hash('sha256', $result['id'] . '_' . microtime(true));

        $generation = (new \App\ScoreData())->getMaxGeneration(Auth::id()) + 1;
        \App\UserUpdateStatus::create([
            'user_id' => $result['id'],
            'hash' => $result['hash'],
            'begin_at' => Carbon::now(),
            'generation' => $generation,
        ]);

        $user = Auth::user();
        $content = "スコア登録: " . "<" . url("/user/" . $user->id) . "|" . $result['name'] . "(" . $user->id . ")>";
        $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n\nUser:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\nRequest:\n" . var_export($request, true);
        $fields = ["IP Address" => \Request::ip(), "User id" => $user->id];
        Slack::Info($content, $fileContent, $fields, "success");

        return $result;
    }

    public function PostUpdate(Request $request){
        $result = [];
        $result['id'] = null;
        $result['isError'] = false;
        $result['message'] = [];

        $hash = $request->input('hash', null);
        $data = $request->input('data', null);
        $methodType = $request->input('methodType', null);

        if(Auth::id() === null){
            $result['message'][] = "認証に失敗しました。";
            $result['isError'] = true;
            return $result;
        }

        if(config('env.is-maintenance-api-user-update') && Auth::user()->role < 7){
            $result['message'][] = "<p>只今メンテナンスを行っています。スコアデータの登録は行なえません。</p><p>詳細は<a href='https://twitter.com/ongeki_score' target='_blank' style='color:#222'>Twitter@ongeki_score</a>にてお知らせします。</p>";

            $user = Auth::user();
            if(Auth::user()->name !== null){
                $name = Auth::user()->name;
            }else{
                $name = "<Unknown>";
            }
            $content = "スコア登録(post): " . $name . "(" . $user->id . ")\n" . url("/user/" . $user->id);
            $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n\nUser:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\nRequest:\n" . var_export($request, true);
            $fields = ["IP Address" => \Request::ip(), "User id" => $user->id];
            \App\Facades\Slack::Warning($content, $fileContent, $fields, "success");
            return $result;
        }

        if($hash === null || $data === null || $methodType === null){
            $result['message'][] = "送信内容を確認できませんでした。";
            $result['isError'] = true;
            return $result;
        }

        $userStatus = \App\UserUpdateStatus::where('hash', $hash)->first();

        if($userStatus === null){
            $result['message'][] = "送信内容が正しくありません。";
            $result['isError'] = true;
            return $result;
        }

        $uniqueID = md5(uniqid(rand(),1));
        \App\UniqueIDForRequest::create([
            'ip_address' => \Request::ip(),
            'unique_id' => $uniqueID,
        ]);

        try {
            if ($methodType === "0") {
                $this->setPlayer($data, $userStatus->begin_at, $uniqueID);
            } else if ($methodType === "1") {
                if (Auth::user()->role >= 7) {
                    $result['message'] = array_merge($result['message'], $this->addMusic($data, $uniqueID));
                }
                $result['message'] = array_merge($result['message'], $this->setScore($data, $userStatus->begin_at, $uniqueID, $userStatus->generation));
            } else if ($methodType === "2") {
                $this->setTrophy($data, $userStatus->begin_at, $uniqueID);
            } else if ($methodType === "3") {
                $this->setCharacterFriendly($data, $userStatus->begin_at, $uniqueID);
            } else if ($methodType === "4") {
                $result['message'] = $this->setRatingRecentMusic($data, $userStatus->begin_at, $uniqueID);
            } else if ($methodType === "5") {
                $this->setPaymentStatus($data, $userStatus->begin_at, $uniqueID);
            } else if ($methodType === "6") {
                $result['message'] = $this->setRatingPlatinumMusic($data, $userStatus->begin_at, $uniqueID);
            } else {
                $result['message'][] = "未知のtypeが渡されました。 type:" . $methodType;
                $result['isError'] = true;
                return $result;
            }

            $result['data'] = $data;
        } catch (\Throwable $th) {
            $result['message'][] = $th->getMessage();
            $result['message'][] = $th->getLine();
            $result['isError'] = true;
        }

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

    private function setScore($data, $dateTime, $uniqueID, $generation){
        $message = [];
        foreach ($data as $key => $value) {
            if($value['difficulty'] !== "0" && $value['difficulty'] !== "1" && $value['difficulty'] !== "2" && $value['difficulty'] !== "3" && $value['difficulty'] !== "10"){
                throw new RuntimeException("未知の難易度が送信されました。(" . $value['difficulty'] . ")");
            }
            if (!array_key_exists('artist', $value)) {
                throw new RuntimeException("アーティスト情報が送信されませんでした。古いブックマークレットが実行されています。ブラウザのキャッシュクリアをお試しください。");
            }

            $temp = null;
            if ($value['artist'] != '') {
                $temp = \App\MusicData::where("title", $value['title'])->where("genre", $value['genre'])->where("artist", $value['artist'])->get();
            } else {
                $v['artist'] = null;
                $temp = \App\MusicData::where("title", $value['title'])->where("genre", $value['genre'])->get();
            }

            // 複数取得、２つ以上あったらエラー出して継続
            if(count($temp) > 1){
                $m = "楽曲が特定できませんでした: " . $value['title'] . " / " . $value['genre'] . " / " . $value['artist'];
                $message[] = $m;

                $content = "同名楽曲が複数存在します。";
                $fields = ["IP Address" => \Request::ip(), "User id" => Auth::id(), "Title" => $value['title'], "Artist" => $value['artist'], "Genre" => $value['genre']];
                \App\Facades\Slack::Notice($content, "", $fields, "success");
                continue;
            } elseif ($temp === null || count($temp) === 0){
                $m = "未知の曲: " . $value['title'] . " / " . $value['genre'] . " / " . $value['artist'];
                $message[] = $m;

                $content = "未知の曲が送信されました。";
                $fields = ["IP Address" => \Request::ip(), "User id" => Auth::id(), "Title" => $value['title'], "Artist" => $value['artist'], "Genre" => $value['genre']];
                \App\Facades\Slack::Notice($content, "", $fields, "success");
                continue;
            }
            $music = $temp[0];


            $recentScore = (new \App\ScoreData())->getRecentGenerationOfScoreData(Auth::id(), $music->id, $value['difficulty'])->getValue();
            $isUpdate = false;

            $full_bell = ($value['full_bell'] === "true" ? 1 : 0);
            $full_combo = ($value['full_combo'] === "true" ? 1 : 0);
            $all_break = ($value['all_break'] === "true" ? 1 : 0);

            $platinum_score = 0;
            // 最悪取れなくてもいいように値チェック
            if(isset($value['platinum_score'])){
                $platinum_score = $value['platinum_score'];
            }

            if((bool)$recentScore === false){
                $isUpdate = true;
            }else{
                if($value['over_damage_high_score'] > $recentScore->over_damage_high_score){
                    $isUpdate = true;
                }else{
                    $value['over_damage_high_score'] = $recentScore->over_damage_high_score;
                }

                if($value['battle_high_score'] > $recentScore->battle_high_score){
                    $isUpdate = true;
                }else{
                    $value['battle_high_score'] = $recentScore->battle_high_score;
                }

                if($value['technical_high_score'] > $recentScore->technical_high_score){
                    $isUpdate = true;
                }else{
                    $value['technical_high_score'] = $recentScore->technical_high_score;
                }

                if($platinum_score > $recentScore->platinum_score){
                    $isUpdate = true;
                }else{
                    $platinum_score = $recentScore->platinum_score;
                }

                if($full_bell > $recentScore->full_bell){
                    $isUpdate = true;
                }else{
                    $full_bell = $recentScore->full_bell;
                }

                if($full_combo > $recentScore->full_combo){
                    $isUpdate = true;
                }else{
                    $full_combo = $recentScore->full_combo;
                }

                if($all_break > $recentScore->all_break){
                    $isUpdate = true;
                }else{
                    $all_break = $recentScore->all_break;
                }
            }
            if($isUpdate){
                \App\ScoreData::create([
                    'user_id' => Auth::id(),
                    'generation' => $generation,
                    'song_id' => $music->id,
                    'difficulty' => $value['difficulty'],
                    'over_damage_high_score' => $value['over_damage_high_score'],
                    'battle_high_score' => $value['battle_high_score'],
                    'technical_high_score' => $value['technical_high_score'],
                    'platinum_score' => $platinum_score,
                    'full_bell' => $full_bell,
                    'full_combo' => $full_combo,
                    'all_break' => $all_break,
                    'unique_id' => $uniqueID,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);
            }
        }
        return $message;
    }

    private function addMusic($data, $uniqueID){
        $message = [];
        $titles = [];

        $currentVersion = config('env.ongeki-version', null);
        if($currentVersion === null){
            throw new RuntimeException("現在のバージョンが取得できませんでした。");
        }

        foreach ($data as $v) {
            // FIXME: 同名楽曲が追加された際、先に既存曲のアーティストをいれないと不具合が起きる
            if ($v['artist'] != '') {
                $musicData = \App\MusicData::where("title", $v['title'])->where("genre", $v['genre'])->where("artist", $v['artist'])->first();
            } else {
                $v['artist'] = null;
                $musicData = \App\MusicData::where("title", $v['title'])->where("genre", $v['genre'])->first();
            }

            if(is_null($musicData)){
                $musicData = new \App\MusicData();
                $musicData->title = $v['title'];
                $message[] = "[楽曲データ追加] " . $v['title'];
                $titles[] = $v['title'];
            }
            $musicData->genre = $v['genre'];
            $musicData->artist = $v['artist'];

            $difficulty = "";
            if($v['difficulty'] === "0"){
                $difficulty = "basic";
                $musicData->basic_level = $v['level'];
            }else if($v['difficulty'] === "1"){
                $difficulty = "advanced";
                $musicData->advanced_level = $v['level'];
            }else if($v['difficulty'] === "2"){
                $difficulty = "expert";
                $musicData->expert_level = $v['level'];
            }else if($v['difficulty'] === "3"){
                $difficulty = "master";
                $musicData->master_level = $v['level'];
            }else if($v['difficulty'] === "10"){
                $difficulty = "lunatic";
                $musicData->lunatic_level = $v['level'];
            }else{
                throw new RuntimeException("未知の難易度が送信されました。(" . $v['difficulty'] . ")");
            }
            if($difficulty === "lunatic" && is_null($musicData->lunatic_added_version)){
                $musicData->lunatic_added_version = $currentVersion;
            }else if($difficulty !== "lunatic" && is_null($musicData->normal_added_version)){
                $musicData->normal_added_version = $currentVersion;
            }
            $musicData->unique_id = $uniqueID;
            $musicData->save();
        }
        if(count($titles) !== 0){
            $message[] = "楽曲情報の追加を行いました。";
        }

        return $message;
    }

    private $trophyGrade = ["Normal" => 0, "Silver" => 1, "Gold" => 2, "Platinum" => 3, "Rainbow" => 4];
    private function setTrophy($data, $dateTime, $uniqueID){
        foreach ($data as $k => $v) {
            $record = \App\UserTrophy::where([
                ['user_id', '=', Auth::id()],
                ['name', '=', $v['name']],
            ])->get();
            if(count($record) > 0){
                continue;
            }

            \App\UserTrophy::create([
                'user_id' => Auth::id(),
                'grade' => $this->trophyGrade[$v['rank']],
                'name' => $v['name'],
                'detail' => $v['detail'],
                'unique_id' => $uniqueID,
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ]);
        }
    }

    private function setCharacterFriendly($data, $dateTime, $uniqueID){
        foreach ($data['friendly'] as $key => $value) {
            \App\CharacterFriendly::create([
                'user_id' => Auth::id(),
                'character_id' => $key,
                'value' => $value,
                'unique_id' => $uniqueID,
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ]);
        }
    }

    private function setRatingRecentMusic($data, $dateTime, $uniqueID){
        $message = [];
        \App\RatingRecentMusic::where('user_id', Auth::id())->delete();
        foreach ($data['ratingRecentMusicObject'] as $key => $value) {
            $genre = null;
            $artist = null;
            if (!array_key_exists('genre', $value) || !array_key_exists('artist', $value)) {
                $message = ["古いブックマークレットが実行されている可能性があります。問題が発生する場合はブラウザのキャッシュクリアをお試しください。"];
            }

            if ($value['genre'] !== "") {
                $genre = $value['genre'];
            }
            if ($value['artist'] !== "") {
                $artist = $value['artist'];
            }

            \App\RatingRecentMusic::create([
                'user_id' => Auth::id(),
                'rank' => $key,
                'title' => $value['title'],
                'artist' => $artist,
                'genre' => $genre,
                'difficulty' => $value['difficulty'],
                'technical_score' => $value['technicalScore'],
                'unique_id' => $uniqueID,
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ]);
        }
        return $message;
    }

    private function setRatingPlatinumMusic($data, $dateTime, $uniqueID){
        $message = [];
        \App\RatingPlatinumMusic::where('user_id', Auth::id())->delete();
        foreach ($data['ratingPlatinumMusicObject'] as $key => $value) {
            $genre = null;
            $artist = null;

            if ($value['genre'] !== "") {
                $genre = $value['genre'];
            }
            if ($value['artist'] !== "") {
                $artist = $value['artist'];
            }

            \App\RatingPlatinumMusic::create([
                'user_id' => Auth::id(),
                'rank' => $key,
                'title' => $value['title'],
                'artist' => $artist,
                'genre' => $genre,
                'difficulty' => $value['difficulty'],
                'platinum_score' => $value['platinumScore'],
                'star' => $value['star'],
                'unique_id' => $uniqueID,
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ]);
        }
        return $message;
    }

    private function setPaymentStatus($data, $dateTime, $uniqueID){
        \App\UserInformation ::updateOrCreate(
            ['user_id' => Auth::id()], [
                'is_standard_plan' => $data['isStandardPlan'] === "true" ? 1 : 0,
                'is_premium_plan' => $data['isPremiumPlan'] === "true" ? 1 : 0,
                'unique_id' => $uniqueID,
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ]
        );
    }
}

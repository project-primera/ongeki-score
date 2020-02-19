<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

use App\UserStatus;
use App\CharacterFriendly;
use App\RatingRecentMusic;
use App\UserTrophy;
use App\UniqueIDForRequest;
use App\MusicData;
use App\ScoreData;
use App\AdminTweet;
use App\Facades\Slack;

use Log;

class BookmarkletAccessController extends Controller
{

    public function postUserUpdate(Request $request)
    {
        if(config('env.is-maintenance-api-user-update')){
            $message['info'] = "";
            $message['result'] = "<p>只今新バージョン対応に向けたメンテナンスを行っています。メンテナンス中はスコアデータの登録は行なえません。</p><p>詳細は<a href='https://twitter.com/ongeki_score' target='_blank' style='color:#222'>Twitter@ongeki_score</a>にてお知らせします。</p>";
            $message['id'] = Auth::id();

            $user = Auth::user();
            if(!is_null($request->input('PlayerData'))){
                $name = $request->input('PlayerData')['name'];
            }else{
                $name = "<Unknown>";
            }
            $content = "スコア登録: " . $name . "(" . $user->id . ")\n" . url("/user/" . $user->id);
            $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n\nUser:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\nCookie:\n" . var_export(Cookie::get(), true) . "\n\nRequest:\n" . var_export($request, true);
            $fields = ["IP Address" => \Request::ip(), "User id" => $user->id];
            Slack::Warning($content, $fileContent, $fields, "success");
            return $message;
        }

        // 0 -> origin
        // 1 -> origin plus
        // 2 -> summer
        $currentVersion = config('env.ongeki-version');
        try{
            {
                if(!is_null($request->input('PlayerData'))){
                    $name = $request->input('PlayerData')['name'];
                }else{
                    $name = "<Unknown>";
                }
                $user = Auth::user();
                $content = "スコア登録: " . $name . "(" . $user->id . ")\n" . url("/user/" . $user->id);
                $fileContent = "ip: " . \Request::ip() . "\nUser agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n\nUser:\nid: " . $user->id . "\nemail: " . $user->email . "\nrole: " . $user->role . "\n\nCookie:\n" . var_export(Cookie::get(), true) . "\n\nRequest:\n" . var_export($request, true);
                $fields = ["IP Address" => \Request::ip(), "User id" => $user->id];
                Slack::Info($content, $fileContent, $fields, "success");
            }

            $message['info'] = "";
            $message['result'] = "";
            $message['id'] = Auth::id();

            $uniqueID = md5(uniqid(rand(),1));
            $uniqueIDForRequest = new UniqueIDForRequest();
            $uniqueIDForRequest->ip_address = \Request::ip();
            $uniqueIDForRequest->unique_id =$uniqueID;
            $uniqueIDForRequest->save();

            if(!is_null($request->input('PlayerData'))){
                $userStatus = new UserStatus();
                $userStatus->user_id = Auth::id();
                if(is_null($request->input('PlayerData')['trophy'])){
                    $userStatus->trophy = "";
                }else{
                    $userStatus->trophy = $request->input('PlayerData')['trophy'];
                }
                $userStatus->level = $request->input('PlayerData')['level'];
                $userStatus->name = $request->input('PlayerData')['name'];
                $userStatus->battle_point = $request->input('PlayerData')['battle_point'];
                $userStatus->rating = $request->input('PlayerData')['rating'];
                $userStatus->rating_max = $request->input('PlayerData')['rating_max'];
                $userStatus->money = $request->input('PlayerData')['money'];
                $userStatus->total_money = $request->input('PlayerData')['total_money'];
                $userStatus->total_play = $request->input('PlayerData')['total_play'];
                $userStatus->comment = $request->input('PlayerData')['comment'];
                $userStatus->friend_code = $request->input('PlayerData')['friend_code'];
                $userStatus->unique_id =$uniqueID;
                $userStatus->save();
            }else{
                throw new Exception("キャラクター情報が取得できませんでした。");
            }


            if(!is_null($request->input('CharacterFriendlyData'))){
                foreach ($request->input('CharacterFriendlyData')['friendly'] as $key => $value) {
                    $characterFriendly = new CharacterFriendly();
                    $characterFriendly->user_id = Auth::id();
                    $characterFriendly->character_id = $key;
                    $characterFriendly->value = $value;
                    $characterFriendly->unique_id =$uniqueID;
                    $characterFriendly->save();
                }
            }else{
                $message['info'] .= "キャラクターの親密度情報が取得できませんでした。<br>";
            }

            if(!is_null($request->input('RatingRecentMusicData'))){
                DB::table('rating_recent_musics')->where('user_id', '=', Auth::id())->delete();
                foreach ($request->input('RatingRecentMusicData')['ratingRecentMusicObject'] as $key => $value) {
                    $ratingRecentMusic = new RatingRecentMusic();
                    $ratingRecentMusic->user_id = Auth::id();
                    $ratingRecentMusic->rank = $key;
                    $ratingRecentMusic->title = $value['title'];
                    $ratingRecentMusic->difficulty = $value['difficulty'];
                    $ratingRecentMusic->technical_score = $value['technicalScore'];
                    $ratingRecentMusic->unique_id =$uniqueID;
                    $ratingRecentMusic->save();

                    // 取得ができればOngekiNetプレミアムプランと見なす
                    if(Auth::user()->role < 2){
                        Auth::user()->role = 2;
                        Auth::user()->save();
                    }
                }
            }else{
                $message['info'] .= "レーティング対象曲情報が取得できませんでした。<br>";
            }

            if(!is_null($request->input('TrophyData'))){
                $trophyGrade = ["normalTrophyInfos" => 0,"silverTrophyInfos" => 1, "goldTrophyInfos" => 2, "platinumTrophyInfo" => 3, "rainbowTrophyInfo" => 4];
                foreach ($request->input('TrophyData') as $key => $value) {
                    foreach ($value as $k => $v) {
                        $record = DB::table('user_trophies')->where([
                            ['user_id', '=', Auth::id()],
                            ['name', '=', $v['name']],
                        ])->get();
                        if(count($record) > 0){
                            continue;
                        }

                        $userTrophy = new UserTrophy();
                        $userTrophy->user_id = Auth::id();
                        $userTrophy->grade = $trophyGrade[$key];
                        $userTrophy->name = $v['name'];
                        $userTrophy->detail = $v['detail'];
                        $userTrophy->unique_id =$uniqueID;
                        $userTrophy->save();
                    }
                }
            }else{
                $message['info'] .= "称号獲得状況が取得できませんでした。<br>";
            }


            if(!is_null($request->input('ScoreData'))){

                if(Auth::user()->role >= 7){
                    $titles = [];

                    $difficultyArrayKey = [
                        "basicSongInfos" => "basic",
                        "advancedSongInfos" => "advanced",
                        "expertSongInfos" => "expert",
                        "masterSongInfos" => "master",
                        "lunaticSongInfos" => "lunatic",
                    ];
                    foreach ($difficultyArrayKey as $key => $value) {
                        foreach ($request->input('ScoreData')[$key] as $k => $v) {
                            $userStatus = MusicData::where("title", $v['title'])->first();
                            if(is_null($userStatus)){
                                $userStatus = new MusicData();
                                $userStatus->title = $v['title'];
                                $message['info'] .=  "[追加] ". $v['title'] . "<br>";
                                $titles[] = $v['title'];
                            }
                            $def = $value . "_level";
                            $userStatus->$def = $v['level'];
                            $userStatus->genre = $v['genre'];
                            if($value === "lunatic" && is_null($userStatus->lunatic_added_version)){
                                $userStatus->lunatic_added_version = $currentVersion;
                            }else if($value !== "lunatic" && is_null($userStatus->normal_added_version)){
                                $userStatus->normal_added_version = $currentVersion;
                            }
                            $userStatus->unique_id = $uniqueID;
                            $userStatus->save();
                        }
                    }
                    if(count($titles) !== 0){
                        $message['info'] .= "楽曲情報の追加を行いました。<br>";
                        (new AdminTweet())->tweetMusicUpdate($titles);
                    }else{
                        $message['info'] .= "追加楽曲はありませんでした。<br>";
                    }
                }


                $difficultyArrayKey = [
                    "basicSongInfos" => "basic",
                    "advancedSongInfos" => "advanced",
                    "expertSongInfos" => "expert",
                    "masterSongInfos" => "master",
                    "lunaticSongInfos" => "lunatic",
                ];
                $difficultyValue = [
                    "basicSongInfos" => 0,
                    "advancedSongInfos" => 1,
                    "expertSongInfos" => 2,
                    "masterSongInfos" => 3,
                    "lunaticSongInfos" => 10,
                ];
                $generation = (new ScoreData())->getMaxGeneration(Auth::id()) + 1;
                foreach ($difficultyArrayKey as $key => $value) {
                    foreach ($request->input('ScoreData')[$key] as $k => $v) {
                        $userStatus = MusicData::where("title", "=", $v['title'])->first();
                        if(!is_null($userStatus)){
                            $scoreData = new ScoreData();
                            $recentSong = $scoreData->getRecentGenerationOfScoreData(Auth::id(), $userStatus->id, $difficultyValue[$key])->getValue();
                            $scoreData->generation = $generation;
                            $scoreData->user_id = Auth::id();
                            $scoreData->song_id = $userStatus->id;
                            $scoreData->difficulty = $difficultyValue[$key];
                            $scoreData->over_damage_high_score = $v['over_damage_high_score'];
                            $scoreData->battle_high_score = $v['battle_high_score'];
                            $scoreData->technical_high_score = $v['technical_high_score'];
                            $scoreData->full_bell = $v['full_bell'] === "true" ? 1 : 0;
                            $scoreData->full_combo = $v['full_combo'] === "true" ? 1 : 0;
                            $scoreData->all_break = $v['all_break'] === "true" ? 1 : 0;
                            $scoreData->unique_id = $uniqueID;


                            $isUpdate = false;

                            if((bool)$recentSong === false){
                                $isUpdate = true;

                            }else{
                                if($scoreData->over_damage_high_score > $recentSong->over_damage_high_score){
                                    $isUpdate = true;
                                }else{
                                    $scoreData->over_damage_high_score = $recentSong->over_damage_high_score;
                                }

                                if($scoreData->battle_high_score > $recentSong->battle_high_score){
                                    $isUpdate = true;
                                }else{
                                    $scoreData->battle_high_score = $recentSong->battle_high_score;
                                }

                                if($scoreData->technical_high_score > $recentSong->technical_high_score){
                                    $isUpdate = true;
                                }else{
                                    $scoreData->technical_high_score = $recentSong->technical_high_score;
                                }

                                if($scoreData->technical_high_score > $recentSong->technical_high_score){
                                    $isUpdate = true;
                                }else{
                                    $scoreData->technical_high_score = $recentSong->technical_high_score;
                                }

                                if($scoreData->full_bell > $recentSong->full_bell){
                                    $isUpdate = true;
                                }else{
                                    $scoreData->full_bell = $recentSong->full_bell;
                                }

                                if($scoreData->full_combo > $recentSong->full_combo){
                                    $isUpdate = true;
                                }else{
                                    $scoreData->full_combo = $recentSong->full_combo;
                                }

                                if($scoreData->all_break > $recentSong->all_break){
                                    $isUpdate = true;
                                }else{
                                    $scoreData->all_break = $recentSong->all_break;
                                }
                            }
                            if($isUpdate){
                                $scoreData->save();
                            }
                        }
                    }
                }
                // 取得ができればOngekiNetスタンダードプランと見なす
                if(Auth::user()->role < 1){
                    Auth::user()->role = 1;
                    Auth::user()->save();
                }
                $message['result'] .= "スコア登録に成功しました！<br>";
            }else{
                $message['result'] .= "スコアデータの取得が出来ませんでした。<br>";
            }

            return $message;
        }catch(\PDOException $e){
            Log::error($e);
            Slack::Error($e->getMessage(), $e, $fields, "error");
            return "error";
        }catch(\Exception $e){
            Log::error($e);
            Slack::Error($e->getMessage(), $e, $fields, "error");
            return "error";
        }
    }
}

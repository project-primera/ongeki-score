<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserStatus;
use App\ScoreData;
use App\ApplicationVersion;
use App\ExternalServiceCoordination;

class ViewUserProgressController extends Controller
{
    function getIndex(int $id, int $generation = null){
        function shapingKeys($array){
            $ret = [];
            foreach ($array as $value) {
                $ret[$value->song_id][$value->difficulty] = $value;
            }
            return $ret;
        }

        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);

        if(count($status) === 0){
            if(is_null(User::where('id' ,$id)->first())){
                abort(404);
            }else{
                return view("user_error", ['message' => '<p>このユーザーはOngekiScoreLogに登録していますが、オンゲキNETからスコア取得を行っていません。(UserID: ' . $id . ')</p><p>スコアの取得方法は<a href="/howto">こちら</a>をお読みください。</p>']);
            }
        }

        // 設計がゆるふわだったときのユーザーにgeneration 0を持っているユーザーが居るので補正
        $fixedGeneration = 0;
        $gen = (new ScoreData)->getAllGenerationUserScore($id)->getValue();
        if($gen[0]->generation == 0){
            $fixedGeneration = 1;
        }
        
        $prevGeneration = (new ScoreData)->getMaxGeneration($id) - 1 + $fixedGeneration;
        if($generation === null){
            $generation = $prevGeneration;
        }else if($generation >= $prevGeneration){
            return redirect("/user/" . $id . "/progress");
        }

        $sidemark = null;
        if(Auth::check() && \Auth::user()->id == $id){
            $sidemark = "sidemark_mypage_progress";
        }

        $version = (new ApplicationVersion())->getLatestVersion();
        $version = isset($version[0]->tag_name) ? $version[0]->tag_name : "";

        $progress = [];
        $isLoggedIn = false;
        $isTwitterEnabled = false;
        $twitterScreenName = '';

        $user = \Auth::user();
        if($user !== null){
            $isLoggedIn = true;
            $external = new ExternalServiceCoordination();
            $ret = $external->get($user->id);
            if(count($ret) !== 0){
                // Twitterの連携情報を実際に使ってみて確認
                $twitter = $external->getTwitter($ret[0]->twitter_access_token, $ret[0]->twitter_access_token_secret);
                if($twitter !== null){
                    $isTwitterEnabled = true;
                    $twitterScreenName = $twitter->screen_name;
                }
            }
        }

        $score = [
            'new' =>[
                'Basic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Advanced' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Expert' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Master' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Lunatic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
            ],
            'old' =>[
                'Basic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Advanced' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Expert' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Master' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
                'Lunatic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                ],
            ]
        ];
        $difficultyToStr = [
            0 => 'Basic',
            1 => 'Advanced',
            2 => 'Expert',
            3 => 'Master',
            10 => 'Lunatic',
        ];


        
        $display['url'] = "/user/" . $id . "/progress";
        $display['select'] = [];
        $display['select'][0]["value"] = "初回登録";
        $display['select'][0]["selected"] = "";
        $display['select'][0]["disabled"] = "";

        foreach ($gen as $key => $value) {
            if($value == end($gen)){
                $display['select_last']["value"] = $value->updated_at;
            }else{
                $display['select'][$value->generation + $fixedGeneration]["value"] = $value->updated_at;
                $display['select'][$value->generation + $fixedGeneration]["selected"] = "";
                $display['select'][$value->generation + $fixedGeneration]["disabled"] = "";
                if($value->generation + $fixedGeneration == $generation){
                    $display['select'][$value->generation + $fixedGeneration]["selected"] = " selected";
                }
            }
        }

        $old = shapingKeys((new ScoreData)->getSpecifiedGenerationUserScore($id, $generation - $fixedGeneration)->addDetailedData()->getValue());
        $new = shapingKeys((new ScoreData)->getRecentUserScore($id)->addMusicData()->addDetailedData()->getValue());
        
        $date["new"] = date("Y/m/d H:i", strtotime($display['select_last']["value"]));
        if($date["new"] === date("Y/m/d H:i", strtotime(0))){
            $date["new"] = "N/A"; 
        }
        if(array_key_exists($generation - 1, $display['select'])){
            $date["old"] = date("Y/m/d H:i", strtotime($display['select'][$generation]["value"]));
        }else{
            $date["old"] = "初回登録";
        }

        foreach ($new as $music => $temp) {
            foreach ($temp as $difficulty => $value) {
                $score['new'][$difficultyToStr[$difficulty]]['battle_high_score'] += $value->battle_high_score;
                $score['new'][$difficultyToStr[$difficulty]]['technical_high_score'] += $value->technical_high_score;

                if(!array_key_exists($music, $old) || !array_key_exists($difficulty, $old[$music])){
                    if($value->battle_high_score !== 0){
                        // not implemented → played
                        // echo "[new] " . $value->title . " / " . $value->difficulty_str . "<br>";
                        $progress[$music][$difficulty]["new"] = $value;
                        $progress[$music][$difficulty]["difference"]['battle_high_score'] = "+" . number_format($value->battle_high_score);
                        $progress[$music][$difficulty]["difference"]['technical_high_score'] = "+" . number_format($value->technical_high_score);
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                        $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = "N" . " → " . $value->technical_high_score_rank;
                        $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = "update";
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = "不可" . " → " . $value->over_damage_high_score_rank;
                        $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = "update";
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                        $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = "not-light";
                        $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = "not-light";
                        $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = "not-light";
                        $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                        $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                        $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";
                        
                    }
                }else{
                    $score['old'][$difficultyToStr[$difficulty]]['battle_high_score'] += $old[$music][$difficulty]->battle_high_score;
                    $score['old'][$difficultyToStr[$difficulty]]['technical_high_score'] += $old[$music][$difficulty]->technical_high_score;

                    if($old[$music][$difficulty]->over_damage_high_score < $value->over_damage_high_score
                    || $old[$music][$difficulty]->battle_high_score < $value->battle_high_score
                    || $old[$music][$difficulty]->technical_high_score < $value->technical_high_score
                    || $old[$music][$difficulty]->full_bell < $value->full_bell
                    || $old[$music][$difficulty]->full_combo < $value->full_combo
                    || $old[$music][$difficulty]->all_break < $value->all_break){
                        if($old[$music][$difficulty]->battle_high_score === 0){
                            // noplay → played
                            // echo "[new*] " . $value->title . " / " . $value->difficulty_str . "<br>";
                            $progress[$music][$difficulty]["new"] = $value;
                            $progress[$music][$difficulty]["difference"]['battle_high_score'] = "+" . number_format($value->battle_high_score);
                            $progress[$music][$difficulty]["difference"]['technical_high_score'] = "+" . number_format($value->technical_high_score);
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                            $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = "N" . " → " . $value->technical_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = "update";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = "N" . " → " . $value->over_damage_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = "update";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";

                        }else{
                            // played → played
                            // echo "[update] " . $value->title . " / " . $value->difficulty_str . "<br>";
                            $progress[$music][$difficulty]["difference"]['battle_high_score'] = ($value->battle_high_score - $old[$music][$difficulty]->battle_high_score) != 0 ? "+" . number_format($value->battle_high_score - $old[$music][$difficulty]->battle_high_score) : "";
                            $progress[$music][$difficulty]["difference"]['technical_high_score'] = ($value->technical_high_score - $old[$music][$difficulty]->technical_high_score) != 0 ? "+" . number_format($value->technical_high_score - $old[$music][$difficulty]->technical_high_score) : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) != 0 ? "+" . ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) . "%" : "";
                            $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = $old[$music][$difficulty]->technical_high_score_rank . " → " . $value->technical_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = ($old[$music][$difficulty]->technical_high_score_rank != $value->technical_high_score_rank) ? "update" : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = $old[$music][$difficulty]->over_damage_high_score_rank . " → " . $value->over_damage_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = ($old[$music][$difficulty]->over_damage_high_score_rank != $value->over_damage_high_score_rank) ? "update" : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) != 0 ? "+" . (floor(($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) * 100) / 100) . "%" : "";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = $old[$music][$difficulty]->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = $old[$music][$difficulty]->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = $old[$music][$difficulty]->all_break ? "all-break" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";
                            
                            $progress[$music][$difficulty]["new"] = $value;
                        }
                    }
                }
            }
        }
        $score['new']['Total']['battle_high_score'] = $score['new']['Basic']['battle_high_score'] + $score['new']['Advanced']['battle_high_score'] + $score['new']['Expert']['battle_high_score'] + $score['new']['Master']['battle_high_score'] + $score['new']['Lunatic']['battle_high_score'];
        $score['new']['Total']['technical_high_score'] = $score['new']['Basic']['technical_high_score'] + $score['new']['Advanced']['technical_high_score'] + $score['new']['Expert']['technical_high_score'] + $score['new']['Master']['technical_high_score'] + $score['new']['Lunatic']['technical_high_score'];

        $score['old']['Total']['battle_high_score'] = $score['old']['Basic']['battle_high_score'] + $score['old']['Advanced']['battle_high_score'] + $score['old']['Expert']['battle_high_score'] + $score['old']['Master']['battle_high_score'] + $score['old']['Lunatic']['battle_high_score'];
        $score['old']['Total']['technical_high_score'] = $score['old']['Basic']['technical_high_score'] + $score['old']['Advanced']['technical_high_score'] + $score['old']['Expert']['technical_high_score'] + $score['old']['Master']['technical_high_score'] + $score['old']['Lunatic']['technical_high_score'];

        $score['difference'] = [
            'Total' => [
                'battle_high_score' => $score['new']['Total']['battle_high_score'] - $score['old']['Total']['battle_high_score'],
                'technical_high_score' => $score['new']['Total']['technical_high_score'] - $score['old']['Total']['technical_high_score'],
            ],
            'Basic' => [
                'battle_high_score' => $score['new']['Basic']['battle_high_score'] - $score['old']['Basic']['battle_high_score'],
                'technical_high_score' => $score['new']['Basic']['technical_high_score'] - $score['old']['Basic']['technical_high_score'],
            ],
            'Advanced' => [
                'battle_high_score' => $score['new']['Advanced']['battle_high_score'] - $score['old']['Advanced']['battle_high_score'],
                'technical_high_score' => $score['new']['Advanced']['technical_high_score'] - $score['old']['Advanced']['technical_high_score'],
            ],
            'Expert' => [
                'battle_high_score' => $score['new']['Expert']['battle_high_score'] - $score['old']['Expert']['battle_high_score'],
                'technical_high_score' => $score['new']['Expert']['technical_high_score'] - $score['old']['Expert']['technical_high_score'],
            ],
            'Master' => [
                'battle_high_score' => $score['new']['Master']['battle_high_score'] - $score['old']['Master']['battle_high_score'],
                'technical_high_score' => $score['new']['Master']['technical_high_score'] - $score['old']['Master']['technical_high_score'],
            ],
            'Lunatic' => [
                'battle_high_score' => $score['new']['Lunatic']['battle_high_score'] - $score['old']['Lunatic']['battle_high_score'],
                'technical_high_score' => $score['new']['Lunatic']['technical_high_score'] - $score['old']['Lunatic']['technical_high_score'],
            ]
        ];

        return view('user_progress', compact('status', 'progress', 'date', 'score', 'version', 'display', 'id', 'sidemark', 'isLoggedIn', 'isTwitterEnabled', 'twitterScreenName'));
    }
}

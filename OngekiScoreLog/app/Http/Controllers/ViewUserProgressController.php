<?php

namespace App\Http\Controllers;

use App\Facades\OngekiUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserStatus;
use App\ScoreData;
use App\ApplicationVersion;
use App\ExternalServiceCoordination;

class ViewUserProgressController extends Controller
{
    function getIndex(Request $request, int $id, int $generation = null){
        function shapingKeys($array){
            $ret = [];
            foreach ($array as $value) {
                $ret[$value->song_id][$value->difficulty] = $value;
            }
            return $ret;
        }

        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);
        $gen = (new ScoreData)->getAllGenerationUserScore($id)->getValue();

        if(count($status) === 0 || count($gen) === 0){
            if(is_null(User::where('id' ,$id)->first())){
                abort(404);
            }else{
                return view("user_error", ['message' => '<p>このユーザーはOngekiScoreLogに登録していますが、オンゲキNETからスコア取得を行っていません。(UserID: ' . $id . ')</p><p>スコアの取得方法は<a href="/howto">こちら</a>をお読みください。</p>']);
            }
        }

        // 設計がゆるふわだったときのユーザーにgeneration 0を持っているユーザーが居るので補正
        $fixedGeneration = 0;
        if($gen[0]->generation == 0){
            $fixedGeneration = 1;
        }

        $url = "/user/" . $id . "/progress/";
        if($generation !== null){
            $url = "/user/" . $id . "/progress/" . $generation;
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

        $filter = "";
        if($request->filter === "bs"){
            $filter = "Battle Scoreの更新を表示中";
        }
        if($request->filter === "ts"){
            $filter = "レーティング関連（Technical Score, Platinum Score, クリアランプ）の更新を表示中";
        }
        if($request->filter === "od"){
            $filter = "Over Damageの更新を表示中";
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
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Advanced' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Expert' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Master' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Lunatic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
            ],
            'old' =>[
                'Basic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Advanced' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Expert' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Master' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
                ],
                'Lunatic' => [
                    'battle_high_score' => 0,
                    'technical_high_score' => 0,
                    'platinum_score' => 0,
                    'over_damage_high_score' => 0,
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
                $score['new'][$difficultyToStr[$difficulty]]['platinum_score'] += $value->platinum_score;
                $score['new'][$difficultyToStr[$difficulty]]['over_damage_high_score'] += $value->over_damage_high_score;
                $newNormalRating = OngekiUtility::RateValueFromTitle($value->title, $value->difficulty, $value->technical_high_score, $value->lampForRating, $value->genre, $value->artist);
                if(!array_key_exists($music, $old) || !array_key_exists($difficulty, $old[$music])){
                    if($value->battle_high_score !== 0){
                        // not implemented → played
                        // echo "[new] " . $value->title . " / " . $value->difficulty_str . "<br>";
                        $progress[$music][$difficulty]["new"] = $value;
                        $progress[$music][$difficulty]["ratings"]["normal_new"] = $newNormalRating;
                        $progress[$music][$difficulty]["difference"]['battle_high_score'] = "+" . number_format($value->battle_high_score);
                        $progress[$music][$difficulty]["difference"]['technical_high_score'] = "+" . number_format($value->technical_high_score);
                        $progress[$music][$difficulty]["difference"]['platinum_score'] = "+" . number_format($value->platinum_score);
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                        $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = "N" . " → " . $value->technical_high_score_rank;
                        $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = "update";
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = "不可" . " → " . $value->over_damage_high_score_rank;
                        $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = "update";
                        $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                        $progress[$music][$difficulty]["difference"]['normal_rating'] = "+" . sprintf("%.3f", $newNormalRating);
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
                    $score['old'][$difficultyToStr[$difficulty]]['platinum_score'] += $old[$music][$difficulty]->platinum_score;
                    $score['old'][$difficultyToStr[$difficulty]]['over_damage_high_score'] +=  $old[$music][$difficulty]->over_damage_high_score;

                    if($old[$music][$difficulty]->over_damage_high_score < $value->over_damage_high_score
                    || $old[$music][$difficulty]->battle_high_score < $value->battle_high_score
                    || $old[$music][$difficulty]->technical_high_score < $value->technical_high_score
                    || $old[$music][$difficulty]->platinum_score < $value->platinum_score
                    || $old[$music][$difficulty]->full_bell < $value->full_bell
                    || $old[$music][$difficulty]->full_combo < $value->full_combo
                    || $old[$music][$difficulty]->all_break < $value->all_break){
                        if($old[$music][$difficulty]->battle_high_score === 0){
                            // noplay → played
                            // echo "[new*] " . $value->title . " / " . $value->difficulty_str . "<br>";
                            $progress[$music][$difficulty]["new"] = $value;
                            $progress[$music][$difficulty]["ratings"]["normal_new"] = $newNormalRating;
                            $progress[$music][$difficulty]["difference"]['battle_high_score'] = "+" . number_format($value->battle_high_score);
                            $progress[$music][$difficulty]["difference"]['technical_high_score'] = "+" . number_format($value->technical_high_score);
                            $progress[$music][$difficulty]["difference"]['platinum_score'] = "+" . number_format($value->platinum_score);
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                            $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = "N" . " → " . $value->technical_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = "update";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = "N" . " → " . $value->over_damage_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = "update";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = "+" . ($value->over_damage_high_score) . "%";
                            $progress[$music][$difficulty]["difference"]['normal_rating'] = "+" . sprintf("%.3f",$newNormalRating);
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";

                        }else{
                            // played → played
                            // echo "[update] " . $value->title . " / " . $value->difficulty_str . "<br>";

                            if($request->filter === "bs"){
                                if($old[$music][$difficulty]->battle_high_score >= $value->battle_high_score){
                                    continue;
                                }
                            }
                            if($request->filter === "ts"){
                                if($old[$music][$difficulty]->technical_high_score >= $value->technical_high_score
                                && $old[$music][$difficulty]->platinum_score >= $value->platinum_score
                                && $old[$music][$difficulty]->full_bell >= $value->full_bell
                                && $old[$music][$difficulty]->full_combo >= $value->full_combo
                                && $old[$music][$difficulty]->all_break >= $value->all_break){
                                    continue;
                                }
                            }
                            if($request->filter === "od"){
                                if($old[$music][$difficulty]->over_damage_high_score >= $value->over_damage_high_score){
                                    continue;
                                }
                            }
                            // Rating計算はできるだけ少なくしたいので先に計算しておく。
                            // ViewUserRatingController.php から引用。
                            $oldLampForRating = "";
                            if ($old[$music][$difficulty]->technical_high_score == 1010000){
                                if ($old[$music][$difficulty]->full_bell == 1) {
                                    $oldLampForRating = "FB/AB+";
                                } else {
                                    $oldLampForRating = "AB+";
                                }
                            } elseif ($old[$music][$difficulty]->all_break == 1) {
                                if ($old[$music][$difficulty]->full_bell == 1) {
                                    $oldLampForRating = "FB/AB";
                                } else {
                                    $oldLampForRating = "AB";
                                }
                            } elseif ($old[$music][$difficulty]->full_combo == 1) {
                                if ($old[$music][$difficulty]->full_bell == 1) {
                                    $oldLampForRating = "FB/FC";
                                } else {
                                    $oldLampForRating = "FC";
                                }
                            } else {
                                if ($old[$music][$difficulty]->full_bell == 1) {
                                    $oldLampForRating = "FB";
                                }
                            }
                            $oldNormalRating = OngekiUtility::RateValueFromTitle($value->title, $old[$music][$difficulty]->difficulty, $old[$music][$difficulty]->technical_high_score, $oldLampForRating, $value->genre, $value->artist);

                            $progress[$music][$difficulty]["difference"]['battle_high_score'] = ($value->battle_high_score - $old[$music][$difficulty]->battle_high_score) != 0 ? "+" . number_format($value->battle_high_score - $old[$music][$difficulty]->battle_high_score) : "";
                            $progress[$music][$difficulty]["difference"]['technical_high_score'] = ($value->technical_high_score - $old[$music][$difficulty]->technical_high_score) != 0 ? "+" . number_format($value->technical_high_score - $old[$music][$difficulty]->technical_high_score) : "";
                            $progress[$music][$difficulty]["difference"]['platinum_score'] = ($value->platinum_score - $old[$music][$difficulty]->platinum_score) != 0 ? "+" . number_format($value->platinum_score - $old[$music][$difficulty]->platinum_score) : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) != 0 ? "+" . ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) . "%" : "";
                            $progress[$music][$difficulty]["difference"]['technical_high_score_rank'] = $old[$music][$difficulty]->technical_high_score_rank . " → " . $value->technical_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_technical_high_score_rank'] = ($old[$music][$difficulty]->technical_high_score_rank != $value->technical_high_score_rank) ? "update" : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score_rank'] = $old[$music][$difficulty]->over_damage_high_score_rank . " → " . $value->over_damage_high_score_rank;
                            $progress[$music][$difficulty]["difference"]['is_update_over_damage_high_score_rank'] = ($old[$music][$difficulty]->over_damage_high_score_rank != $value->over_damage_high_score_rank) ? "update" : "";
                            $progress[$music][$difficulty]["difference"]['over_damage_high_score'] = ($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) != 0 ? "+" . (floor(($value->over_damage_high_score - $old[$music][$difficulty]->over_damage_high_score) * 100) / 100) . "%" : "";
                            $progress[$music][$difficulty]["difference"]['normal_rating'] = ($newNormalRating - $oldNormalRating) != 0 ? "+" . sprintf("%.3f",($newNormalRating - $oldNormalRating)) : "";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fb'] = $old[$music][$difficulty]->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-fc'] = $old[$music][$difficulty]->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['old-lamp-is-ab'] = $old[$music][$difficulty]->all_break ? "all-break" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fb'] = $value->full_bell ? "full-bell" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-fc'] = $value->full_combo ? "full-combo" : "not-light";
                            $progress[$music][$difficulty]["difference"]['new-lamp-is-ab'] = $value->all_break ? "all-break" : "not-light";

                            $progress[$music][$difficulty]["new"] = $value;
                            $progress[$music][$difficulty]["ratings"]["normal_new"] = $newNormalRating;
                        }
                    }
                }
            }
        }
        $score['new']['Total']['battle_high_score'] = $score['new']['Basic']['battle_high_score'] + $score['new']['Advanced']['battle_high_score'] + $score['new']['Expert']['battle_high_score'] + $score['new']['Master']['battle_high_score'] + $score['new']['Lunatic']['battle_high_score'];
        $score['new']['Total']['technical_high_score'] = $score['new']['Basic']['technical_high_score'] + $score['new']['Advanced']['technical_high_score'] + $score['new']['Expert']['technical_high_score'] + $score['new']['Master']['technical_high_score'] + $score['new']['Lunatic']['technical_high_score'];
        $score['new']['Total']['platinum_score'] = $score['new']['Basic']['platinum_score'] + $score['new']['Advanced']['platinum_score'] + $score['new']['Expert']['platinum_score'] + $score['new']['Master']['platinum_score'] + $score['new']['Lunatic']['platinum_score'];
        $score['new']['Total']['over_damage_high_score'] = $score['new']['Basic']['over_damage_high_score'] + $score['new']['Advanced']['over_damage_high_score'] + $score['new']['Expert']['over_damage_high_score'] + $score['new']['Master']['over_damage_high_score'] + $score['new']['Lunatic']['over_damage_high_score'];

        $score['old']['Total']['battle_high_score'] = $score['old']['Basic']['battle_high_score'] + $score['old']['Advanced']['battle_high_score'] + $score['old']['Expert']['battle_high_score'] + $score['old']['Master']['battle_high_score'] + $score['old']['Lunatic']['battle_high_score'];
        $score['old']['Total']['technical_high_score'] = $score['old']['Basic']['technical_high_score'] + $score['old']['Advanced']['technical_high_score'] + $score['old']['Expert']['technical_high_score'] + $score['old']['Master']['technical_high_score'] + $score['old']['Lunatic']['technical_high_score'];
        $score['old']['Total']['platinum_score'] = $score['old']['Basic']['platinum_score'] + $score['old']['Advanced']['platinum_score'] + $score['old']['Expert']['platinum_score'] + $score['old']['Master']['platinum_score'] + $score['old']['Lunatic']['platinum_score'];
        $score['old']['Total']['over_damage_high_score'] = $score['old']['Basic']['over_damage_high_score'] + $score['old']['Advanced']['over_damage_high_score'] + $score['old']['Expert']['over_damage_high_score'] + $score['old']['Master']['over_damage_high_score'] + $score['old']['Lunatic']['over_damage_high_score'];

        $score['difference'] = [
            'Total' => [
                'battle_high_score' => $score['new']['Total']['battle_high_score'] - $score['old']['Total']['battle_high_score'],
                'technical_high_score' => $score['new']['Total']['technical_high_score'] - $score['old']['Total']['technical_high_score'],
                'platinum_score' => $score['new']['Total']['platinum_score'] - $score['old']['Total']['platinum_score'],
                'over_damage_high_score' => $score['new']['Total']['over_damage_high_score'] - $score['old']['Total']['over_damage_high_score'],
            ],
            'Basic' => [
                'battle_high_score' => $score['new']['Basic']['battle_high_score'] - $score['old']['Basic']['battle_high_score'],
                'technical_high_score' => $score['new']['Basic']['technical_high_score'] - $score['old']['Basic']['technical_high_score'],
                'platinum_score' => $score['new']['Basic']['platinum_score'] - $score['old']['Basic']['platinum_score'],
                'over_damage_high_score' => $score['new']['Basic']['over_damage_high_score'] - $score['old']['Basic']['over_damage_high_score'],
            ],
            'Advanced' => [
                'battle_high_score' => $score['new']['Advanced']['battle_high_score'] - $score['old']['Advanced']['battle_high_score'],
                'technical_high_score' => $score['new']['Advanced']['technical_high_score'] - $score['old']['Advanced']['technical_high_score'],
                'platinum_score' => $score['new']['Advanced']['platinum_score'] - $score['old']['Advanced']['platinum_score'],
                'over_damage_high_score' => $score['new']['Advanced']['over_damage_high_score'] - $score['old']['Advanced']['over_damage_high_score'],
            ],
            'Expert' => [
                'battle_high_score' => $score['new']['Expert']['battle_high_score'] - $score['old']['Expert']['battle_high_score'],
                'technical_high_score' => $score['new']['Expert']['technical_high_score'] - $score['old']['Expert']['technical_high_score'],
                'platinum_score' => $score['new']['Expert']['platinum_score'] - $score['old']['Expert']['platinum_score'],
                'over_damage_high_score' => $score['new']['Expert']['over_damage_high_score'] - $score['old']['Expert']['over_damage_high_score'],
            ],
            'Master' => [
                'battle_high_score' => $score['new']['Master']['battle_high_score'] - $score['old']['Master']['battle_high_score'],
                'technical_high_score' => $score['new']['Master']['technical_high_score'] - $score['old']['Master']['technical_high_score'],
                'platinum_score' => $score['new']['Master']['platinum_score'] - $score['old']['Master']['platinum_score'],
                'over_damage_high_score' => $score['new']['Master']['over_damage_high_score'] - $score['old']['Master']['over_damage_high_score'],
            ],
            'Lunatic' => [
                'battle_high_score' => $score['new']['Lunatic']['battle_high_score'] - $score['old']['Lunatic']['battle_high_score'],
                'technical_high_score' => $score['new']['Lunatic']['technical_high_score'] - $score['old']['Lunatic']['technical_high_score'],
                'platinum_score' => $score['new']['Lunatic']['platinum_score'] - $score['old']['Lunatic']['platinum_score'],
                'over_damage_high_score' => $score['new']['Lunatic']['over_damage_high_score'] - $score['old']['Lunatic']['over_damage_high_score'],
            ]
        ];

        return view('user_progress', compact('filter', 'url', 'status', 'progress', 'date', 'score', 'version', 'display', 'id', 'sidemark', 'isLoggedIn', 'isTwitterEnabled', 'twitterScreenName'));
    }
}

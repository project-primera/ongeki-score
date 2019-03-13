<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\User;
use App\UserStatus;
use App\ScoreData;
use App\Facades\OngekiUtility;

class ViewUserController extends Controller
{
    public function redirectRandomUserPage(){
        $users = (new UserStatus())->getRecentAllUserData();
        return redirect("/user/" . $users[array_rand($users)]->user_id);
    }

    public function getMyUserPage($path = ""){
        $user = \Auth::user();

        if($user == null){
            return view('require');
        }

        return redirect("/user/" . $user->id . "/" . $path);
    }

    public function getUserPage($id, $mode = null){
        $userStatus = new UserStatus();
        $user = User::where('id' ,$id)->first();
        $status = $userStatus->getRecentUserData($id);
        if(count($status) === 0){
            if(is_null($user)){
                abort(404);
            }else{
                return view("user_error", ['message' => '<p>このユーザーはOngekiScoreLogに登録していますが、オンゲキNETからスコア取得を行っていません。(UserID: ' . $id . ')</p><p>スコアの取得方法は<a href="/howto">こちら</a>をお読みください。</p>']);
            }
        }
        $status[0]->badge = "";
        if($user->role == 7){
            $status[0]->badge .= '&nbsp;<span class="tag developer">ProjectPrimera Developer</span>';
        }
        if($user->role >= 2){
            $status[0]->badge .= '&nbsp;<span class="tag net-premium">OngekiNet Premium</span>';
        }else if($user->role >= 1){
            $status[0]->badge .= '&nbsp;<span class="tag net-standard">OngekiNet Standard</span>';
        }

        $score = (new ScoreData)->getRecentUserScore($id)->addMusicData()->addDetailedData()->getValue();

        array_multisort(array_column($score, 'updated_at'), SORT_DESC, $score);

        $submenuActive = [0 => "", 1 => "", 2 => "", 3 => ""];

        $sidemark = null;
        if(Auth::check() && \Auth::user()->id == $id){
            switch (true) {
                case ($mode === "technical"):
                    $sidemark = "sidemark_mypage_technical";
                    break;
                case ($mode === "battle"):
                    $sidemark = "sidemark_mypage_battle";
                    break;
                case ($mode === "details"):
                    $sidemark = "sidemark_mypage_details";
                    break;
                default:
                    $sidemark = "sidemark_mypage_default";
                    break;
            }
        }
        
        switch (true) {
            case ($mode === "technical"):
                $mode = "song_status_technical";
                $submenuActive[3] = "is-active";
                break;
            case ($mode === "battle"):
                $mode = "song_status_battle";
                $submenuActive[2] = "is-active";
                break;
            case ($mode === "details"):
                $mode = "song_status_details";
                $submenuActive[1] = "is-active";
                break;
            default:
                $mode = "song_status";
                $submenuActive[0] = "is-active";
                break;
        }

        

        
        $stat['difficulty'] = [
            "Basic" => [],
            "Advanced" => [],
            "Expert" => [],
            "Master" => [],
            "Lunatic" => [],

        ];
        $stat['level'] = [
            "Lv.1" => [],
            "Lv.2" => [],
            "Lv.3" => [],
            "Lv.4" => [],
            "Lv.5" => [],
            "Lv.6" => [],
            "Lv.7" => [],
            "Lv.7+" => [],
            "Lv.8" => [],
            "Lv.8+" => [],
            "Lv.9" => [],
            "Lv.9+" => [],
            "Lv.10" => [],
            "Lv.10+" => [],
            "Lv.11" => [],
            "Lv.11+" => [],
            "Lv.12" => [],
            "Lv.12+" => [],
            "Lv.13" => [],
            "Lv.13+" => [],
            "Lv.14" => [],
            "Lv.14+" => [],
        ];

        $stat['average'] = $stat['level'];
        $stat['averageExist'] = $stat['level'];

        foreach ($score as $key => $value) {
            // レート値を表示していいユーザーなら取得 だめなら隠す
            if($user->role >= 2){
                $score[$key]->ratingValue = sprintf("%.2f", OngekiUtility::RateValueFromTitle($score[$key]->title, $score[$key]->difficulty, $score[$key]->technical_high_score));
                $score[$key]->ratingValueRaw = $score[$key]->ratingValue;
                if(OngekiUtility::IsEstimatedRateValueFromTitle($score[$key]->title, $score[$key]->difficulty, $score[$key]->technical_high_score)){
                    $score[$key]->ratingValue = "<i><span class='estimated-rating'>" . $score[$key]->ratingValue . "</span></i>";
                }else if($score[$key]->technical_high_score >= 1007500){
                    $score[$key]->ratingValue = "<i><span class='max-rating'>" . $score[$key]->ratingValue . "</span></i>";
                }
            }else{
                $score[$key]->ratingValue = "|||||||||"; 
                $score[$key]->ratingValueRaw = 0;
            }

            // フィルタ用隠しキー ランプ
            if($value->full_bell && $value->all_break){
                $score[$key]->rawLamp = "FB+FC+AB";
                $score[$key]->lamp = "fbfcab";
                $score[$key]->sortLamp = "5";
            }else if($value->full_bell && $value->full_combo){
                $score[$key]->rawLamp = "FB+FC";
                $score[$key]->lamp = "fbfc__";
                $score[$key]->sortLamp = "3";
            }else if($value->all_break){
                $score[$key]->rawLamp = "FC+AB";
                $score[$key]->lamp = "__fcab";
                $score[$key]->sortLamp = "4";
            }else if($value->full_combo){
                $score[$key]->rawLamp = "FC";
                $score[$key]->lamp = "__fc__";
                $score[$key]->sortLamp = "2";
            }else if($value->full_bell){
                $score[$key]->rawLamp = "FB";
                $score[$key]->lamp = "fb____";
                $score[$key]->sortLamp = "1";
            }else{
                $score[$key]->rawLamp = "-";
                $score[$key]->lamp = "______";
                $score[$key]->sortLamp = "0";
            }

            // ソート用隠しキー Technicalランク
            if($value->technical_high_score == 0){
                $score[$key]->rawTechnicalRank = "-";
            }else if($value->technical_high_score < 850000){
                $score[$key]->rawTechnicalRank = "under A";
            }else{
                $score[$key]->rawTechnicalRank = $value->technical_high_score_rank;
            }
            
            // ソート用隠しキー 難易度
            $score[$key]->rawDifficulty = $score[$key]->difficulty_str;

            // technicalスコアをランクにする
            if($value->technical_high_score == 0){
                $key = "NP";
            }else if($value->technical_high_score < 850000){
                $key = "B";
            }else{
                $key = $value->technical_high_score_rank;
            }
            // 対応する難易度のランク総数を追加
            if(!isset($stat['difficulty'][$value->difficulty_str][$key])){
				$stat['difficulty'][$value->difficulty_str][$key] = 0;
            }
            $stat['difficulty'][$value->difficulty_str][$key] += 1;
            
            // 対応する難易度のオーバーダメージランク総数を追加
            if(!isset($stat['difficulty'][$value->difficulty_str][$value->over_damage_high_score_rank])){
				$stat['difficulty'][$value->difficulty_str][$value->over_damage_high_score_rank] = 0;
            }
            $stat['difficulty'][$value->difficulty_str][$value->over_damage_high_score_rank] += 1;

            // 対応する難易度のfullcombo総数を追加
            if(!isset($stat['difficulty'][$value->difficulty_str]["fc"])){
				$stat['difficulty'][$value->difficulty_str]["fc"] = 0;
            }
            $stat['difficulty'][$value->difficulty_str]["fc"] += $value->full_combo;
            
            // 対応する難易度のallbreak総数を追加
            if(!isset($stat['difficulty'][$value->difficulty_str]["ab"])){
				$stat['difficulty'][$value->difficulty_str]["ab"] = 0;
            }
            $stat['difficulty'][$value->difficulty_str]["ab"] += $value->all_break;
            
            // 対応する難易度のfullbell総数を追加
            if(!isset($stat['difficulty'][$value->difficulty_str]["fb"])){
				$stat['difficulty'][$value->difficulty_str]["fb"] = 0;
            }
			$stat['difficulty'][$value->difficulty_str]["fb"] += $value->full_bell;
        

            // 対応するレベルのランク総数を追加
            if(!isset($stat['level']["Lv." . $value->level_str][$key])){
                $stat['level']["Lv." . $value->level_str][$key] = 0;
            }
            $stat['level']["Lv." . $value->level_str][$key] += 1;

            // 対応するレベルのオーバーダメージランク総数を追加
            if(!isset($stat['level']["Lv." . $value->level_str][$value->over_damage_high_score_rank])){
                $stat['level']["Lv." . $value->level_str][$value->over_damage_high_score_rank] = 0;
            }
            $stat['level']["Lv." . $value->level_str][$value->over_damage_high_score_rank] += 1;

            // 対応するレベルのfullcombo総数を追加
            if(!isset($stat['level']["Lv." . $value->level_str]["fc"])){
                $stat['level']["Lv." . $value->level_str]["fc"] = 0;
            }
            $stat['level']["Lv." . $value->level_str]["fc"] += $value->full_combo;
            
            // 対応するレベルのallbreak総数を追加
            if(!isset($stat['level']["Lv." . $value->level_str]["ab"])){
                $stat['level']["Lv." . $value->level_str]["ab"] = 0;
            }
            $stat['level']["Lv." . $value->level_str]["ab"] += $value->all_break;
            
            // 対応するレベルのfullbell総数を追加
            if(!isset($stat['level']["Lv." . $value->level_str]["fb"])){
                $stat['level']["Lv." . $value->level_str]["fb"] = 0;
            }
            $stat['level']["Lv." . $value->level_str]["fb"] += $value->full_bell;


            // レベル平均の曲数を追加
            if(!isset($stat['average']["Lv." . $value->level_str][$value->difficulty_str]["count"])){
                $stat['average']["Lv." . $value->level_str][$value->difficulty_str]['count'] = 0;
            }
            $stat['average']["Lv." . $value->level_str][$value->difficulty_str]['count']++;
    
            // レベル平均のスコアを追加 表示時に割る
            if(!isset($stat['average']["Lv." . $value->level_str][$value->difficulty_str]["score"])){
                $stat['average']["Lv." . $value->level_str][$value->difficulty_str]['score'] = 0;
            }
            $stat['average']["Lv." . $value->level_str][$value->difficulty_str]['score'] += $value->technical_high_score;

            // レベル平均のトータル曲数を追加
            if(!isset($stat['average']["Lv." . $value->level_str]["total"]["count"])){
                $stat['average']["Lv." . $value->level_str]["total"]['count'] = 0;
            }
            $stat['average']["Lv." . $value->level_str]["total"]['count']++;
    
            // // レベル平均のトータルスコアを追加 表示時に割る
            if(!isset($stat['average']["Lv." . $value->level_str]["total"]["score"])){
                $stat['average']["Lv." . $value->level_str]["total"]['score'] = 0;
            }
            $stat['average']["Lv." . $value->level_str]["total"]['score'] += $value->technical_high_score;

            // 全曲平均の難易度別曲数を追加
            if(!isset($stat['average']["All"][$value->difficulty_str]["count"])){
                $stat['average']["All"][$value->difficulty_str]['count'] = 0;
            }
            $stat['average']["All"][$value->difficulty_str]['count']++;
    
            // 全曲平均の難易度別スコアを追加 表示時に割る
            if(!isset($stat['average']["All"][$value->difficulty_str]["score"])){
                $stat['average']["All"][$value->difficulty_str]['score'] = 0;
            }
            $stat['average']["All"][$value->difficulty_str]['score'] += $value->technical_high_score;

            // 全曲平均のトータル曲数を追加
            if(!isset($stat['average']["All"]["total"]["count"])){
                $stat['average']["All"]["total"]['count'] = 0;
            }
            $stat['average']["All"]["total"]['count']++;
    
            // 全曲平均のトータルスコアを追加 表示時に割る
            if(!isset($stat['average']["All"]["total"]["score"])){
                $stat['average']["All"]["total"]['score'] = 0;
            }
            $stat['average']["All"]["total"]['score'] += $value->technical_high_score;

            // 以下プレイ済み曲のみ
            if($value->technical_high_score !== 0){
                // レベル平均の曲数を追加
                if(!isset($stat['averageExist']["Lv." . $value->level_str][$value->difficulty_str]["count"])){
                    $stat['averageExist']["Lv." . $value->level_str][$value->difficulty_str]['count'] = 0;
                }
                $stat['averageExist']["Lv." . $value->level_str][$value->difficulty_str]['count']++;
    
                // レベル平均のスコアを追加 表示時に割る
                if(!isset($stat['averageExist']["Lv." . $value->level_str][$value->difficulty_str]["score"])){
                    $stat['averageExist']["Lv." . $value->level_str][$value->difficulty_str]['score'] = 0;
                }
                $stat['averageExist']["Lv." . $value->level_str][$value->difficulty_str]['score'] += $value->technical_high_score;

                // レベル平均のトータル曲数を追加
                if(!isset($stat['averageExist']["Lv." . $value->level_str]["total"]["count"])){
                    $stat['averageExist']["Lv." . $value->level_str]["total"]['count'] = 0;
                }
                $stat['averageExist']["Lv." . $value->level_str]["total"]['count']++;
    
                // レベル平均のトータルスコアを追加 表示時に割る
                if(!isset($stat['averageExist']["Lv." . $value->level_str]["total"]["score"])){
                    $stat['averageExist']["Lv." . $value->level_str]["total"]['score'] = 0;
                }
                $stat['averageExist']["Lv." . $value->level_str]["total"]['score'] += $value->technical_high_score;

                // 全曲平均の難易度別曲数を追加
                if(!isset($stat['averageExist']["All"][$value->difficulty_str]["count"])){
                    $stat['averageExist']["All"][$value->difficulty_str]['count'] = 0;
                }
                $stat['averageExist']["All"][$value->difficulty_str]['count']++;
        
                // 全曲平均の難易度別スコアを追加 表示時に割る
                if(!isset($stat['averageExist']["All"][$value->difficulty_str]["score"])){
                    $stat['averageExist']["All"][$value->difficulty_str]['score'] = 0;
                }
                $stat['averageExist']["All"][$value->difficulty_str]['score'] += $value->technical_high_score;
    
                // 全曲平均のトータル曲数を追加
                if(!isset($stat['averageExist']["All"]["total"]["count"])){
                    $stat['averageExist']["All"]["total"]['count'] = 0;
                }
                $stat['averageExist']["All"]["total"]['count']++;
        
                // 全曲平均のトータルスコアを追加 表示時に割る
                if(!isset($stat['averageExist']["All"]["total"]["score"])){
                    $stat['averageExist']["All"]["total"]['score'] = 0;
                }
                $stat['averageExist']["All"]["total"]['score'] += $value->technical_high_score;
            }
            
        }
        return view('user', compact('id', 'status', 'score', 'stat', 'mode', 'submenuActive', 'sidemark'));
    }
}

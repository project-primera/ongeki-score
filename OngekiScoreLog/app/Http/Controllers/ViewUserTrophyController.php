<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserStatus;
use App\UserTrophy;

class ViewUserTrophyController extends Controller
{
    function getIndex(int $id){
        $user = User::where('id' ,$id)->first();
        $userStatus = new UserStatus();
        $status = $userStatus->getRecentUserData($id);

        if(count($status) === 0){
            if(is_null(User::where('id' ,$id)->first())){
                abort(404);
            }else{
                return view("user_error", ['message' => '<p>このユーザーはOngekiScoreLogに登録していますが、オンゲキNETからスコア取得を行っていません。(UserID: ' . $id . ')</p><p>スコアの取得方法は<a href="/howto">こちら</a>をお読みください。</p>']);
            }
        }

        $sidemark = null;
        if(Auth::check() && \Auth::user()->id == $id){
            $sidemark = "sidemark_mypage_trophy";
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

        $trophies = json_decode(json_encode(UserTrophy::where('user_id', $id)->get()), true);

        $trophyIdToStr = [
            0 => "Normal",
            1 => "Silver",
            2 => "Gold",
            3 => "Platinum",
            4 => "Rainbow",
        ];

        return view("user_trophy", compact('id', 'status', 'trophies', 'trophyIdToStr', 'sidemark'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OngekiScoreLog\UserListCondition;
use App\UserStatus;

class ViewAllUserController extends Controller
{
    private const PER_PAGE = 100;

    /** 一覧の表示を変える URL クエリ */
    private const LIST_PARAMETERS = ['page', 'sort', 'order', 'q'];

    public function getIndex(Request $request){
        $condition = UserListCondition::fromRequest($request);
        $currentVersionFrom = $this->currentVersionFrom();

        // ページ送りのリンクは、ほかのリンクと同じくホスト名を含まない形（/user?page=2）にする
        $users = (new UserStatus())
            ->paginateLatestOfPublicUsers($condition, $currentVersionFrom, self::PER_PAGE)
            ->withPath('/user')
            ->appends($condition->query())
            ->onEachSide(1);

        // 並び替え・ページ・検索の組み合わせで URL が大量にできるので、検索エンジンに載せるのは /user そのものだけにする
        $noindex = count(array_intersect(self::LIST_PARAMETERS, $request->query->keys())) > 0;

        return view('alluser', compact('users', 'condition', 'currentVersionFrom', 'noindex'));
    }

    /**
     * 現行バージョンの開始日時を、user_status の日時と比べられる形（Y-m-d H:i:s）で返す。
     * 設定が無い・日時として読めない場合は null（全員を現行バージョンとして扱う）。
     */
    private function currentVersionFrom(): ?string
    {
        $value = config('env.ongeki-version-date');
        $timestamp = is_string($value) ? strtotime($value) : false;

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OngekiScoreLog\UserListCondition;
use App\UserStatus;

class ViewAllUserController extends Controller
{
    private const PER_PAGE = 100;

    public function getIndex(Request $request){
        $condition = UserListCondition::fromRequest($request);
        $currentVersionFrom = $this->currentVersionFrom();

        $users = (new UserStatus())->paginateLatestOfPublicUsers($condition, $currentVersionFrom, self::PER_PAGE);

        return view('alluser', compact('users', 'currentVersionFrom'));
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

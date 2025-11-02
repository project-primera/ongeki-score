<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserStatus extends Model
{
    protected $table = "user_status";
    protected $guarded = ['id'];

    function getRecentUserData($id, $exclude_private = true){
        $sql = DB::table($this->table)->select('*')
            ->from($this->table)->where('user_id', $id)->orderBy('id', 'desc')->limit(1);

        # プライベートユーザを除外（自分自身以外）
        if ($exclude_private){
            $me = \Auth::user();
            if ($me === null || $id != $me->id){
                $sql = $sql
                    ->join('users', "$this->table.user_id", '=', 'users.id')
                    ->where('users.private', 0);
            }
        }

        return $sql->select("$this->table.*")->get();
    }

    function getRecentAllUserData($exclude_private = true){
        # SELECT * FROM user_status AS t1
        #     WHERE created_at = (
        #         SELECT MAX(created_at) FROM user_status AS t2
        #             WHERE t1.user_id = t2.user_id
        #     )
        $sql_latest_status = DB::table("$this->table as t2")
            ->select('user_id', DB::raw('MAX(created_at) as latest_created_at'))
            ->groupBy('user_id');

        $sql_all_users = DB::table("$this->table as t1")
            ->joinSub($sql_latest_status, 'latest', function ($join) {
                $join->on('t1.user_id', '=', 'latest.user_id')
                    ->on('t1.created_at', '=', 'latest.latest_created_at');
            });

        # プライベートユーザを除外
        if ($exclude_private){
            $sql_all_users = $sql_all_users
                ->join('users', 't1.user_id', '=', 'users.id')
                ->where('users.private', 0);
        }

        $sql = $sql_all_users->select('t1.*')->get();

        $users = [];
        foreach ($sql as $key => $value) {
            if(!in_array($value->user_id, $users)){
                $users[] = $value->user_id;
            }else{
                unset($sql[$key]);
            }
        }
        return $sql;
    }
}

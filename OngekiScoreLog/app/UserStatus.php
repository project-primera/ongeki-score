<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserStatus extends Model
{
    protected $table = "user_status";
    protected $guarded = ['id', 'user_id'];

    function getRecentUserData($id){
        $sql = DB::table($this->table)->select('*')
            ->from($this->table)->where('user_id', $id)->orderBy('id', 'desc')->limit(1);
        return $sql->get();
    }

    function getRecentAllUserData(){
        $sql = DB::select('SELECT * FROM user_status AS t1 WHERE created_at = (SELECT MAX(created_at) FROM user_status AS t2 WHERE t1.user_id = t2.user_id)');
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

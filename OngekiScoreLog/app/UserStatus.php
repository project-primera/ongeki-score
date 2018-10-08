<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserStatus extends Model
{
    protected $table = "user_status";
    protected $guarded = ['id', 'user_id'];

    function getRecentUserData($id){
        // SELECT * FROM ongeki_score_tool.user_status WHERE user_id = 1 ORDER BY id DESC LIMIT 1
        $sql = DB::table($this->table)->select('*')
            ->from($this->table)->where('user_id', $id)->orderBy('id', 'desc')->limit(1);
        return $sql->get();
    }
}

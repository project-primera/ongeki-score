<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScoreData extends Model
{
    //
    protected $table = "score_datas";
    protected $guarded = ['id', 'user_id'];
}

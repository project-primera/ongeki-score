<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MusicData extends Model
{
    protected $table = "music_datas";
    protected $guarded = ['id'];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    protected $table = "user_status";
    public $incrementing = false;
    protected $guarded = ['id'];

}

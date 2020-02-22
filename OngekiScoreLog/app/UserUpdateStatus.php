<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserUpdateStatus extends Model
{
    protected $table = "user_update_status";
    protected $guarded = ['id', 'created_at', 'updated_at'];
}

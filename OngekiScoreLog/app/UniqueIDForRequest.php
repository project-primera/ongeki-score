<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UniqueIDForRequest extends Model
{
    //
    protected $table = "unique_id_for_requests";
    protected $guarded = ['id'];
}

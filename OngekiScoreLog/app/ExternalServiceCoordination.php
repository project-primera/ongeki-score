<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Log;

class ExternalServiceCoordination extends Model{
    
    protected $table = "external_service_coordinations";
    protected $fillable = ['id', 'twitter_access_token', 'twitter_access_token_secret'];

    function get($id){
        $value = DB::table($this->table)->select('*')->from($this->table)->where('id', $id)->get();
        return $value;
    }

}
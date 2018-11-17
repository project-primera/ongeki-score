<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExternalServiceStatus extends Model
{
    protected $table = "external_service_statuses";
    protected $fillable = ['id', 'twitter_id', 'twitter_screen_name'];
}

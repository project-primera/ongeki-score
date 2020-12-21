<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;

class MusicController extends Controller
{
    public function GetSameNameMusic()
    {
        return [
            'Hand in Hand',
            'Singularity',
        ];
    }
}

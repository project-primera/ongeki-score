<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\MusicData;

class MusicController extends Controller
{
    public function GetSameNameMusic()
    {
        return (new MusicData())->getSameMusicList();
    }

    public function GetFirstDraftMusic()
    {
        return (new MusicData())->getFirstDraftMusicList();
    }
}

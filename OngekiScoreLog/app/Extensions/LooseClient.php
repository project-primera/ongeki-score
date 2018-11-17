<?php

namespace App\Extensions\Cowitter;
use mpyw\Cowitter\Client;

class LooseClient extends Client{
    public function getToken(){
        return $this->getInternalCredential()['token'];
    }

    public function getTokenSecret(){
        return $this->getInternalCredential()['token_secret'];
    }
}
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getIndex()
    {
        $user = Auth::user();
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ];
    }
}

@extends('layouts.app')

@section('title', 'Page Title')
@section('sidemark_user', "is-active")

@section('content')
    @component('layouts/components/user/user_status')
        @slot('name')
            Ａｋｉ．１４２ｓ
        @endslot
        @slot('trophy')
            タテマエと本心の大乱闘
        @endslot
        @slot('level')
            69
        @endslot
        @slot('battle_point')
            9219
        @endslot
        @slot('rating')
            15.04
        @endslot
        @slot('rating_max')
            15.04
        @endslot
        @slot('money')
            13,404
        @endslot
        @slot('money_max')
            79,004
        @endslot
        @slot('total_play')
            552
        @endslot
        @slot('friend_code')
            1012895587066
        @endslot
        @slot('comment')
        当面の目標は全曲全難易度プレイ<br>
        お財布壊れない程度に頑張ります。<br>
        <br>
        申請お気軽にどうぞ。@2xAki<br>
        18/08/15 レート15
        @endslot
    @endcomponent
@endsection
@extends('layouts.app')

@section('title', 'Page Title')
@section('sidemark_user', "is-active")

@section('content')
    @component('layouts/components/user/user_status')
        @slot('name')
            {{$status[0]->name}}
        @endslot
        @slot('trophy')
            {{$status[0]->trophy}}
        @endslot
        @slot('level')
            {{$status[0]->level}}
        @endslot
        @slot('battle_point')
            {{$status[0]->battle_point}}
        @endslot
        @slot('rating')
            {{$status[0]->rating}}
        @endslot
        @slot('rating_max')
            {{$status[0]->rating_max}}
        @endslot
        @slot('money')
            {{$status[0]->money}}
        @endslot
        @slot('money_max')
            {{$status[0]->total_money}}
        @endslot
        @slot('total_play')
            {{$status[0]->total_play}}
        @endslot
        @slot('friend_code')
            {{$status[0]->friend_code}}
        @endslot
        @slot('comment')
            {!! nl2br(e($status[0]->comment)) !!}
        @endslot
    @endcomponent

    @component('layouts/components/user/song_status')
    @endcomponent

@endsection
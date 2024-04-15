@extends('layouts.app')

@section('title', $status[0]->name)
@section('hero_subtitle', $status[0]->trophy)
@section('hero_title', $status[0]->name)
@section('additional_head')
    <meta name="robots" content="noindex">
@endsection
@section('additional_footer')
    <script type="text/javascript" src="{{ mix('/js/sortTable.js') }}"></script>
    <script type="text/javascript" src="{{ mix('/js/tableScalable.js') }}"></script>
@endsection
@section('sidemark_mypage_overdamage', "is-active")

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li><a href="/user/{{$id}}/battle">Battle</a></li>
    <li class="is-active"><a href="/user/{{$id}}/overdamage">OverDamage</a></li>
    <li><a href="/user/{{$id}}/trophy">称号</a></li>
    <li><a href="/user/{{$id}}/rating">Rating</a></li>
    <li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection

@section('content')
    @component('layouts/components/user/user_status')
        @slot('badge')
            {!!$status[0]->badge!!}
        @endslot
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

    {{-- @component('layouts/components/user/song_filter')
    @endcomponent --}}

    @component('layouts/components/user/song_status_overdamage', ['score' => $scoreDatas, 'topRankerScore' => $topRankerScore, 'id' => $id])
    @endcomponent

@endsection

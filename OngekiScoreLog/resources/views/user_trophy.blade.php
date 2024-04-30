@extends('layouts.app')

@section('title', $status[0]->name . "の獲得称号")
@section('hero_subtitle', $status[0]->trophy)
@section('hero_title', $status[0]->name)
@section('additional_footer')
    <script type="text/javascript" src="{{ mix('/js/sortTrophy.js') }}"></script>
@endsection

@if(isset($sidemark) && !is_null($sidemark))
    @section($sidemark, "is-active")
@endif

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li><a href="/user/{{$id}}/battle">Battle</a></li>
    <li><a href="/user/{{$id}}/overdamage">OverDamage</a></li>
    <li class="is-active"><a href="/user/{{$id}}/trophy">称号</a></li>
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

    <article class="box">
        <h3 class="title is-3">獲得称号</h3>
        <div id="sort_table" class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th class="sort" data-sort="sort_title">Title</th>
                        <th class="sort" data-sort="sort_grade">Grade</th>
                        <th class="sort" data-sort="sort_detail">detail</th>
                        <th class="sort" data-sort="sort_update">Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="sort" data-sort="sort_title">Title</th>
                        <th class="sort" data-sort="sort_grade">Grade</th>
                        <th class="sort" data-sort="sort_detail">detail</th>
                        <th class="sort" data-sort="sort_update">Update</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    @foreach ($trophies as $trophy)
                    <tr>
                        <td class="sort_title">{{$trophy['name']}}</td>
                        <td class="sort_grade"><span class="sort-key">{{$trophy['grade']}}</span>{{$trophyIdToStr[$trophy['grade']]}}</td>
                        <td class="sort_detail">{{$trophy['detail']}}</td>
                        <td class="sort_update">{{date('Y-m-d', strtotime($trophy['updated_at']))}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection

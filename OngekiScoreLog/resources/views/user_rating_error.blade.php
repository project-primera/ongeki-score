@extends('layouts.app')

@section('title', $status[0]->name . "のレーティング情報")
@section('hero_subtitle', $status[0]->trophy)
@section('hero_title', $status[0]->name)
@section('additional_footer')
    <script type="text/javascript" src="/js/html2canvas.min.js"></script>
    <script type="text/javascript" src="{{ mix('/js/userProgress.js') }}"></script>
@endsection
@if(isset($sidemark) && !is_null($sidemark))
    @section($sidemark, "is-active")
@endif

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li><a href="/user/{{$id}}/battlescore">Battle</a></li>
    <li><a href="/user/{{$id}}/overdamage">OverDamage</a></li>
    <li><a href="/user/{{$id}}/trophy">称号</a></li>
    <li class="is-active"><a href="/user/{{$id}}/rating">Rating</a></li>
    <li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection


@section('content')
    <article class="box">
        <p>{{$message}}</p>
    </article>
@endsection

@extends('layouts.app')

@section('title', $status[0]->name . " / [" . ucfirst($difficulty) . "] " . $score[0]->title)
@section('hero_subtitle', $status[0]->name . " - 楽曲詳細")
@section('hero_title', "[" . ucfirst($difficulty) . "] " . $score[0]->title)
@section('additional_footer')
    {!!$highcharts!!}
@endsection
@if(isset($sidemark) && !is_null($sidemark))
    @section($sidemark, "is-active")
@endif

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/battle">Battle</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li><a href="/user/{{$id}}/trophy">称号</a></li>
    <li><a href="/user/{{$id}}/rating">Rating</a></li>
    <li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection

@section('content')
    <article class="box">
        <div id="graph"></div>
    </article>
@endsection
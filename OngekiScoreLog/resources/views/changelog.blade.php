@extends('layouts.app')

@section('title', '更新履歴')
@section('hero_title', "更新履歴")

@section('content')
    <article class="box">
        @foreach ($version as $item)
            <h3 class="title is-3" style="margin-bottom: 0.2em;">{{$item->name}}</h3>
            <p>
                {{$item->tag_name . '(' . date('Y/m/d', strtotime($item->published_at)) . ')'}}<br>
                {!! nl2br($item->body) !!}
            </p>
        @endforeach
    </article>
@endsection
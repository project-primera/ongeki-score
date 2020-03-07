@extends('layouts.app')

@section('title', '更新履歴')
@section('hero_title', "更新履歴")

@section('content')
    <article class="box">
        <article class="message is-warning">
            <div class="message-body">
                このページは更新されていません。
                @if (config('env.github-repo-url', NULL) !== NULL)
                    <br>最新の情報は<a href="<?=config('env.github-repo-url')?>/releases" target="_blank">GitHub release</a>をご覧ください。
                @endif
            </div>
        </article>
        @foreach ($version as $item)
            <h3 class="title is-3" style="margin-bottom: 0.2em;">{{$item->name}}</h3>
            <p>
                {{$item->tag_name . '(' . date('Y/m/d', strtotime($item->published_at)) . ')'}}<br>
                {!! nl2br($item->body) !!}
            </p>
        @endforeach
    </article>
@endsection
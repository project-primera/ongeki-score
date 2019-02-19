@extends('layouts.app')

@section('title', '404 Not Found')
@section('hero_title', "404 Not Found")

@section('content')
<div class="container">
    <article class="box">
        <div class="error-top">
            <div class="error-top-child">
                <span class="status-code">404</span><br>
                お探しのページは見つかりませんでした
            </div>
        </div>
        <p>
            お探しのページは一時的にアクセスできない状況にあるか、移動もしくは削除された可能性があります。<br>
            以下の方法をお試しください。<br>
            <a href="/">Topページに戻る</a><br>
            @if (!is_null($referer))
                <a href="{{$referer}}">元のページに戻る</a><br>
            @endif
        </p>
        <p>
            不具合の可能性がありましたら以下の情報を添えてご報告をお願いいたします。<br>
            URL: {{$user['url']}}<br>
            IP: {{$user['ip']}}<br>
            ID: {{$user['id']}}<br>
            <a href="https://twitter.com/ongeki_score" target="_blank">Twitter@ongeki_score</a><br>
            <a href="https://github.com/Slime-hatena/ProjectPrimera/issues" target="_blank">GitHub issue</a>
        </p>
    </article>
</div>
@endsection
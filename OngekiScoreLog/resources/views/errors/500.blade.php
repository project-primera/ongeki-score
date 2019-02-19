@extends('layouts.app')

@section('title', 'すべてのユーザー')
@section('sidemark_alluser', "is-active")
@section('hero_title', "すべてのユーザー")
@section('additional_footer')
    <script type="text/javascript" src="{{ mix('/js/sortAllUserTable.js') }}"></script>
@endsection

@section('content')
<div class="container">
    <article class="box">
        <div class="error-top">
            <div class="error-top-child">
                <span class="status-code">500</span><br>
                何らかの不具合が発生しました
            </div>
        </div>
        <p>
            表示しようとしたページはシステムの不具合で表示できませんでした。<br>
            
            <a href="/">Topページに戻る</a><br>
            @if (!is_null($referer))
            <a href="{{$referer}}">元のページに戻る</a><br>
            @endif
        </p>
        <p>
            以下の情報を添えてご報告をお願いいたします。<br>
            URL: {{$user['url']}}<br>
            IP: {{$user['ip']}}<br>
            ID: {{$user['id']}}<br>
            <a href="https://twitter.com/ongeki_score" target="_blank">Twitter@ongeki_score</a><br>
            <a href="https://github.com/Slime-hatena/ProjectPrimera/issues" target="_blank">GitHub issue</a>
        </p>
    </article>
</div>
@endsection
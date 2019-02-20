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
            お探しのページは一時的にアクセスできない状況にあるか、移動もしくは削除された可能性があります。
        </p>
        <p>
            <a href="/">Topページに戻る</a><br>
            <a href="{{url()->previous()}}">元のページに戻る</a><br>
        </p>
    </article>
</div>
@endsection
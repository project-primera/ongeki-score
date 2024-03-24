@extends('layouts.app')

@section('title', '429 Too Many Requests')
@section('hero_title', "429 Too Many Requests")

@section('content')
    <article class="box">
        <div class="error-top">
            <div class="error-top-child">
                <span class="status-code">429</span><br>
                ちょっと待ってください
            </div>
        </div>
        <p>
            レートリミット以上のアクセスを行ったため、アクセスが制限されています。<br>
            しばらく時間を置いてから再度アクセスしてください。
        </p>
        <p>
            <a href="/">Topページに戻る</a><br>
            <a href="{{url()->previous()}}">元のページに戻る</a><br>
        </p>
    </article>
@endsection

@extends('layouts.app')

@section('title', '419 Page Expired')
@section('hero_title', "419 Page Expired")

@section('content')
    <article class="box">
        <div class="error-top">
            <div class="error-top-child">
                <span class="status-code">419</span><br>
                ページの有効期限が切れました
            </div>
        </div>
        <p>
            ページを開いてから時間が経ったか、別のタブでログインし直したため、操作を受け付けられませんでした。<br>
            元のページを再読み込みしてから、もう一度お試しください。
        </p>
        <p>
            <a href="{{url()->previous()}}">元のページに戻る</a><br>
            <a href="/">Topページに戻る</a><br>
        </p>
    </article>
@endsection

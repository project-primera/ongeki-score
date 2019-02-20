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
            表示しようとしたページはシステムの不具合で表示できませんでした。
        </p>
        <p>
            <a href="/">Topページに戻る</a><br>
            <a href="{{url()->previous()}}">元のページに戻る</a><br>
        </p>
    </article>
</div>
@endsection
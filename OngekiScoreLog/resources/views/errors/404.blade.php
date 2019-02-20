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
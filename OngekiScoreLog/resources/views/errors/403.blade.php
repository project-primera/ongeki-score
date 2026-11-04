@extends('layouts.app')

@section('title', '403 Forbidden')
@section('hero_title', "403 Forbidden")

@section('content')
    <article class="box">
        <div class="error-top">
            <div class="error-top-child">
                <span class="status-code">403</span><br>
                このページを表示する権限がありません
            </div>
        </div>
        <p>
            管理者向けのページなど、今のアカウントでは開けないページです。<br>
            別のアカウントで使う場合は、一度ログアウトしてからログインし直してください。
        </p>
        <p>
            <a href="{{url()->previous()}}">元のページに戻る</a><br>
            <a href="/">Topページに戻る</a><br>
        </p>
    </article>
@endsection

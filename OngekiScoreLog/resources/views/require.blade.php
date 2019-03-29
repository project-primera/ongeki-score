@extends('layouts.app')

@section('title', '要ログイン')
@section('hero_subtitle', '')
@section('hero_title', '要ログインページ')

@section('content')
    <article class="box">
        <h3 class="title is-3">このページはログインが必要です。</h3>
        <p>
            <a href="/register">新規登録ページ</a>&nbsp;/&nbsp;<a href="/login">ログインページ</a>
        </p>
    </article>
@endsection

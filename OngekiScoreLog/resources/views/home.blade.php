@extends('layouts.app')

@section('title', 'ログインページ')
@section('hero_subtitle', '')
@section('hero_title', 'ログインページ')

@section('content')
    <article class="box">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        ログイン済みです。
    </article>
@endsection

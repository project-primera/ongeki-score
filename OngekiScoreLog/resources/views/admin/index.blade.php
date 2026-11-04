@extends('layouts.app')

@section('title', '管理ページ')
@section('hero_title', "管理ページ")
@section('hero_subtitle', "Top")

@section('submenu')
    @include('admin/_submenu', ['active' => 'index'])
@endsection

@section('content')
    <article class="box">
        @if ($message !== null)
            <div class="notification">
                {{ $message }}
            </div>
        @endif
        {{-- 実行は CSRF 付きの POST。GET のリンクだと他サイトから踏ませるだけでキャッシュ消去や重い集計を走らせられる --}}
        <h3 class="title is-3">キャッシュ系クリア</h3>
        <h4 class="title is-4">すべて</h4>
        <div class="buttons">
            <form method="post" action="/admin/apply/all/clear">@csrf<button type="submit" class="button is-danger">all:clear</button></form>
            <form method="post" action="/admin/apply/all/cache">@csrf<button type="submit" class="button is-warning">all:cache</button></form>
        </div>
        <h4 class="title is-4">config</h4>
        <div class="buttons">
            <form method="post" action="/admin/apply/config/clear">@csrf<button type="submit" class="button is-danger">config:clear</button></form>
            <form method="post" action="/admin/apply/config/cache">@csrf<button type="submit" class="button is-warning">config:cache</button></form>
        </div>
        <h4 class="title is-4">route</h4>
        <div class="buttons">
            <form method="post" action="/admin/apply/route/clear">@csrf<button type="submit" class="button is-danger">route:clear</button></form>
            <form method="post" action="/admin/apply/route/cache">@csrf<button type="submit" class="button is-warning">route:cache</button></form>
        </div>
        <h4 class="title is-4">view</h4>
        <div class="buttons">
            <form method="post" action="/admin/apply/view/clear">@csrf<button type="submit" class="button is-danger">view:clear</button></form>
            <form method="post" action="/admin/apply/view/cache">@csrf<button type="submit" class="button is-warning">view:cache</button></form>
        </div>
        <h4 class="title is-4">cache</h4>
        <div class="buttons">
            <form method="post" action="/admin/apply/cache">@csrf<button type="submit" class="button is-danger">cache:clear</button></form>
        </div>
        <hr>
        <h3 class="title is-3">統計データ生成</h3>
        <p>超絶重いので実行する時間とタイミングに注意！</p>
        <h4 class="title is-4">Battle Score</h4>
        <div class="buttons">
            <form method="post" action="/admin/generate/battle-score">@csrf<button type="submit" class="button is-danger">max</button></form>
        </div>
        <h4 class="title is-4">Over Damage</h4>
        <div class="buttons">
            <form method="post" action="/admin/generate/over-damage">@csrf<button type="submit" class="button is-danger">max</button></form>
        </div>
    </article>
@endsection

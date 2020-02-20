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
        <h3 class="title is-3">キャッシュ系クリア</h3>
        <h4 class="title is-4">config</h4>
        <p>
            <a href="/admin/apply/config/clear"><button class="button is-danger">config:clear</button></a>
            <a href="/admin/apply/config/cache"><button class="button is-warning">config:cache</button></a>
        </p>
        <h4 class="title is-4">route</h4>
        <p>
            <a href="/admin/apply/route/clear"><button class="button is-danger">route:clear</button></a>
            <a href="/admin/apply/route/cache"><button class="button is-warning">route:cache</button></a>
        </p>
        <h4 class="title is-4">view</h4>
        <p>
            <a href="/admin/apply/view/clear"><button class="button is-danger">view:clear</button></a>
            <a href="/admin/apply/view/cache"><button class="button is-warning">view:cache</button></a>
        </p>
        <h4 class="title is-4">cache</h4>
        <p>
            <a href="/admin/apply/cache"><button class="button is-danger">cache:clear</button></a>
        </p>
    </article>
@endsection
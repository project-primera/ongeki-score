@extends('layouts.app')

@section('title', '設定')
@section('sidemark_setting', "is-active")
@section('hero_title', "設定")

@section('content')
    <article class="box">
        <h3 class="title is-3">Twitter連携</h3>
        <p>Twitterを連携するとスコア更新を画像とともにツイートできます。<br>
            認証中のアカウント: {{$display['screenName']}}<br>
            <a href="/setting/twitter" class="button">連携する</a>
        </p>
    </article>

@endsection
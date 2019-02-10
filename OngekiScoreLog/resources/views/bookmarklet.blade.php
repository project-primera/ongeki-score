@extends('layouts.app')

@section('title', 'ブックマークレットを生成')
@section('hero_subtitle', '')
@section('hero_title', 'ブックマークレットを生成')
@section('sidemark_bookmarklet', "is-active")

@section('content')
<div class="container">
    <article class="box">
        <h3 class="title is-3">ブックマークレットを生成</h3>
        <p>
            ブックマークレットを生成します。<br>
            すでに生成した後に再生成すると以前のブックマークレットは使用できなくなります。
        </p>
        <p>
            {!! $content !!}
        </p>

        <div class="notification is-warning">
            <p>
                生成されたブックマークレットは絶対にあなた以外の人に教えないでください。<br>
                このブックマークレットがあれば誰でも<strong>あなたとしてスコアを登録することが出来ます。</strong><br>
            </p>
            <p>
                もし複数の端末でブックマークレットを使用したい場合は、ブックマークレットを何らかの方法で別のブラウザに渡してください。<br>
                再生成すると以前のブックマークレットは使用できなくなります。
            </p>
        </div>

    </article>
</div>
@endsection

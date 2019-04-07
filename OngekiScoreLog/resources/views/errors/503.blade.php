@extends('layouts.app')

@section('title', '503 Service Temporarily Unavailable')
@section('hero_title', "503 Service Temporarily Unavailable")

@section('content')
    <article class="box">
        <div class="error-top">
            <div class="error-top-child">
                <span class="status-code">503</span><br>
                サービスは一時的に利用できません
            </div>
        </div>
        <p>
            ただいまメンテナンス中です。予告のないメンテナンスの場合、1分程度で終了します。<br>
            情報については<a href="https://twitter.com/ongeki_score" target="_blank">Twitter@ongeki_score</a>にてお知らせします。
        </p>
    </article>
@endsection
@extends('layouts.app')

@section('title', '設定')
@section('sidemark_setting', "is-active")
@section('hero_title', "設定")

@section('content')
    <article class="box">
        <h3 class="title is-3">プライベートモード</h3>
        <p>プライベートモードにすると他人から見えなくなります。<br>
            現在の状態: {{$display['private'] ? 'プライベート' : 'パブリック'}}モード<br>

            @if ($display['private'])
                <a href="/setting/public" class="button">パブリックモードに戻す</a>
            @else
                <a href="/setting/private" class="button">プライベートモードにする</a>
            @endif
        </p>
    </article>

@endsection
@extends('layouts.app')

@section('title', '設定')
@section('sidemark_setting', "is-active")
@section('hero_title', "設定")

@section('content')
    <article class="box">
        <h3 class="title is-3">プライベートモード</h3>
        <p>
            プライベートモードにすると自分のユーザーページが他のユーザーから閲覧できなくなります。
        </p>
        <p>
        <ul>
            <li>・「すべてのユーザー」に表示されなくなります</li>
            <li>・スコアページやレーティングページ、獲得称号ページが自分以外のユーザーから見えなくなります</li>
            <li>・副作用として、OngekiScoreLogのデータを利用した外部サイトが利用できなくなるかもしれません</li>
            <li>※統計情報の集計対象からは外れません</li>
            <li>※管理者はユーザーサポートのためプライベートモードのユーザーページにアクセスする可能性があります</li>
        </ul>
        </p>
        <p>
            {{$display['private'] ? '現在の状態: 有効' : ''}}
        </p>
        <p>
            @if ($display['private'])
                <a href="/setting/public" class="button is-danger">プライベートモードを無効にする</a>
            @else
                <a href="/setting/private" class="button is-info">プライベートモードを有効にする</a>
            @endif
        </p>

        <h3 class="title is-3">Twitter連携</h3>
        <p>提供を終了いたしました。</p>
        <p><a href="#" class="button" disabled>連携する</a></p>

    </article>

@endsection

@extends('layouts.app')

@section('title', 'Page Title')
@section('sidemark_top', "is-active")
@section('hero_subtitle', "")
@section('hero_title', "OngekiScoreLog")

@section('content')
    <div class="box">
        <h3 class="title is-3">このサイトについて</h3>
        <p class="space-bottom">
                OngekiScoreLogはSEGAのアーケード音楽ゲーム「オンゲキ」のスコアを集計し、見やすくソートしたりできるツールです。<br>
            他のユーザーにスコアを共有することが出来ます。
        </p>
        <p class="space-bottom">
            すべての機能を無料で使用することが出来ますが、オンゲキNETの課金状況によって使用できる機能に制限が掛かります。<br>
            無料版では殆どのデータが得られません。<br>
            オンゲキNETの登録と利用券の購入を済ませてください。
        </p>
        <p class="space-bottom">
            サンプル: <a href="/user/1">作者のスコアページ</a>
        </p>
    </div>
    <div class="box">
        <h3 class="title is-3">利用方法</h3>
        <p class="space-bottom">
            スコアの取得はブックマークレットを使用します。<br>
            詳細な使い方はこちらをご覧ください。
            <ol>
                <li>サイトに登録します。</li>
                <li>オンゲキNETにログインします。</li>
                <li>ブックマークレットを実行します。</li>
                <li>成功すればサイトにスコアが登録されます。</li>
            </ol>
        </p>
    </div>
@endsection
@extends('layouts.app')

@section('title', '使い方')
@section('hero_subtitle', '')
@section('hero_title', '使い方')
@section('sidemark_howto', "is-active")

@section('content')
<div class="container">
    <article class="box">
        <h3 class="title is-3">はじめに</h3>
        <p>
            このサイトではオンゲキNETからデータを取得します。<br>
            まずはオンゲキNETへの会員登録を済ませ、カードの紐づけを行ってください。<br>
            <a href="http://otogame-net.com" target="_blank">詳しくはこちら</a>
        </p>
        <p>
            オンゲキNETの情報を使用する為、有料プランに加入していないと得られる情報に制限が掛かります。<br>
            <b>プレミアムコースに課金しましょう！！</b>
        </p>

        <table class="table is-bordered is-striped">
            <tbody>
                <tr>
                    <th>無料コース</th>
                    <td>基本的なプレイヤー情報のみ</td>
                </tr>
                <tr>
                    <th>スタンダートコース</th>
                    <td>楽曲のスコア キャラクター情報など</td>
                </tr>
                <tr>
                    <th>プレミアムコース</th>
                    <td>レーティング・BP対象曲情報</td>
                </tr>
            </tbody>
        </table>

        <h3 class="title is-3">ブックマークレットの使い方</h3>
        <p>
            どのブラウザでも以下のような流れになると思います。<br>
            詳しくは<b>「ブックマークレット &lt;環境名&gt;」</b>などで調べてみてください。<br>
            以下はPC版Google Chromeでの解説です。
            <ol>
                <li><a href="/bookmarklet" target="_blank">このページ</a>からブックマークレットを取得します。メモ帳などにコピペしておいてください。</li>
                <li>適当なサイトをブックマークに登録します</li>
                <li>右クリック→編集を押します</li>
                <img src="/img/howto_chrome_01.jpg"><br>
                <li>以下のように変更します(名前は何でもok わかりやすく設定しましょう)</li>
                <img src="/img/howto_chrome_02.jpg"><br>
                <li><a href="https://ongeki-net.com">オンゲキNET</a>にログインします</li>
                <li>ホーム画面でさっき登録したブックマークを実行します</li>
            </ol>
        </p>

        <div class="notification is-danger">
            ブックマークレットの使い方は端末やブラウザ等、環境によって利用方法が大きく異なる場合がございます。<br>
            お問い合わせをいただきましてもお答えできない場合がございますので予めご了承ください。
        </div>
    </article>
</div>
@endsection

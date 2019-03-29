@extends('layouts.app')

@section('title', 'ブックマークレットを生成')
@section('hero_subtitle', '')
@section('hero_title', 'ブックマークレットを生成')
@section('sidemark_bookmarklet', "is-active")
@section('additional_footer')
    <script type="text/javascript" src="{{ mix('/js/clipboard.min.js') }}"></script>
    <script>
        new ClipboardJS('.btn');
    </script>
@endsection

@section('content')
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
                このブックマークレットがあれば誰でも<b>あなたとしてスコアを登録することが出来ます。</b><br>
            </p>
            <p>
                もし複数の端末でブックマークレットを使用したい場合は、ブックマークレットを何らかの方法で別のブラウザに渡してください。<br>
                再生成すると以前のブックマークレットは使用できなくなります。
            </p>
        </div>
        <div class="notification is-danger">
            ブックマークレットの使い方は端末やブラウザ等、環境によって利用方法が大きく異なる場合がございます。<br>
            お問い合わせをいただきましてもお答えできない場合がございますので予めご了承ください。
        </div>
    </article>
@endsection

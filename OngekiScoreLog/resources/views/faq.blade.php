@extends('layouts.app')

@section('title', 'よくある質問')
@section('hero_subtitle', 'FAQ')
@section('hero_title', 'よくある質問')
@section('sidemark_faq', "is-active")

@section('content')
    <article class="box">
        <div class="content">
            <h3 id="toc" class="title is-3">よくある質問</h3>
            <ul>
                <li><a data-scroll href="#usage-fee">サービスを利用する上で料金は発生しますか</a></li>
                <li><a data-scroll href="#error">特定の機能が動かない</a></li>
                <li><a data-scroll href="#why-usage-limit">何故レーティング関連にプレミアムコースの加入が必要なのですか</a></li>
                <li><a data-scroll href="#donation">サービスに対してカンパをしたい</a></li>
            </ul>

            <h4 id="usage-fee" class="title is-4">サービスを利用する上で料金は発生しますか</h4>
            <p>
                <b>当サイトが提供するサービスは、全て無料でご利用いただけます。</b><br>
                今後広告を配信する気もありません。
            </p>
            <p>
                但し一部機能を使うには、オンゲキNETのプレミアムプランへの加入が必要です。
            </p>
            <p><a data-scroll href="#toc">▲&nbsp;目次に戻る</a></p>

            <h4 id="error" class="title is-4">特定の機能が動かない</h4>
            <p>
                <b>まずはフッターの連絡先にご報告ください。</b><br>
                発生時間やユーザーID、IPアドレスなどの情報が添えられていると非常に助かります。
            </p>
            <p>
                環境に依存した問題の場合、すぐ対応ができない可能性があります。<br>
                個人運営ではすべての環境に対応することは困難です。ご了承ください。
            </p>
            <p><a data-scroll href="#toc">▲&nbsp;目次に戻る</a></p>

            <h4 id="why-usage-limit" class="title is-4">何故レーティング関連にプレミアムコースの加入が必要なのですか</h4>
            <p>
                大きな理由として<b>「SEGA様の利益に対して妨害をしたくない」</b>ことが理由です。<br>
                例えば「OngekiScoreLogがあるしプレミアムプランは課金しなくてもいいや」というユーザーが出てくる事も考えられるでしょう。<br>
                そのような方が出てくればその分だけ、利益が得られなくなる可能性があります。
            </p>
            <p>
                コンテンツやサービスを継続する上で、収益はとても大切なものです。<br>
                開発者個人としても、できるだけ長くオンゲキが運営されることを願っています。<br>
                <b>この制限に関しましては今後も緩和するつもりはありません。ご理解ください。</b>
            </p>
            <p style="font-size: 0.5em; -webkit-font-smoothing: subpixel-antialiased; -moz-osx-font-smoothing: auto | grayscale;">
                    因みにあまり大きな声では言えませんが、業務妨害で訴えられないようにという意味もあります...
            </p>
            <p>
                制限をどうしても解除したい方も居るでしょう。このサイトを表示するプロジェクトはオープンソースで管理されています。<br>
                ライセンスに違反しない範囲であれば自由に改変してホスティングしてください。
            </p>
            <p><a data-scroll href="#toc">▲&nbsp;目次に戻る</a></p>

            <h4 id="donation" class="title is-4">サービスに対してカンパをしたい</h4>
            <p>
                基本的に<b>このサービスに対するカンパは受け取らない方針です。</b><br>
                その分、カードメーカーのガチャを回してください！
            </p>
            <p><a data-scroll href="#toc">▲&nbsp;目次に戻る</a></p>
        </div>
    </article>
@endsection

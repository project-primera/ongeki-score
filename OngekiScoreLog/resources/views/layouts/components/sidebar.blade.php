<aside class="box">
        {{ $box }}
</aside>
<aside class="box menu">
    <p class="menu-label" id="side_menu">
        メニュー
    </p>
    <ul class="menu-list">
        <li><a href="/" class="@yield('sidemark_top')">トップページ</a></li>
        <li><a href="/howto" class="@yield('sidemark_howto')">使い方</a></li>
        <li><a href="/alluser" class="@yield('sidemark_alluser')">すべてのユーザー</a></li>
        <li><a href="/random" class="@yield('sidemark_random')">ランダムアクセス</a></li>
        <li><a href="/music" class="@yield('sidemark_music')">楽曲リスト/譜面定数</a></li>
    </ul>
    <p class="menu-label">
        ユーザーページ
    </p>
    <ul class="menu-list">
        <li><a href="/mypage">自分のページ</a></li>
        <li><span class="menu-list-dummy">設定</span>
            <ul>
                <li><a href="/bookmarklet" class="@yield('sidemark_bookmarklet')">ブックマークレットを取得する</a></li>
                <li><a href="/setting" class="@yield('sidemark_setting')">設定</a></a></li>
            </ul>
        </li>
    </ul>
    <ul class="menu-list">
        <li><a href="/eula" class="@yield('sidemark_eula')">利用規約 / プライバシーポリシー</a></li>
    </ul>
</aside>
<aside class="box">
    <a class="twitter-timeline" data-lang="ja" data-height="480" href="https://twitter.com/ongeki_score?ref_src=twsrc%5Etfw">Tweets by ongeki_score</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
</aside>
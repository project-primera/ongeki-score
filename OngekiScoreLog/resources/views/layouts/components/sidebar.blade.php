<aside class="box">
        {{ $box }}
</aside>
    <div class="side_sticky">
    <aside class="box menu" id="side_menu">
        <p class="menu-label">メニュー</p>
        <ul class="menu-list">
            <li><a href="/" class="@yield('sidemark_top')">トップページ</a></li>
            <li><a href="/howto" class="@yield('sidemark_howto')">使い方</a></li>
            <li><a href="/user" class="@yield('sidemark_alluser')">すべてのユーザー</a></li>
            <li><a href="/random" class="@yield('sidemark_random')">ランダムアクセス</a></li>
            <li><a href="/music" class="@yield('sidemark_music')">楽曲リスト/譜面定数</a></li>
        </ul>
        @if (Auth::check())
            <p class="menu-label">マイページ</p>
            <ul class="menu-list">
                <li><a href="/mypage" class="@yield('sidemark_mypage_default')">簡易表示</a></li>
                <li><a href="/mypage/details" class="@yield('sidemark_mypage_details')">詳細表示</a></li>
                <li><a href="/mypage/battle" class="@yield('sidemark_mypage_battle')">Battle Score</a></li>
                <li><a href="/mypage/technical" class="@yield('sidemark_mypage_technical')">Technical Score</a></li>
                <li><a href="/mypage/trophy" class="@yield('sidemark_mypage_trophy')">称号</a></li>
                <li><a href="/mypage/rating" class="@yield('sidemark_mypage_rating')">レーティング情報</a></li>
                <li><a href="/mypage/progress" class="@yield('sidemark_mypage_progress')">更新差分</a></li>
            </ul>
            <p class="menu-label">設定</p>
            <ul class="menu-list">
                <li><a href="/bookmarklet" class="@yield('sidemark_bookmarklet')">ブックマークレットを取得する</a></li>
                <li><a href="/setting" class="@yield('sidemark_setting')">その他の設定</a></li>
            </ul>
        @endif
        <ul class="menu-list">
            <li><a href="/eula" class="@yield('sidemark_eula')">利用規約<br>プライバシーポリシー</a></li>
        </ul>
    </aside>
    <aside class="box">
        <div class="twitter-widget-wrapper">
            <a class="twitter-timeline" data-lang="ja" data-height="480" href="https://twitter.com/ongeki_score?ref_src=twsrc%5Etfw">Tweets by ongeki_score</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        </div>
    </aside>
</div>
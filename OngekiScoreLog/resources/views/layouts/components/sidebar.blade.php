<aside class="box">
        {{ $box }}
</aside>
<aside class="box menu">
    <p class="menu-label">
        メニュー
    </p>
    <ul class="menu-list">
        <li><a href="/" class="@yield('sidemark_top')">トップページ</a></li>
        <li><a href="/howto" class="@yield('sidemark_howto')">使い方</a></li>
        <li><a href="/alluser" class="@yield('sidemark_alluser')">すべてのユーザー</a></li>
        <li><a href="/random" class="@yield('sidemark_random')">ランダムアクセス</a></li>
    </ul>
    <p class="menu-label">
        ユーザーページ
    </p>
    <ul class="menu-list">
        <li><a href="/mypage">自分のページ</a></li>
        <li><span class="menu-list-dummy">設定</span>
            <ul>
                <li><a href="/bookmarklet" class="@yield('sidemark_bookmarklet')">ブックマークレットを取得する</a></li>
                <li><span class="menu-list-dummy">公開範囲(未実装)</span></a></li>
            </ul>
        </li>
    </ul>
    <ul class="menu-list">
        <li><a href="#">License</a></li>
    </ul>
</aside>
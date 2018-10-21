<aside class="box">
        {{ $box }}
</aside>
<aside class="box menu">
    <p class="menu-label">
        メニュー
    </p>
    <ul class="menu-list">
        <li><a href="/" class="@yield('sidemark_top')">トップページ</a></li>
        <li><a href="/user/1" class="@yield('sidemark_user')">ユーザー</a></li>
        <li><a href="/howto" class="@yield('sidemark_howto')">使い方</a></li>
        <li><a href="#">ランダムアクセス</a></li>
        <li><a href="#">ランキング</a></li>
    </ul>
    <p class="menu-label">
        ユーザーページ
    </p>
    <ul class="menu-list">
        <li><a href="#">プロフィール</a></li>
        <li><a href="#">設定</a>
            <ul>
                <li><a href="/bookmarklet" class="@yield('sidemark_bookmarklet')">ブックマークレットを取得する</a></li>
                <li><a href="#">公開範囲</a></li>
            </ul>
        </li>
    </ul>
    <ul class="menu-list">
        <li><a href="#">License</a></li>
    </ul>
</aside>
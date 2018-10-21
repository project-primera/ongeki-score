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
        <li><a href="/random">ランダムアクセス</a></li>
        <li><a href="/alluser">すべてのユーザー</a></li>
    </ul>
    <p class="menu-label">
        ユーザーページ
    </p>
    <ul class="menu-list">
        <li><a href="#">自分のページ</a></li>
        <li><span class="menu-list-dummy">設定</span>
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
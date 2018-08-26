<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>OngekiScoreLog - @yield('title')</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.1.2/css/bulma.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
        <link rel="stylesheet" href="/css/style.css">
    </head>

    <body>
        <header class="nav">
            <div class="nav-left">
                <span class="nav-item">
                    <i class="fas fa-chart-line"></i>&nbsp;OngekiScoreLog</span>
            </div>
            <div class="nav-right">
                <span class="nav-item">
                    <div class="control has-addons">
                        <input class="input" type="search" name="search" placeholder="キーワード検索">
                        <a class="button is-info"><i class="fas fa-search"></i>&nbsp;検索</a>
                    </div>
                </span>
                <span class="nav-item">
                    <a href="/login"><i class="fas fa-user"></i>&nbsp;ログイン</a>
                </span>
            </div>
        </header>

        <div class="hero is-info is-bold">
            <div class="hero-body">
                <div class="container">
                    <h2 class="subtitle">タテマエと本心の大乱闘</h2>
                    <h1 class="title">Ａｋｉ．１４２ｓ</h1>
                </div>
            </div>
        </div>
        <section class="level">
            <div class="level-left"></div>
            <div class="level-right">
                <div class="level-item tabs">
                    <ul>
                        <li class="is-active"><a>簡易表示</a></li>
                        <li><a>詳細表示</a></li>
                    </ul>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a class="button is-greylight">フォロー</a>
                </div>
            </div>
        </section>

        <main class="columns">
            <div class="submenu column is-3">
                @component('layouts/components/sidebar')
                    @slot('box')
                        🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣🍣
                    @endslot
                @endcomponent
            </div>
            <div class="column">
                @yield('content')
            </div>

        </main>
        <footer class="footer">
            <div class="container">
                <div class="content has-text-centered">
                    copyright?
                </div>
            </div>
        </footer>
    </body>
</html>
<!DOCTYPE html>
<html lang="ja">
    <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
        <meta charset="utf-8">
        @include('layouts/components/assets/google_analytics')
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>OngekiScoreLog - @yield('title')</title>
        <meta name="description" content="SEGAのアーケード音楽ゲーム「オンゲキ」のスコアを集計し、見やすくソートしたりできる非公式ツールです。他のユーザーにスコアを共有することが出来ます。">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.1.2/css/bulma.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ mix('/css/style.css') }}">
        <!-- favicons -->
        <meta name="msapplication-TileColor" content="#2d88ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
        <link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
        <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="36x36" href="/android-chrome-36x36.png">
        <link rel="icon" type="image/png" sizes="48x48" href="/android-chrome-48x48.png">
        <link rel="icon" type="image/png" sizes="72x72" href="/android-chrome-72x72.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/android-chrome-96x96.png">
        <link rel="icon" type="image/png" sizes="128x128" href="/android-chrome-128x128.png">
        <link rel="icon" type="image/png" sizes="144x144" href="/android-chrome-144x144.png">
        <link rel="icon" type="image/png" sizes="152x152" href="/android-chrome-152x152.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
        <link rel="icon" type="image/png" sizes="256x256" href="/android-chrome-256x256.png">
        <link rel="icon" type="image/png" sizes="384x384" href="/android-chrome-384x384.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
        <link rel="icon" type="image/png" sizes="36x36" href="/icon-36x36.png">
        <link rel="icon" type="image/png" sizes="48x48" href="/icon-48x48.png">
        <link rel="icon" type="image/png" sizes="72x72" href="/icon-72x72.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/icon-96x96.png">
        <link rel="icon" type="image/png" sizes="128x128" href="/icon-128x128.png">
        <link rel="icon" type="image/png" sizes="144x144" href="/icon-144x144.png">
        <link rel="icon" type="image/png" sizes="152x152" href="/icon-152x152.png">
        <link rel="icon" type="image/png" sizes="160x160" href="/icon-160x160.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="196x196" href="/icon-196x196.png">
        <link rel="icon" type="image/png" sizes="256x256" href="/icon-256x256.png">
        <link rel="icon" type="image/png" sizes="384x384" href="/icon-384x384.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/icon-512x512.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png">
        <link rel="icon" type="image/png" sizes="24x24" href="/icon-24x24.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png">
        <link rel="manifest" href="/manifest.json">
        <!-- Open Graph protocol -->
        <meta property="og:title" content="@yield('title') - OngekiScoreLog" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{(empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]}}" />
        <meta property="og:image" content="https://ongeki-score.net/icon-512x512.png" />
        <meta property="og:site_name" content="OngekiScoreLog" />
        <meta property="og:description" content="SEGAのアーケード音楽ゲーム「オンゲキ」のスコアを集計し、見やすくソートしたりできる非公式ツールです。他のユーザーにスコアを共有することが出来ます。" />
        <meta name="twitter:card" content="summary"/>
        <meta name="twitter:site" content="@ongeki_score" />
        <!--<meta property="fb:app_id" content="***************" />-->
    </head>

    <body>
        <a href="#side_menu" class="round_button is-hidden-tablet" data-scroll><i class="fas fa-bars"></i></a>
        <header class="nav">
            <div class="nav-left">
                <span class="nav-item">
                    <a href="/"><i class="fas fa-chart-line"></i>&nbsp;ongeki-score.net</span></a>
            </div>
            <div class="nav-right">
                <span class="nav-item">
                    <!-- <div class="control has-addons">
                        <input class="input" type="search" name="search" placeholder="キーワード検索">
                        <a class="button is-info"><i class="fas fa-search"></i>&nbsp;検索</a>
                    </div> -->
                </span>
                <span class="nav-item">
                    @component('layouts/components/login_button')
                    @endcomponent
                </span>
            </div>
        </header>

        <aside>
            <div class="hero is-info is-bold">
                <div class="hero-body">
                    <div class="container">
                        <h2 class="subtitle">@yield('hero_subtitle')</h2>
                        <h1 class="title">@yield('hero_title')</h1>
                    </div>
                </div>
            </div>
            <section class="level">
                <div class="level-left"></div>
                <div class="level-right">
                    <div class="level-item tabs">
                        <ul>
                            @section('submenu')
                            @show
                        </ul>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        &nbsp;
                    </div>
                </div>
            </section>
        </aside>

        <main class="columns">
            <div class="column">
                @include('layouts/components/assets/share_buttons')
                @yield('content')
            </div>
            <div class="submenu column is-3">
                @component('layouts/components/sidebar')
                    @slot('box')
                        @php
                            $applicationVersion =  new App\ApplicationVersion();
                            $version = $applicationVersion->getLatestVersion();
                            echo '<p class="menu-label">更新履歴</p><p>';
                            echo '<b>' . (isset($version[0]->name) ? $version[0]->name : "") . '</b><br>';
                            echo (isset($version[0]->tag_name) ? $version[0]->tag_name : "") . (isset($version[0]->published_at) ? date('(Y/m/d)', strtotime($version[0]->published_at)) : "") . '<br>';
                            echo (isset($version[0]->body) ? nl2br($version[0]->body) : "") . '</p>';
                        @endphp
                    <a href="/changelog">過去の更新</a>
                    @endslot
                @endcomponent
            </div>
        </main>

        <footer>
            This site is powered by <a href="https://github.com/Slime-hatena/ProjectPrimera" target="_blank">ProjectPrimera</a> licensed under the <a href="https://github.com/Slime-hatena/ProjectPrimera/blob/master/LICENSE" target="_blank">MIT</a>.<br>
            お問い合わせ:&nbsp;<a href="https://twitter.com/ongeki_score" target="blank">Twitter@ongeki_score</a> / <a href="mailto:info&#64;ongeki-score.net">
                info&nbsp;(at)&nbsp;ongeki-score.net
             </a>
        </footer>

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha256-3edrmyuQ0w65f8gfBsqowzjJe2iM6n0nKciPUp8y+7E=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="{{ mix('/js/list.min.js') }}"></script>
        <script type="text/javascript" src="{{ mix('/js/sweet-scroll.min.js') }}"></script>
        <script type="text/javascript" src="{{ mix('/js/app.js') }}"></script>
        @section('additional_footer')
        @show
    </body>
</html>

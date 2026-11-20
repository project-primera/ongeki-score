@extends('layouts.app')

@section('title', $status[0]->name . "のレーティング情報")
@section('hero_subtitle', $status[0]->trophy)
@section('hero_title', $status[0]->name)
@section('additional_footer')
<script type="text/javascript" src="/js/html2canvas.min.js"></script>
@vite('resources/assets/js/userProgress.ts')
@endsection
@if(isset($sidemark) && !is_null($sidemark))
@section($sidemark, "is-active")
@endif

@section('submenu')
<li><a href="/user/{{$id}}">簡易</a></li>
<li><a href="/user/{{$id}}/details">詳細</a></li>
<li><a href="/user/{{$id}}/technical">Technical</a></li>
<li><a href="/user/{{$id}}/battlescore">Battle</a></li>
<li><a href="/user/{{$id}}/overdamage">OverDamage</a></li>
<li><a href="/user/{{$id}}/trophy">称号</a></li>
<li class="is-active"><a href="/user/{{$id}}/rating">Rating</a></li>
<li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection


@section('content')
<article id="rating_statistics" class="box">
    <h3 class="title is-3">レーティング対象曲の統計</h3>
    <p>
        ■統計&nbsp;/&nbsp;<a data-scroll href="#rating_new">▼新曲枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_old">▼ベスト枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_platinum">▼プラチナスコア枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_formula">▼計算式</a>
    </p>
    <div class="table_wrap scalable">
        <table class="table is-striped">
            <thead>
                <tr>
                    <th></th>
                    <th>平均値</th>
                    <th>対象曲数</th>
                    <th>Max</th>
                    <th>Min</th>
                    <th>合計レート値</th>
                    <th>レート寄与値</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>新曲枠</td>
                    <td class="table-number">{{$statistics->newRatingAverage}}</td>
                    <td class="table-number">{{$statistics->newBestRatingCount}}</td>
                    <td class="table-number">{{$statistics->newBestRatingTop}}</td>
                    <td class="table-number">{{$statistics->newBestRatingMin}}</td>
                    <td class="table-number">{{$statistics->newBestRatingTotal}}</td>
                    <td class="table-number">{{$statistics->newRatingContribute}}</td>
                </tr>
                <tr>
                    <td>ベスト枠</td>
                    <td class="table-number">{{$statistics->oldBestRatingAverage}}</td>
                    <td class="table-number">{{$statistics->oldBestRatingCount}}</td>
                    <td class="table-number">{{$statistics->oldBestRatingTop}}</td>
                    <td class="table-number">{{$statistics->oldBestRatingMin}}</td>
                    <td class="table-number">{{$statistics->oldBestRatingTotal}}</td>
                    <td class="table-number">{{$statistics->oldBestRatingContribute}}</td>
                </tr>
                <tr>
                    <td>プラチナスコア枠</td>
                    <td class="table-number">{{$statistics->platinumRatingAverage}}</td>
                    <td class="table-number">{{$statistics->platinumRatingCount}}</td>
                    <td class="table-number">{{$statistics->platinumRatingTop}}</td>
                    <td class="table-number">{{$statistics->platinumRatingMin}}</td>
                    <td class="table-number">{{$statistics->platinumRatingTotal}}</td>
                    <td class="table-number">{{$statistics->platinumRatingContribute}}</td>
                </tr>
            </tbody>
        </table>
    </div>
    </p>
    <p>
        <span style="font-size: 1.2em">RATING: {{$statistics->ratingCalc}}</span><span style="font-size: .9em"> = ({{$statistics->newRatingContribute}} + {{$statistics->oldBestRatingContribute}} + {{$statistics->platinumRatingContribute}})</span>
    </p>
    @foreach ($messages as $message)
    <p>
        <span style="color: #dd3333">{{$message}}</span>
    </p>
    @endforeach
</article>

<article id="rating_new" class="box">
    <h3 class="title is-3">新曲枠</h3>
    <p>
        <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;■新曲枠&nbsp;/&nbsp;<a data-scroll href="#rating_old">▼ベスト枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_platinum">▼プラチナスコア枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_formula">▼計算式</a><br>
        現在のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->newBestRatingCount}}曲が選出されます。
    </p>
    @component('layouts/components/user_rating/rating_best_table', ['array' => $newScore, 'statistics' => $statistics, 'start' => 0, 'targetCount' => $statistics->newBestRatingCount, 'end' => count($newScore), 'id' => $id])
    @endcomponent
</article>

<article id="rating_old" class="box">
    <h3 class="title is-3">ベスト枠</h3>
    <p>
        <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;<a data-scroll href="#rating_new">▲新曲枠</a>&nbsp;/&nbsp;■ベスト枠&nbsp;/&nbsp;<a data-scroll href="#rating_platinum">▼プラチナスコア枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_formula">▼計算式</a><br>
        過去のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->oldBestRatingCount}}曲が選出されます。
    </p>
    @component('layouts/components/user_rating/rating_best_table', ['array' => $oldScore, 'statistics' => $statistics, 'start' => 0, 'targetCount' => $statistics->oldBestRatingCount, 'end' => count($oldScore), 'id' => $id])
    @endcomponent
</article>

<article id="rating_platinum" class="box">
    <h3 class="title is-3">プラチナスコア枠</h3>
    <p>
        <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;<a data-scroll href="#rating_new">▲新曲枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_old">▲ベスト枠</a>&nbsp;/&nbsp;■プラチナスコア枠&nbsp;/&nbsp;<a data-scroll href="#rating_formula">▼計算式</a><br>
        星の数と譜面定数から計算されたレート値の高い{{$statistics->platinumRatingCount}}曲が選出されます。<br>
    </p>
    <h4 class="title is-4">レーティング対象曲</h4>
    <div class="table_wrap scalable">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th><abbr title="Difficulty">Dif</abbr></th>
                    <th>Lv</th>
                    <th><abbr title="Platinum Score">PS</abbr></th>
                    <th>☆</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th><abbr title="Difficulty">Dif</abbr></th>
                    <th>Lv</th>
                    <th><abbr title="Platinum Score">PS</abbr></th>
                    <th>☆</th>
                    <th>Rate</th>
                </tr>
            </tfoot>
            <tbody>
                @for ($i = 0; $i < $statistics->platinumRatingCount; $i++)
                    <tr>
                        <td>{{$i + 1}}</td>
                        @if (array_key_exists($i, $platinumMusic) && array_key_exists('song_id', $platinumMusic[$i]))
                        <td class="sort_title"><a href="{{url("/user/" . $id . "/music/" . $platinumMusic[$i]['song_id'] . "/" . strtolower($platinumMusic[$i]['difficulty_str']))}}">{{$platinumMusic[$i]['title']}}</a></td>
                        @else
                        <td class="sort_title">{{$platinumMusic[$i]['title']}}</td>
                        @endif
                        <td>{{substr($platinumMusic[$i]['difficulty_str'], 0, 3)}}</td>
                        <td>{!!$platinumMusic[$i]['level_str']!!}</td>
                        <td>{{number_format($platinumMusic[$i]['platinum_score'])}}</td>
                        <td>{{$platinumMusic[$i]['star']}}</td>
                        <td>{!!$platinumMusic[$i]['ratingValue']!!}</td>
                    </tr>
                    @endfor
            </tbody>
        </table>
    </div>
</article>

<article id="rating_formula" class="box">
    <h3 class="title is-3">レーティング計算式</h3>
    <p>
        <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;<a data-scroll href="#rating_new">▲新曲枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_old">▲ベスト枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_platinum">▲プラチナスコア枠</a>&nbsp;/&nbsp;■計算式<br>
        <br>当サイトで採用しているレーティング計算式は以下の通りとなっています。
    </p>

    <h4 class="title is-4">前提</h4>
    <p>
        計算式は公式には非公開のため、有志の検証にもとづく次の式で計算しています。
        <br>どの式も、計算結果の小数第4位以下を切り捨てます。
        <br>Lv0の譜面やソロverはレーティング枠の対象外です。
    </p>

    <h4 class="title is-4">譜面定数</h4>
    <p>
        譜面定数は、レーティング計算に使う譜面ごとの内部難易度です。
        <br>筐体では確認できず、有志の検証にもとづいています。
        <br>当ツールでは譜面定数が未登録の場合、レベルからの推定値で計算されます（13 は 13.0、13+ は 13.7 など）。
    </p>

    <h4 class="title is-4">RATING</h4>
    <p>
        RATING = 新曲枠 + ベスト枠 + プラチナスコア枠（各枠のレート寄与値の合計）
    </p>
    <div class="table_wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>枠</th>
                    <th>対象</th>
                    <th>レート寄与値</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>新曲枠</td>
                    <td>現在のバージョンで追加された譜面のうち、単曲レーティング値の高い10曲</td>
                    <td>1曲ごとに「単曲レーティング値 ÷ 5」を切り捨て、その合計 ÷ 10</td>
                </tr>
                <tr>
                    <td>ベスト枠</td>
                    <td>過去のバージョンで追加された譜面のうち、単曲レーティング値の高い50曲</td>
                    <td>単曲レーティング値の合計 ÷ 50</td>
                </tr>
                <tr>
                    <td>プラチナスコア枠</td>
                    <td>プラチナスコアの単曲レーティング値の高い50曲</td>
                    <td>単曲レーティング値の合計 ÷ 50</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p>
        曲数が足りなくても、10・50・50 で割ります。<br>
        同じ曲でも、難易度が違えば別の曲として数えます。
    </p>

    <h4 class="title is-4">単曲レーティング値（新曲枠・ベスト枠）</h4>
    <p>
        単曲レーティング値 = 基礎値 + ランク補正 + ランプ補正
    </p>
    <h5 class="title is-5">基礎値</h5>
    <div class="table_wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>テクニカルスコア</th>
                    <th>下限の値</th>
                    <th>下限からの加算</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1,010,000</td>
                    <td>譜面定数 + 2.000</td>
                    <td>－</td>
                </tr>
                <tr>
                    <td>1,007,500〜1,009,999</td>
                    <td>譜面定数 + 1.750</td>
                    <td>10点ごとに +0.001</td>
                </tr>
                <tr>
                    <td>1,000,000〜1,007,499</td>
                    <td>譜面定数 + 1.250</td>
                    <td>15点ごとに +0.001</td>
                </tr>
                <tr>
                    <td>990,000〜999,999</td>
                    <td>譜面定数 + 0.750</td>
                    <td>20点ごとに +0.001</td>
                </tr>
                <tr>
                    <td>970,000〜989,999</td>
                    <td>譜面定数</td>
                    <td>（スコア − 970,000）× 0.003 ÷ 80</td>
                </tr>
                <tr>
                    <td>900,000〜969,999</td>
                    <td>譜面定数 − 4.000</td>
                    <td>（スコア − 900,000）× 0.002 ÷ 35</td>
                </tr>
                <tr>
                    <td>800,000〜899,999</td>
                    <td>譜面定数 − 6.000</td>
                    <td>50点ごとに +0.001</td>
                </tr>
                <tr>
                    <td>500,000〜799,999</td>
                    <td>0</td>
                    <td>（譜面定数 − 6.000）×（スコア − 500,000）÷ 300,000</td>
                </tr>
                <tr>
                    <td>500,000未満</td>
                    <td>0</td>
                    <td>－</td>
                </tr>
            </tbody>
        </table>
    </div>
    <h5 class="title is-5">補正</h5>
    <p>
        ランク補正: SSS+（1,007,500点以上）+0.300 ／ SSS（1,000,000点以上）+0.200 ／ SS（990,000点以上）+0.100<br>
        ランプ補正: ALL BREAK+ +0.350 ／ ALL BREAK +0.300 ／ FULL COMBO +0.100<br>
        FULL BELL は、ランプ補正とは別に +0.050 です。
    </p>
    <p>
        例: 1,010,000点・ALL BREAK+・FULL BELL なら、譜面定数 + 2.000 + 0.300 + 0.350 + 0.050 = 譜面定数 + 2.700
    </p>

    <h4 class="title is-4">単曲レーティング値（プラチナスコア枠）</h4>
    <p>
        単曲レーティング値 = 譜面定数 × 譜面定数 × ☆の数（最大5）÷ 1,000
    </p>
</article>
@endsection

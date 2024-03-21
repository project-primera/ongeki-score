@extends('layouts.app')

@section('title', $status[0]->name . "のレーティング情報")
@section('hero_subtitle', $status[0]->trophy)
@section('hero_title', $status[0]->name)
@section('additional_footer')
    <script type="text/javascript" src="/js/html2canvas.min.js"></script>
    <script type="text/javascript" src="{{ mix('/js/userProgress.js') }}"></script>
@endsection
@if(isset($sidemark) && !is_null($sidemark))
    @section($sidemark, "is-active")
@endif

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/battle">Battle</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li><a href="/user/{{$id}}/trophy">称号</a></li>
    <li class="is-active"><a href="/user/{{$id}}/rating">Rating</a></li>
    <li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection


@section('content')
    <article id="rating_statistics" class="box">
        <h3 class="title is-3">レーティング対象曲の統計</h3>
        <p>
            ■統計&nbsp;/&nbsp;<a data-scroll href="#rating_new">▼新曲枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_old">▼ベスト枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_recent">▼リーセント枠</a>
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
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>新曲枠</td>
                        <td>{{sprintf("%.2f", floor($statistics->newBestRatingTotal / $statistics->newBestRatingCount * 100) / 100)}}</td>
                        <td>{{$statistics->newBestRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->newBestRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->newBestRatingMin)}}</td>
                    </tr>
                    <tr>
                        <td>ベスト枠</td>
                        <td>{{sprintf("%.2f", floor($statistics->oldBestRatingTotal / $statistics->oldBestRatingCount * 100) / 100)}}</td>
                        <td>{{$statistics->oldBestRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->oldBestRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->oldBestRatingMin)}}</td>
                    </tr>
                    <tr>
                        <td>リーセント枠</td>
                        <td>{{sprintf("%.2f", floor($statistics->recentRatingTotal / $statistics->recentRatingCount * 100) / 100)}}</td>
                        <td>{{$statistics->recentRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->recentRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->recentRatingMin)}}</td>
                    </tr>
                    <tr>
                        <td>全対象曲</td>
                        <td>{{sprintf("%.2f", floor($statistics->totalRatingTotal / $statistics->totalRatingCount * 100) / 100)}}</td>
                        <td>{{$statistics->totalRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->totalRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->totalRatingMin)}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>
            <span class="subtitle is-5">到達可能レーティング: {{sprintf("%.4f",floor($statistics->maxRatingTotal / $statistics->totalRatingCount * 10000) / 10000)}}</span>
        </p>
            現在のスコアデータのうち、最大レート({{$statistics->potentialRatingTop}})の曲でリーセント枠を全て埋めたときの値です。<br>
            なお、枠にAAA以下の曲が含まれる場合、計算が正確でない場合があります。
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
            <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;■新曲枠&nbsp;/&nbsp;<a data-scroll href="#rating_old">▼ベスト枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_recent">▼リーセント枠</a><br>
            現在のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->newBestRatingCount}}曲が選出されます。
        </p>
        @component('layouts/components/user_rating/rating_best_table', ['array' => $newScore, 'statistics' => $statistics, 'start' => 0, 'targetCount' => $statistics->newBestRatingCount, 'end' => count($newScore), 'id' => $id])
        @endcomponent
    </article>

    <article id="rating_old" class="box">
        <h3 class="title is-3">ベスト枠</h3>
        <p>
            <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;<a data-scroll href="#rating_new">▲新曲枠</a>&nbsp;/&nbsp;■ベスト枠&nbsp;/&nbsp;<a data-scroll href="#rating_recent">▼リーセント枠</a><br>
            過去のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->oldBestRatingCount}}曲が選出されます。
        </p>
        @component('layouts/components/user_rating/rating_best_table', ['array' => $oldScore, 'statistics' => $statistics, 'start' => 0, 'targetCount' => $statistics->oldBestRatingCount, 'end' => count($oldScore), 'id' => $id])
        @endcomponent
    </article>

    <article id="rating_recent" class="box">
        <h3 class="title is-3">リーセント枠</h3>
        <p>
            <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;<a data-scroll href="#rating_new">▲新曲枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_old">▲ベスト枠</a>&nbsp;/&nbsp;■リーセント枠<br>
            過去にプレイした30曲(?)のうち、レート値が高い{{$statistics->recentRatingCount}}曲が選出されます。<br>
            ランクSSS(?)以上を取得し、現在のリーセント枠の最下位よりもレート値が低い場合はリーセント枠に含まれません。
        </p>
        <h4 class="title is-4">レーティング対象曲</h4>
        <div class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>最大レートとの差</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>最大レートとの差</th>
                    </tr>
                </tfoot>
                <tbody>
                    @for ($i = 0; $i < $statistics->recentRatingCount; $i++)
                        <tr>
                            @if (array_key_exists($i, $recentScore) && array_key_exists('song_id', $recentScore[$i]))
                                <td class="sort_title"><a href="{{url("/user/" . $id . "/music/" . $recentScore[$i]['song_id'] . "/" . strtolower($recentScore[$i]['difficulty_str']))}}">{{$recentScore[$i]['title']}}</a></td>
                            @else
                                <td class="sort_title">{{$recentScore[$i]['title']}}</td>
                            @endif
                            <td>{{substr($recentScore[$i]['difficulty_str'], 0, 3)}}</td>
                            <td>{{$recentScore[$i]['level_str']}}</td>
                            <td>{{number_format($recentScore[$i]['technical_score'])}}</td>
                            <td>{!!$recentScore[$i]['ratingValue']!!}</td>
                            <td>{{sprintf("%.2f", $recentScore[$i]['rawRatingValue'] - $statistics->totalRatingTop)}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </article>
@endsection

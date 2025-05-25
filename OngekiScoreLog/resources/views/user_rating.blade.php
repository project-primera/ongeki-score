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
            ■統計&nbsp;/&nbsp;<a data-scroll href="#rating_new">▼新曲枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_old">▼ベスト枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_platinum">▼プラチナスコア枠</a>
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
                        <td class="table-number">{{sprintf("%.3f", $statistics->newRatingAverage)}}</td>
                        <td class="table-number">{{$statistics->newBestRatingCount}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->newBestRatingTop)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->newBestRatingMin)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->newBestRatingTotal)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->newRatingContribute)}}</td>
                    </tr>
                    <tr>
                        <td>ベスト枠</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->oldBestRatingAverage)}}</td>
                        <td class="table-number">{{$statistics->oldBestRatingCount}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->oldBestRatingTop)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->oldBestRatingMin)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->oldBestRatingTotal)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->oldBestRatingContribute)}}</td>
                    </tr>
                    <!-- <tr>
                        <td>レート対象曲平均</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->totalRatingAverage)}}</td>
                        <td class="table-number">{{$statistics->totalRatingCount}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->totalRatingTop)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->totalRatingMin)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->totalRatingTotal)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->totalRatingContribute)}}</td>
                    </tr> -->
                    <tr>
                        <td>プラチナスコア枠</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->platinumRatingAverage)}}</td>
                        <td class="table-number">{{$statistics->platinumRatingCount}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->platinumRatingTop)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->platinumRatingMin)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->platinumRatingTotal)}}</td>
                        <td class="table-number">{{sprintf("%.3f", $statistics->platinumRatingContribute)}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </p>
        <p>
            <span style="font-size: 1.2em">RATING: {{sprintf("%.3f", $statistics->ratingCalc)}}</span><span style="font-size: .9em"> = ({{$statistics->newRatingContribute}} + {{sprintf("%.3f", $statistics->oldBestRatingContribute)}} + {{sprintf("%.3f", $statistics->platinumRatingContribute)}})</span>
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
            <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;■新曲枠&nbsp;/&nbsp;<a data-scroll href="#rating_old">▼ベスト枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_platinum">▼プラチナスコア枠</a><br>
            現在のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->newBestRatingCount}}曲が選出されます。
        </p>
        @component('layouts/components/user_rating/rating_new_table', ['array' => $newScore, 'statistics' => $statistics, 'start' => 0, 'targetCount' => $statistics->newBestRatingCount, 'end' => count($newScore), 'id' => $id])
        @endcomponent
    </article>

    <article id="rating_old" class="box">
        <h3 class="title is-3">ベスト枠</h3>
        <p>
            <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;<a data-scroll href="#rating_new">▲新曲枠</a>&nbsp;/&nbsp;■ベスト枠&nbsp;/&nbsp;<a data-scroll href="#rating_platinum">▼プラチナスコア枠</a><br>
            過去のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->oldBestRatingCount}}曲が選出されます。
        </p>
        @component('layouts/components/user_rating/rating_best_table', ['array' => $oldScore, 'statistics' => $statistics, 'start' => 0, 'targetCount' => $statistics->oldBestRatingCount, 'end' => count($oldScore), 'id' => $id])
        @endcomponent
    </article>

    <article id="rating_platinum" class="box">
        <h3 class="title is-3">プラチナスコア枠</h3>
        <p>
            <a data-scroll href="#rating_statistics">▲統計</a>&nbsp;/&nbsp;<a data-scroll href="#rating_new">▲新曲枠</a>&nbsp;/&nbsp;<a data-scroll href="#rating_old">▲ベスト枠</a>&nbsp;/&nbsp;■プラチナスコア枠<br>
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
    <!-- <article id="rating_platinum" class="box">
    <h3 class="title is-3">レーティング計算式</h3>
    <h4>新曲枠 / ベスト枠</h4>
    <p>

    </p>
    <h4>プラチナスコア枠</h4>
    <p>

    </p>
    </article> -->
@endsection

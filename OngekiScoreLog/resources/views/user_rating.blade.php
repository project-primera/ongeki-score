@extends('layouts.app')

@section('title', $status[0]->name . "のレーティング情報")
@section('hero_subtitle', $status[0]->trophy)
@section('hero_title', $status[0]->name)
@section('additional_footer')
    <script type="text/javascript" src="/js/html2canvas.min.js"></script>
    <script type="text/javascript" src="{{ mix('/js/userProgress.js') }}"></script>
@endsection
@if(!is_null($sidemark))
    @section($sidemark, "is-active")
@endif

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/battle">Battle</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li class="is-active"><a href="/user/{{$id}}/rating">Rating</a></li>
    <li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection


@section('content')
    <article class="box">
        <h3 class="title is-3">レーティング対象曲の内訳</h3>
        <div class="table_wrap scalable">
            <table class="table is-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>平均値</th>
                        <th>合計値</th>
                        <th>対象曲数</th>
                        <th>Max</th>
                        <th>Min</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>新曲枠</td>
                        <td>{{sprintf("%.2f", $statistics->newBestRatingTotal / $statistics->newBestRatingCount)}}</td>
                        <td>{{sprintf("%.2f", $statistics->newBestRatingTotal)}}</td>
                        <td>{{$statistics->newBestRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->newBestRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->newBestRatingMin)}}</td>
                    </tr>
                    <tr>
                        <td>ベスト枠</td>
                        <td>{{sprintf("%.2f", $statistics->oldBestRatingTotal / $statistics->oldBestRatingCount)}}</td>
                        <td>{{sprintf("%.2f", $statistics->oldBestRatingTotal)}}</td>
                        <td>{{$statistics->oldBestRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->oldBestRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->oldBestRatingMin)}}</td>
                    </tr>
                    <tr>
                        <td>リーセント枠</td>
                        <td>{{sprintf("%.2f", $statistics->recentRatingTotal / $statistics->recentRatingCount)}}</td>
                        <td>{{sprintf("%.2f", $statistics->recentRatingTotal)}}</td>
                        <td>{{$statistics->recentRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->recentRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->recentRatingMin)}}</td>
                    </tr>
                    <tr>
                        <td>全対象曲</td>
                        <td>{{sprintf("%.2f", $statistics->totalRatingTotal / $statistics->totalRatingCount)}}</td>
                        <td>{{sprintf("%.2f", $statistics->totalRatingTotal)}}</td>
                        <td>{{$statistics->totalRatingCount}}</td>
                        <td>{{sprintf("%.2f", $statistics->totalRatingTop)}}</td>
                        <td>{{sprintf("%.2f", $statistics->totalRatingMin)}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>
    <article class="box">
        <h3 class="title is-3">新曲枠 レーティング対象曲</h3>
        現在のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->newBestRatingCount}}曲が選出されます。
        <div class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </tfoot>
                <tbody>
                    @for ($i = 0; $i < $statistics->newBestRatingCount; $i++)
                        <tr>
                            <td>{{$newScore[$i]->title}}</td>
                            <td>{{substr($newScore[$i]->difficulty_str, 0, 3)}}</td>
                            <td>{{$newScore[$i]->level_str}}</td>
                            <td>{{number_format($newScore[$i]->technical_high_score)}}</td>
                            <td>{!!$newScore[$i]->ratingValue!!}</td>
                            <td>{{date('Y-m-d', strtotime($newScore[$i]->updated_at))}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </article>
    <article class="box">
        <h3 class="title is-3">ベスト枠 レーティング対象曲</h3>
        過去のバージョンに追加された楽曲のうち、テクニカルハイスコアから算出されたレート値が高い{{$statistics->oldBestRatingCount}}曲が選出されます。
        <div class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </tfoot>
                <tbody>
                    @for ($i = 0; $i < $statistics->oldBestRatingCount; $i++)
                        <tr>
                            <td>{{$oldScore[$i]->title}}</td>
                            <td>{{substr($oldScore[$i]->difficulty_str, 0, 3)}}</td>
                            <td>{{$oldScore[$i]->level_str}}</td>
                            <td>{{number_format($oldScore[$i]->technical_high_score)}}</td>
                            <td>{!!$oldScore[$i]->ratingValue!!}</td>
                            <td>{{date('Y-m-d', strtotime($oldScore[$i]->updated_at))}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </article>
    <article class="box">
        <h3 class="title is-3">リーセント枠 レーティング対象曲</h3>
        過去にプレイした30曲(?)のうち、レート値が高い{{$statistics->recentRatingCount}}曲が選出されます。<br>
        ランクSSS(?)以上を取得し、現在のリーセント枠の最下位よりもレート値が低い場合はリーセント枠に含まれません。
        <div class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </tfoot>
                <tbody>
                    @for ($i = 0; $i < $statistics->recentRatingCount; $i++)
                        <tr>
                            <td>{{$recentScore[$i]['title']}}</td>
                            <td>{{substr($recentScore[$i]['difficulty_str'], 0, 3)}}</td>
                            <td>{{$recentScore[$i]['level_str']}}</td>
                            <td>{{number_format($recentScore[$i]['technical_score'])}}</td>
                            <td>{!!$recentScore[$i]['ratingValue']!!}</td>
                            <td>{{date('Y-m-d', strtotime($recentScore[$i]['updated_at']))}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </article>
@endsection
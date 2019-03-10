@extends('layouts.app')

@section('title', $status[0]->name . "の更新差分")
@section('hero_subtitle', $date['old'] . " → " . $date['new'])
@section('hero_title', $status[0]->name . "の更新差分")
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
    <li><a href="/user/{{$id}}/rating">Rating</a></li>
    <li class="is-active"><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection

@section('content')
    <article class="box">
        {!!$display['screenName']!!}
        <hr>
        <div class="user-progress">
            <div class="info">
                <div class="left">
                    <span class="update">Update&nbsp;{{$date['new']}}</span>
                </div>
                <div class="right">
                    <span class="site-name">{{config('app.name')}}</span>&nbsp;
                    <span class="version">({{$version}})</span>&nbsp;
                    <span class="url">{{config('app.url')}}</span>
                </div>
            </div>

            <div class="notification">
                    <span class="title is-5">{{$status[0]->trophy}}</span><br>
                <span class="title is-5">Lv.{{$status[0]->level}}&nbsp;/&nbsp;
                    BP: {{$status[0]->battle_point}}&nbsp;/&nbsp;
                    Rate: {{$status[0]->rating}}&nbsp;(MAX:&nbsp;{{$status[0]->rating_max}})</span><br>
                <span class="title is-3">{{$status[0]->name}}</span>
            </div>

            <table class="table is-narrow user-progress-total-table">
                <thead>
                    <tr>
                        <th>Difficulty</th>
                        <th colspan="2">Battle Score</th>
                        <th colspan="2">Technical Score</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total</td>
                        <td class="right">{{number_format($score['new']['Total']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Total']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Total']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Total']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Total']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Total']['technical_high_score'])}}</td>
                    </tr>
                    <tr>
                        <td>Basic</td>
                        <td class="right">{{number_format($score['new']['Basic']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Basic']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Basic']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Basic']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Basic']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Basic']['technical_high_score'])}}</td>
                    </tr>
                    <tr>
                        <td>Advanced</td>
                        <td class="right">{{number_format($score['new']['Advanced']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Advanced']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Advanced']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Advanced']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Advanced']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Advanced']['technical_high_score'])}}</td>
                    </tr>
                    <tr>
                        <td>Expert</td>
                        <td class="right">{{number_format($score['new']['Expert']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Expert']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Expert']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Expert']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Expert']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Expert']['technical_high_score'])}}</td>
                    </tr>
                    <tr>
                        <td>Master</td>
                        <td class="right">{{number_format($score['new']['Master']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Master']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Master']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Master']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Master']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Master']['technical_high_score'])}}</td>
                    </tr>
                    <tr>
                        <td>Lunatic</td>
                        <td class="right">{{number_format($score['new']['Lunatic']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Lunatic']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Lunatic']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Lunatic']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Lunatic']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Lunatic']['technical_high_score'])}}</td>
                    </tr>
                </tbody>
            </table>

            @php
                // 最初はヘッダー部分があるのでその分の幅を用意
                $count = 3;
            @endphp

            <div class="music">
                @foreach ($progress as $music => $temp)
                    @foreach ($temp as $difficulty => $value)
                        <div class="node">
                            <div class="song-info">
                                <span class="difficulty {{$value['new']->difficulty_str}}">[{{mb_strimwidth($value['new']->difficulty_str, 0, 3, "", "UTF-8")}}]</span>&nbsp;<span class="song-level">Lv.{{$value['new']->level_str}}</span><span class="song-title">{{$value['new']->title}}</span>
                            </div>
                            <div class="score-info">
                                <span class="score-title">Battle Score</span>
                                <span class="score">{{number_format($value['new']->battle_high_score)}}</span>
                                <span class="difference">{{$value['difference']['battle_high_score']}}</span>
                                <br>

                                <span class="score-title">Technical Score</span>
                                <span class="score">{{number_format($value['new']->technical_high_score)}}</span>
                                <span class="difference">{{$value['difference']['technical_high_score']}}</span>
                                <span class="score-rank {{$value['difference']['is_update_technical_high_score_rank']}}">{{$value['difference']['technical_high_score_rank']}}</span>
                                <br>

                                <span class="score-title">Over Damage</span><span class="score">{{$value['new']->over_damage_high_score}}%</span><span class="difference">{{$value['difference']['over_damage_high_score']}}</span>
                                <span class="score-rank {{$value['difference']['is_update_over_damage_high_score_rank']}}">{{$value['difference']['over_damage_high_score_rank']}}</span>
                            </div>
                            <div class="lamp-info">
                                    <span class="tag {{$value['difference']['old-lamp-is-fb']}}">FB</span>
                                    <span class="tag {{$value['difference']['old-lamp-is-fc']}}">FC</span>
                                    <span class="tag {{$value['difference']['old-lamp-is-ab']}}">AB</span>
                                    →
                                    <span class="tag {{$value['difference']['new-lamp-is-fb']}}">FB</span>
                                    <span class="tag {{$value['difference']['new-lamp-is-fc']}}">FC</span>
                                    <span class="tag {{$value['difference']['new-lamp-is-ab']}}">AB</span>
                            </div>
                        </div>
                        @if (++$count < 9)
                            <hr>
                        @else
                            </div>
                            </div>
                            <div class="user-progress">
                            <div class="music">
                            @php
                                $count = 0;   
                            @endphp
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    </article>
@endsection
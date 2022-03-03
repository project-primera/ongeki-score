@extends('layouts.app')

@section('title', $status[0]->name . "の更新差分")
@section('hero_subtitle', $date['old'] . " → " . $date['new'])
@section('hero_title', $status[0]->name . "の更新差分")
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
    <li><a href="/user/{{$id}}/rating">Rating</a></li>
    <li class="is-active"><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection

@section('content')
    <article class="box">
        @if (!$isLoggedIn)
            <p>ツイート機能を使うにはログインしてください。<br><button class="button" disabled>以下を画像化してツイート</button></p>
        @elseif (!$isTwitterEnabled)
            <p>Twitter連携を行っていません。連携は<a href="/setting">こちら</a>。<br>
                <button class="button" disabled>以下を画像化してツイート</button></p>
        @else
            <p>このアカウントでツイートします: {{$twitterScreenName}}</p>
            <form id="tweet_form" action="/tweet/image" method="post" onsubmit="document.getElementById(\'submit_button\').disabled = true">
                {{csrf_field()}}
                <div class="field">
                    <label class="label">ツイートの内容(100文字まで)</label>
                    <div class="control">
                        <textarea name="status" class="textarea" maxlength="100">{{$status[0]->name}}さんの更新差分 https://ongeki-score.net/user/{{$id}} #OngekiScoreLog</textarea>
                    </div>
                </div>
                <button type="button" id="submit_button" class="button convert-to-image-button">以下を画像化してツイート</button>
            </form><div style="padding: 0.75em 0">
                <div class="progress-message"></div>
                <progress class="progress is-progress is-link" value="0" max="100">0%</progress>
            </div>
            <p>全ての記録をツイートします。４枚に収まらない場合はインリプライに続きます。(1枚につき7曲)<br>
                <b>初めてこの機能を使用する場合は大量のツイートがされる可能性があります。十分注意して使用いただくようお願いいたします。</b></p>
        @endif
        <div class="field">
            <label class="label">表示期間</label>
            <div id="select-generation" class="select">
                <select>
                    @foreach ($display['select'] as $key => $value)
                <option class="select-generations-option" value='{{$key}}'{{$value["selected"]}}>{{$key}}: {{$value["value"]}} ～</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="label">更新種別でフィルタリングする</label>
            <a href="{{$url}}">すべて表示</a> /
            <a href="{{$url}}?filter=bs">Battle Score更新</a> /
            <a href="{{$url}}?filter=ts">Technical Score更新</a> /
            <a href="{{$url}}?filter=od">Over Damage更新</a>
            @if ($filter !== "")
                <br>{{$filter}}
            @endif
        </div>

        <span id="current-url" style="display: none;">{{$display['url']}}</span>
        <hr>
        <div class="user-progress">
            <div class="info">
                <div class="right">
                    <span class="site-name">{{config('app.name')}}</span>&nbsp;
                    <span class="version">
                        {{config('env.application-version')}}</b>
                        @if (config('env.commit-hash', NULL) !== NULL)
                            ({{config('env.commit-hash')}})
                        @endif
                    </span>&nbsp;
                    <span class="url">{{config('app.url')}}</span>
                </div>
            </div>

            <div class="notification">
                <div class="date"><span class="update">{{$date['old']}} → {{$date['new']}}</span></div>
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
                        <th colspan="2">Over Damage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total</td>
                        <td class="right">{{number_format($score['new']['Total']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Total']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Total']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Total']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Total']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Total']['technical_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Total']['over_damage_high_score'], 2)}}%</td>
                        <td class="right difference">{{($score['difference']['Total']['over_damage_high_score'] === 0.0) ? "" : "+" . number_format($score['difference']['Total']['over_damage_high_score'], 2) . "%"}}</td>

                    </tr>
                    <tr>
                        <td>Basic</td>
                        <td class="right">{{number_format($score['new']['Basic']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Basic']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Basic']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Basic']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Basic']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Basic']['technical_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Basic']['over_damage_high_score'], 2)}}%</td>
                        <td class="right difference">{{($score['difference']['Basic']['over_damage_high_score'] === 0.0) ? "" : "+" . number_format($score['difference']['Basic']['over_damage_high_score'], 2) . "%"}}</td>

                    </tr>
                    <tr>
                        <td>Advanced</td>
                        <td class="right">{{number_format($score['new']['Advanced']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Advanced']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Advanced']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Advanced']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Advanced']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Advanced']['technical_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Advanced']['over_damage_high_score'], 2)}}%</td>
                        <td class="right difference">{{($score['difference']['Advanced']['over_damage_high_score'] === 0.0) ? "" : "+" . number_format($score['difference']['Advanced']['over_damage_high_score'], 2) . "%"}}</td>

                    </tr>
                    <tr>
                        <td>Expert</td>
                        <td class="right">{{number_format($score['new']['Expert']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Expert']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Expert']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Expert']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Expert']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Expert']['technical_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Expert']['over_damage_high_score'], 2)}}%</td>
                        <td class="right difference">{{($score['difference']['Expert']['over_damage_high_score'] === 0.0) ? "" : "+" . number_format($score['difference']['Expert']['over_damage_high_score'], 2) . "%"}}</td>
                    </tr>
                    <tr>
                        <td>Master</td>
                        <td class="right">{{number_format($score['new']['Master']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Master']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Master']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Master']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Master']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Master']['technical_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Master']['over_damage_high_score'], 2)}}%</td>
                        <td class="right difference">{{($score['difference']['Master']['over_damage_high_score'] === 0.0) ? "" : "+" . number_format($score['difference']['Master']['over_damage_high_score'], 2) . "%"}}</td>
                    </tr>
                    <tr>
                        <td>Lunatic</td>
                        <td class="right">{{number_format($score['new']['Lunatic']['battle_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Lunatic']['battle_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Lunatic']['battle_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Lunatic']['technical_high_score'])}}</td>
                        <td class="right difference">{{($score['difference']['Lunatic']['technical_high_score'] === 0) ? "" : "+" . number_format($score['difference']['Lunatic']['technical_high_score'])}}</td>
                        <td class="right">{{number_format($score['new']['Lunatic']['over_damage_high_score'], 2)}}%</td>
                        <td class="right difference">{{($score['difference']['Lunatic']['over_damage_high_score'] === 0.0) ? "" : "+" . number_format($score['difference']['Lunatic']['over_damage_high_score'], 2) . "%"}}</td>
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
                        <hr>
                        @if (++$count >= 9)
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

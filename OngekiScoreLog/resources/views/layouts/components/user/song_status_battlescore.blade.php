<div class="box">
    <p>登録されている全ユーザーのバトルスコアのうち、一番高いものと比較することが出来ます。<br>
    （※全国ランキング１位のスコアではありません！）</p>
    <p>最終更新: {{$lastUpdate->format('Y-m-d H:i:s')}} （?%表示は未集計です）</p>

    <div class="buttons has-addons">
        <a href="/user/{{$id}}/battlescore" class="button{{($difficulty === "") ? "" : " is-primary"}}">Master + Lunatic</a>
        <a href="/user/{{$id}}/battlescore/basic" class="button{{($difficulty === "basic") ? "" : " basic"}}">Basic</a>
        <a href="/user/{{$id}}/battlescore/advanced" class="button{{($difficulty === "advanced") ? "" : " advanced"}}">Advanced</a>
        <a href="/user/{{$id}}/battlescore/expert" class="button{{($difficulty === "expert") ? "" : " expert"}}">Expert</a>
        <a href="/user/{{$id}}/battlescore/master" class="button{{($difficulty === "master") ? "" : " master"}}">Master</a>
        <a href="/user/{{$id}}/battlescore/lunatic" class="button{{($difficulty === "lunatic") ? "" : " lunatic"}}">Lunatic</a>
    </div>
</div>

<article class="box">
    @component('layouts/components/user/table_scale_button') @endcomponent

    <div id="sort_table" class="table_wrap scalable">
        <table class="table">
            <thead>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_bs"><abbr title="Battle Score">BS</abbr></th>
                    <th class="sort" data-sort="sort_key1"><abbr title="Top Ranker Score">Top</abbr></th>
                    <th class="sort" data-sort="sort_key2">diff</th>
                    <th class="sort" data-sort="sort_key3">達成度</th>
                    <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_bs"><abbr title="Battle Score">BS</abbr></th>
                    <th class="sort" data-sort="sort_key1"><abbr title="Top Ranker Score">Top</abbr></th>
                    <th class="sort" data-sort="sort_key2">diff</th>
                    <th class="sort" data-sort="sort_key3">達成度</th>
                    <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </tfoot>
            <tbody class="list">
                @foreach ($score as $s)
                    <tr>
                        <td class="sort_title"><span class="sort-key">{{$s->title}}</span><a href="{{url("/user/" . $id . "/music/" . $s->song_id . "/" . strtolower($s->difficulty_str))}}">{{$s->title}}</a></td>
                        <td class="sort_difficulty"><span class="sort-key">{{$s->difficulty}}</span>{{substr($s->difficulty_str, 0, 3)}}</td>
                        <td class="sort_bs">{{number_format($s->battle_high_score)}}</td>
                        @if (array_key_exists($s->song_id . "_" . $s->difficulty, $topRankerScore))
                            <td class="sort_key1">{{number_format($topRankerScore[$s->song_id . "_" . $s->difficulty])}}</td>
                            <td class="sort_key2">{{number_format(abs(($s->battle_high_score - $topRankerScore[$s->song_id . "_" . $s->difficulty])))}}</td>
                            @if ($topRankerScore[$s->song_id . "_" . $s->difficulty] == 0)
                                {{-- ありえなさそうだけどDIV/0対策 --}}
                                <td class="sort_key3">100.00%</td>
                            @elseif ($s->battle_high_score == $topRankerScore[$s->song_id . "_" . $s->difficulty])
                                {{-- 1位なので100%！ --}}
                                <td class="sort_key3">100.00%</td>
                            @elseif ($s->battle_high_score == 0)
                                {{-- 未プレイなので0%にする --}}
                                <td class="sort_key3">0.00%</td>
                            @else
                                {{-- １位ではないので計算 --}}
                                <td class="sort_key3">{{number_format(floor((($s->battle_high_score / $topRankerScore[$s->song_id . "_" . $s->difficulty]))))}}%</td>
                            @endif
                        @else
                            <td class="sort_key1">?%</td>
                            <td class="sort_key2">?%</td>
                            <td class="sort_key3">?%</td>
                        @endif
                        <td class="sort_update">{{date('Y-m-d', strtotime($s->updated_at))}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</article>

<div class="box">
    <p>登録されている全ユーザーのオーバーダメージのうち、一番高いものと比較することが出来ます。<br>
    OD埋めなどにご活用ください。</p>
    <p>プレイしている楽曲内で、一番ODが高い難易度のみ表示されます。全難易度のODが0の曲は表示されません。なお削除楽曲等も表示されます。ご了承下さい。</p>
    最終更新: {{$lastUpdate->format('Y-m-d H:i:s')}} （?%表示は未集計です）
</div>

<article class="box">
    <p>
        <b>表示倍率の変更</b><br>
        <button class="button table_scale_change">25%</button>
        <button class="button table_scale_change">50%</button>
        <button class="button table_scale_change">75%</button>
        <button class="button table_scale_change">100%</button>
    </p>
    <div id="sort_table" class="table_wrap scalable">
        <table class="table">
            <thead>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_od"><abbr title="Over Damage">OD</abbr></th>
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
                    <th class="sort" data-sort="sort_od"><abbr title="Over Damage">OD</abbr></th>
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
                        <td class="sort_od">{{$s->over_damage_high_score . "%"}}</td>
                        @if (array_key_exists($s->song_id . "_" . $s->difficulty, $topRankerScore))
                            <td class="sort_key1">{{$topRankerScore[$s->song_id . "_" . $s->difficulty] . "%"}}</td>
                            <td class="sort_key2">{{$s->over_damage_high_score - $topRankerScore[$s->song_id . "_" . $s->difficulty]}}%</td>
                            <td class="sort_key3">{{($topRankerScore[$s->song_id . "_" . $s->difficulty] != 0) ? ($s->over_damage_high_score / $topRankerScore[$s->song_id . "_" . $s->difficulty]) * 100 : 100}}%</td>
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

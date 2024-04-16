<article class="box">
    <p>
        レート値が灰斜体のものは譜面定数が不明のため、推定値で表示されているものです。<br>
        赤太字のものはその曲のレート値が理論値に達しているものです。<br>
        レート値はオンゲキNETのプレミアムプランに加入していないと表示されません。
    </p>
    @component('layouts/components/user/table_scale_button') @endcomponent

    <div id="sort_table" class="table_wrap scalable">
        <table class="table">
            <thead>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_genre">Category</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_lv">Lv</th>
                    <th class="sort" data-sort="sort_fb">FB</th>
                    <th class="sort" data-sort="sort_fc">FC</th>
                    <th class="sort" data-sort="sort_ab">AB</th>
                    <th class="sort" data-sort="sort_rank0">評価</th>
                    <th class="sort" data-sort="sort_rank1">Rank</th>
                    <th class="sort" data-sort="sort_bs"><abbr title="Battle Score">BS</abbr></th>
                    <th class="sort" data-sort="sort_od"><abbr title="Over Damage">OD</abbr></th>
                    <th class="sort" data-sort="sort_nod"><abbr title="Next Over Damage">Next</abbr></th>
                    <th class="sort" data-sort="sort_ts"><abbr title="Technical Score">TS</abbr></th>
                    <th class="sort" data-sort="sort_nts"><abbr title="Next Technical Score">Next</abbr></th>
                    <th class="sort" data-sort="sort_rate">Rate</th>
                    <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_genre">Category</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_lv">Lv</th>
                    <th class="sort" data-sort="sort_fb">FB</th>
                    <th class="sort" data-sort="sort_fc">FC</th>
                    <th class="sort" data-sort="sort_ab">AB</th>
                    <th class="sort" data-sort="sort_rank0">評価</th>
                    <th class="sort" data-sort="sort_rank1">Rank</th>
                    <th class="sort" data-sort="sort_bs"><abbr title="Battle Score">BS</abbr></th>
                    <th class="sort" data-sort="sort_od"><abbr title="Over Damage">OD</abbr></th>
                    <th class="sort" data-sort="sort_nod"><abbr title="Next Over Damage">Next</abbr></th>
                    <th class="sort" data-sort="sort_ts"><abbr title="Technical Score">TS</abbr></th>
                    <th class="sort" data-sort="sort_nts"><abbr title="Next Technical Score">Next</abbr></th>
                    <th class="sort" data-sort="sort_rate">Rate</th>
                        <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </tfoot>
            <tbody class="list">
                @foreach ($score as $s)
                    <tr>
                        <td class="sort_title"><span class="sort-key">{{$s->title}}</span><a href="{{url("/user/" . $id . "/music/" . $s->song_id . "/" . strtolower($s->difficulty_str))}}">{{$s->title}}</a></td>
                        <td class="sort_genre">{{$s->genre}}</td>
                        <td class="sort_difficulty"><span class="sort-key">{{$s->difficulty}}</span>{{substr($s->difficulty_str, 0, 3)}}</td>
                        <td class="sort_lv">{{$s->level_str}}</td>
                        <td class="sort_fb table-tag">{!! $s->full_bell ? '<span class="tag full-bell">FB</span>' : "" !!}</td>
                        <td class="sort_fc table-tag">{!! $s->full_combo ? '<span class="tag full-combo">FC</span>' : "" !!}</td>
                        <td class="sort_ab table-tag">{!! $s->all_break ? '<span class="tag all-break">AB</span>' : "" !!}</td>
                        <td class="sort_rank0"><span class="sort-key">{{$s->over_damage_high_score}}</span>{{$s->over_damage_high_score_rank}}</td>
                        <td class="sort_rank1"><span class="sort-key">{{$s->technical_high_score}}</span>{{$s->technical_high_score_rank}}</td>
                        <td class="sort_bs"><span class="sort-key">{{$s->battle_high_score}}</span>{{number_format($s->battle_high_score)}}</td>
                        <td class="sort_od">{{$s->over_damage_high_score . "%"}}</td>
                        <td class="sort_nod"><span class="sort-key">{{$s->over_damage_high_score_next}}</span>{{($s->over_damage_high_score < 500) ? $s->over_damage_high_score_next . "%" : "-"}}</td>
                        <td class="sort_ts"><span class="sort-key">{{$s->technical_high_score}}</span>{{number_format($s->technical_high_score)}}</td>
                        <td class="sort_nts"><span class="sort-key">{{$s->technical_high_score_next}}</span>{{($s->technical_high_score_next != 0) ? number_format($s->technical_high_score_next) : "-"}}</td>
                        <td>{!!$s->ratingValue!!}</td>
                        <td class="sort_update">{{$s->updated_at}}</td>
                        <td class="table-hidden-data sort_raw_battle_rank">{{$s->over_damage_high_score_rank}}</td>
                        <td class="table-hidden-data sort_raw_technical_rank">{{$s->rawTechnicalRank}}</td>
                        <td class="table-hidden-data sort_raw_lamp">{{$s->rawLamp}}</td>
                        <td class="table-hidden-data sort_raw_difficulty">{{$s->rawDifficulty}}</td>
                        <td class="table-hidden-data sort_rate">{{$s->ratingValueRaw}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</article>

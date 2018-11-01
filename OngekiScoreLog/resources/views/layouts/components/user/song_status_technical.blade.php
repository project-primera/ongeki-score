<article class="box">
    <div id="sort_table" class="table_wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_lv">Lv</th>
                    <th class="sort" data-sort="sort_fb">FB</th>
                    <th class="sort" data-sort="sort_fc">FC</th>
                    <th class="sort" data-sort="sort_ab">AB</th>
                    <th class="sort" data-sort="sort_rank1">Rank</th>
                    <th class="sort" data-sort="sort_ts"><abbr title="Technical Score">TS</abbr></th>
                    <th class="sort" data-sort="sort_nts"><abbr title="Next Technical Score">Next</abbr></th>
                    <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_lv">Lv</th>
                    <th class="sort" data-sort="sort_fb">FB</th>
                    <th class="sort" data-sort="sort_fc">FC</th>
                    <th class="sort" data-sort="sort_ab">AB</th>
                    <th class="sort" data-sort="sort_rank1">Rank</th>
                    <th class="sort" data-sort="sort_ts"><abbr title="Technical Score">TS</abbr></th>
                    <th class="sort" data-sort="sort_nts"><abbr title="Next Technical Score">Next</abbr></th>
                    <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </tfoot>
            <tbody class="list">
                @foreach ($score as $s)
                    <tr>
                        <td class="sort_title">{{$s->title}}</td>
                        <td class="sort_difficulty"><span class="sort-key">{{$s->difficulty}}</span>{{substr($s->difficulty_str, 0, 3)}}</td>
                        <td class="sort_lv">{{$s->level_str}}</td>
                        <td class="sort_fb table-tag">{!! $s->full_bell ? '<span class="tag full-bell">FB</span>' : "" !!}</td>
                        <td class="sort_fc table-tag">{!! $s->full_combo ? '<span class="tag full-combo">FC</span>' : "" !!}</td>
                        <td class="sort_ab table-tag">{!! $s->all_break ? '<span class="tag all-break">AB</span>' : "" !!}</td>
                        <td class="sort_rank1"><span class="sort-key">{{$s->technical_high_score}}</span>{{$s->technical_high_score_rank}}</td>
                        <td class="sort_ts"><span class="sort-key">{{$s->technical_high_score}}</span>{{number_format($s->technical_high_score)}}</td>
                        <td class="sort_nts"><span class="sort-key">{{$s->technical_high_score_next}}</span>{{($s->technical_high_score_next != 0) ? number_format($s->technical_high_score_next) : "-"}}</td>
                        <td class="sort_update">{{date('Y-m-d', strtotime($s->updated_at))}}</td>
                        <td class="table-hidden-data sort_raw_battle_rank">{{$s->over_damage_high_score_rank}}</td>
                        <td class="table-hidden-data sort_raw_technical_rank">{{$s->rawTechnicalRank}}</td>
                        <td class="table-hidden-data sort_raw_lamp">{{$s->rawLamp}}</td>
                        <td class="table-hidden-data sort_raw_difficulty">{{$s->rawDifficulty}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</article>

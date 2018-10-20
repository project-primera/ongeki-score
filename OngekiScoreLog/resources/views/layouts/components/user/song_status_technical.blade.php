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
                    @component('layouts/components/user/song_status_technical_record')
                        @slot('title')
                            {{$s->title}}
                        @endslot
                        @slot('difficulty')
                            <span class="sort-key">{{$s->difficulty}}</span>
                            {{substr($s->difficulty_str, 0, 3)}}
                        @endslot
                        @slot('level')
                            {{$s->level_str}}
                        @endslot
                        @slot('fullBell')
                            {!! $s->full_bell ? '<span class="tag full-bell">FB</span>' : "" !!}
                        @endslot
                        @slot('fullCombo')
                            {!! $s->full_combo ? '<span class="tag full-combo">FC</span>' : "" !!}
                        @endslot
                        @slot('allBreak')
                            {!! $s->all_break ? '<span class="tag all-break">AB</span>' : "" !!}
                        @endslot
                        @slot('technicalRank')
                            <span class="sort-key">{{$s->technical_high_score}}</span>
                            {{$s->technical_high_score_rank}}
                        @endslot
                        @slot('technicalHighScore')
                            <span class="sort-key">{{$s->technical_high_score}}</span>
                            {{number_format($s->technical_high_score)}}
                        @endslot
                        @slot('nextTechnicalScore')
                            <span class="sort-key">{{$s->technical_high_score_next}}</span>
                            {{($s->technical_high_score_next != 0) ? number_format($s->technical_high_score_next) : "-"}}
                        @endslot
                        @slot('updatedAt')
                            {{date('Y-m-d', strtotime($s->updated_at))}}
                        @endslot
                        @slot('rawBattleRank')
                            {{$s->over_damage_high_score_rank}}
                        @endslot
                        @slot('rawTechnicalRank')
                            {{($s->technical_high_score < 850000) ? "under A" : $s->technical_high_score_rank}}
                        @endslot
                        @slot('rawLamp')
                            {{$s->rawLamp}}
                        @endslot
                    @endcomponent
                @endforeach
            </tbody>
        </table>
    </div>
</article>

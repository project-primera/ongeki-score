<article class="box">
    <div id="sort_table" class="table_wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_lv">Lv</th>
                    <th class="sort" data-sort="sort_rank0">評価</th>
                    <th class="sort" data-sort="sort_bs"><abbr title="Battle Score">BS</abbr></th>
                    <th class="sort" data-sort="sort_od"><abbr title="Over Damage">OD</abbr></th>
                    <th class="sort" data-sort="sort_nod"><abbr title="Next Over Damage">Next</abbr></th>
                    <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sort" data-sort="sort_title">Title</th>
                    <th class="sort" data-sort="sort_difficulty"><abbr title="Difficulty">Dif</abbr></th>
                    <th class="sort" data-sort="sort_lv">Lv</th>
                    <th class="sort" data-sort="sort_rank0">評価</th>
                    <th class="sort" data-sort="sort_bs"><abbr title="Battle Score">BS</abbr></th>
                    <th class="sort" data-sort="sort_od"><abbr title="Over Damage">OD</abbr></th>
                    <th class="sort" data-sort="sort_nod"><abbr title="Next Over Damage">Next</abbr></th>
                    <th class="sort desc" data-sort="sort_update">Update</th>
                </tr>
            </tfoot>
            <tbody class="list">
                @foreach ($score as $s)
                    @component('layouts/components/user/song_status_battle_record')
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
                        @slot('battleRank')
                            <span class="sort-key">{{$s->over_damage_high_score}}</span>
                            {{$s->over_damage_high_score_rank}}
                        @endslot
                        @slot('battleScore')
                            <span class="sort-key">{{$s->battle_high_score}}</span>
                            {{number_format($s->battle_high_score)}}
                        @endslot
                        @slot('overDamage')
                            {{$s->over_damage_high_score . "%"}}
                        @endslot
                        @slot('nextOverDamage')
                            <span class="sort-key">{{$s->over_damage_high_score_next}}</span>
                            {{($s->over_damage_high_score < 500) ? $s->over_damage_high_score_next . "%" : "-"}}
                        @endslot
                        @slot('updatedAt')
                            {{date('Y-m-d', strtotime($s->updated_at))}}
                        @endslot
                        @slot('rawBattleRank')
                            {{$s->over_damage_high_score_rank}}
                        @endslot
                        @slot('rawTechnicalRank')
                            {{$s->rawTechnicalRank}}
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

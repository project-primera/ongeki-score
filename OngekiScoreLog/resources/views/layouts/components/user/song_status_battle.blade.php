<article class="box">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th colspan="1">Rank</th>
                <th><abbr title="Battle Score">BS</abbr></th>
                <th><abbr title="Over Damage">OD</abbr></th>
                <th><abbr title="Next Over Damage">Next</abbr></th>
                <th>Update</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th colspan="1">Rank</th>
                <th><abbr title="Battle Score">BS</abbr></th>
                <th><abbr title="Over Damage">OD</abbr></th>
                <th><abbr title="Next Over Damage">Next</abbr></th>
                <th>Update</th>
            </tr>
        </tfoot>
        <tbody>
            @foreach ($score as $s)
                @component('layouts/components/user/song_status_battle_record')
                    @slot('title')
                        {{$s->title}}
                    @endslot
                    @slot('difficulty')
                        {{substr($s->difficulty_str, 0, 3)}}
                    @endslot
                    @slot('level')
                        {{$s->level_str}}
                    @endslot
                    @slot('battleRank')
                        {{$s->over_damage_high_score_rank}}
                    @endslot
                    @slot('battleScore')
                        {{number_format($s->battle_high_score)}}
                    @endslot
                    @slot('overDamage')
                        {{$s->over_damage_high_score . "%"}}
                    @endslot
                    @slot('nextOverDamage')
                        {{($s->over_damage_high_score < 500) ? $s->over_damage_high_score_next . "%" : "-"}}
                    @endslot
                    @slot('updatedAt')
                        {{date('Y-m-d', strtotime($s->updated_at))}}
                    @endslot
                @endcomponent
            @endforeach
        </tbody>
    </table>
</article>

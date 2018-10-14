<article class="box">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th colspan="3">Lamp</th>
                <th colspan="1">Rank</th>
                <th><abbr title="Technical Score">TS</abbr></th>
                <th><abbr title="Next Technical Score">Next</abbr></th>
                <th>Update</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th colspan="3">Lamp</th>
                <th colspan="1">Rank</th>
                <th><abbr title="Technical Score">TS</abbr></th>
                <th><abbr title="Next Technical Score">Next</abbr></th>
                <th>Update</th>
            </tr>
        </tfoot>
        <tbody>
            @foreach ($score as $s)
                @component('layouts/components/user/song_status_technical_record')
                    @slot('title')
                        {{$s->title}}
                    @endslot
                    @slot('difficulty')
                        {{substr($s->difficulty_str, 0, 3)}}
                    @endslot
                    @slot('level')
                        {{$s->level_str}}
                    @endslot
                    @slot('fullBell')
                        {{$s->full_bell ? "FB" : ""}}
                    @endslot
                    @slot('fullCombo')
                        {{$s->full_combo ? "FC" : ""}}
                    @endslot
                    @slot('allBreak')
                        {{$s->all_break ? "AB" : ""}}
                    @endslot
                    @slot('technicalRank')
                        {{$s->technical_high_score_rank}}
                    @endslot
                    @slot('technicalHighScore')
                        {{number_format($s->technical_high_score)}}
                    @endslot
                    @slot('nextTechnicalScore')
                        {{($s->technical_high_score_next != 0) ? number_format($s->technical_high_score_next) : "-"}}
                    @endslot
                    @slot('updatedAt')
                        {{date('Y-m-d', strtotime($s->updated_at))}}
                    @endslot
                @endcomponent
            @endforeach
        </tbody>
    </table>
</article>

<h4 class="title is-4">レーティング対象曲</h4>
<div class="table_wrap scalable">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th><abbr title="Technical Score">TS</abbr></th>
                <th>Lamp</th>
                <th>通常レート値</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th><abbr title="Technical Score">TS</abbr></th>
                <th>Lamp</th>
                <th>通常レート値</th>
                <th>Rate</th>
            </tr>
        </tfoot>
        <tbody>
            @for ($i = $start; $i < $targetCount; $i++)
                <tr>
                    <td>{{$i + 1}}</td>
                    @if (array_key_exists($i, $array) && array_key_exists('song_id', $array[$i]))
                        <td class="sort_title"><a href="{{url("/user/" . $id . "/music/" . $array[$i]->song_id . "/" . strtolower($array[$i]->difficulty_str))}}">{{$array[$i]->title}}</a></td>
                    @else
                        <td class="sort_title">{{$array[$i]->title}}</td>
                    @endif
                    <td>{{substr($array[$i]->difficulty_str, 0, 3)}}</td>
                    <td>{!!$array[$i]->extraLevelStr!!}</td>
                    <td>{{number_format($array[$i]->technical_high_score)}}</td>
                    <td>{{ $array[$i]->lampForRating }}</td>
                    <td>{!!$array[$i]->ratingValue!!}</td>
                    <td>{!!$array[$i]->singleRatingValue!!}</td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>

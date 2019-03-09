<div class="table_wrap scalable">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th><abbr title="Technical Score">TS</abbr></th>
                <th>Rate</th>
                <th><abbr title="この曲のレート値が0.01上昇するために必要なスコアです">+0.01</abbr></th>
                <th><abbr title="1曲のみでユーザーのレート値が0.01上昇するために必要なスコアです">+{{sprintf("%.2f", $statistics->totalRatingCount / 100)}}</abbr></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>Title</th>
                <th><abbr title="Difficulty">Dif</abbr></th>
                <th>Lv</th>
                <th><abbr title="Technical Score">TS</abbr></th>
                <th>Rate</th>
                <th><abbr title="この曲のレート値が0.01上昇するために必要なスコアです">+0.01</abbr></th>
                <th><abbr title="1曲のみでユーザーのレート値が0.01上昇するために必要なスコアです">+{{sprintf("%.2f", $statistics->totalRatingCount / 100)}}</abbr></th>
            </tr>
        </tfoot>
        <tbody>
            @for ($i = 0; $i < $statistics->newBestRatingCount; $i++)
                <tr>
                    <td>{{$array[$i]->title}}</td>
                    <td>{{substr($array[$i]->difficulty_str, 0, 3)}}</td>
                    <td>{{$array[$i]->level_str}}</td>
                    <td>{{number_format($array[$i]->technical_high_score)}}</td>
                    <td>{!!$array[$i]->ratingValue!!}</td>
                    <td>{{$array[$i]->targetMusicRateMusic}}</td>
                    <td>{{$array[$i]->targetMusicRateUser}}</td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
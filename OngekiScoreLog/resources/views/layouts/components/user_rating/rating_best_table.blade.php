<h4 class="title is-4">レーティング対象曲</h4>
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
            @for ($i = $start; $i < $targetCount; $i++)
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
<h4 class="title is-4">レーティング対象外 候補曲</h4>
<p>スコアを上げることでレーティング対象曲入りを望める楽曲です。</p>
<div class="table_wrap scalable">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th><abbr title="Difficulty">Dif</abbr></th>
                    <th>Lv</th>
                    <th><abbr title="Technical Score">TS</abbr></th>
                    <th>Rate</th>
                    <th><abbr title="同枠楽曲のレート値で一番低いものとの差です">レート差</abbr></th>
                    <th><abbr title="レーティング対象曲に入るために必要なスコアです">必要スコア</abbr></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Title</th>
                    <th><abbr title="Difficulty">Dif</abbr></th>
                    <th>Lv</th>
                    <th><abbr title="Technical Score">TS</abbr></th>
                    <th>Rate</th>
                    <th><abbr title="同枠楽曲のレート値で一番低いものとの差です">レート差</abbr></th>
                    <th><abbr title="レーティング対象曲に入るために必要なスコアです">必要スコア</abbr></th>
                </tr>
            </tfoot>
            <tbody>
                @for ($i = $targetCount; $i < $end; $i++)
                    @if($array[$i]->minDifferenceScore !== "")
                        <tr>
                            <td>{{$array[$i]->title}}</td>
                            <td>{{substr($array[$i]->difficulty_str, 0, 3)}}</td>
                            <td>{{$array[$i]->level_str}}</td>
                            <td>{{number_format($array[$i]->technical_high_score)}}</td>
                            <td>{!!$array[$i]->ratingValue!!}</td>
                            <td>{{sprintf("%.2f", $array[$i]->minDifferenceRate)}}</td>
                            <td>{{$array[$i]->minDifferenceScore}}</td>
                        </tr>
                    @endif
                @endfor
            </tbody>
        </table>
    </div>
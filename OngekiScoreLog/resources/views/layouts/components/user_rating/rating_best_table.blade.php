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
                </tr>
            @endfor
        </tbody>
    </table>
</div>

{{-- // ランプ関連でややこしいので廃止

<h4 class="title is-4">レーティング対象外 候補曲</h4>
<p>スコアを上げることでレーティング対象曲入りを望める楽曲です。<br>
記録されているスコアのうち、レート差が1.0以上の楽曲のみ表示されます。</p>
<div class="table_wrap scalable">
    @if ($targetCount == $end)
        <p><b>レーティング対象外の候補曲はありません。</b></p>
    @else

        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th><abbr title="Difficulty">Dif</abbr></th>
                    <th>Lv</th>
                    <th><abbr title="Technical Score">TS</abbr></th>
                    <th>Lamp</th>
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
                    <th>Lamp</th>
                    <th>Rate</th>
                    <th><abbr title="同枠楽曲のレート値で一番低いものとの差です">レート差</abbr></th>
                    <th><abbr title="レーティング対象曲に入るために必要なスコアです">必要スコア</abbr></th>
                </tr>
            </tfoot>
            <tbody>
                @for ($i = $targetCount; $i < $end; $i++)
                    @if($array[$i]->minDifferenceScore !== "" && $array[$i]->technical_high_score !== 0 && $array[$i]->minDifferenceRate >= -1.0)
                        <tr>
                            @if (array_key_exists($i, $array) && array_key_exists('song_id', $array[$i]))
                                <td class="sort_title"><a href="{{url("/user/" . $id . "/music/" . $array[$i]->song_id . "/" . strtolower($array[$i]->difficulty_str))}}">{{$array[$i]->title}}</a></td>
                            @else
                                <td class="sort_title">{{$array[$i]->title}}</td>
                            @endif
                            <td>{{substr($array[$i]->difficulty_str, 0, 3)}}</td>
                            <td>{!!$array[$i]->extraLevelStr!!}</td>
                            <td>{{number_format($array[$i]->technical_high_score)}}</td>
                            <td>{{$array[$i]->lampForRating}}</td>
                            <td>{!!$array[$i]->ratingValue!!}</td>
                            <td>{{sprintf("%.3f", $array[$i]->minDifferenceRate)}}</td>
                            <td>{{$array[$i]->minDifferenceScore}}</td>
                        </tr>
                    @endif
                @endfor
            </tbody>
        </table>
    @endif
</div>
--}}

@extends('layouts.app')

@section('title', '楽曲リスト')
@section('sidemark_music', "is-active")
@section('hero_subtitle', "")
@section('hero_title', "楽曲リスト")

@section('additional_footer')
    <script type="text/javascript" src="{{ mix('/js/sortMusic.js') }}"></script>
@endsection

@section('content')
    <article class="box">
        <p>
            各ユーザーごとのレート値は"ユーザーページ>Technical"からご確認いただけます。
        </p>
        <p>
            譜面定数が灰斜体のものは推定値です。<br>
            データをご提供頂ける場合は「ハンドルネーム, 楽曲名, 難易度, 譜面定数」を添えて、以下の連絡先からご連絡いただけますと幸いです。<br>
            <a href="https://twitter.com/ongeki_score" target="_blank">Twitter@ongeki_score</a>
        </p>
        <div class="notification">
            <p>
                掲載されている譜面定数データは<b>以下のデータ御提供者様の御名前を併記することを条件として</b>、誰でも利用することができます。但し、楽曲名などを始めとした著作物に対しての許諾ではございません。個人の判断でご利用ください。
            </p>
        </div>
        <p>
            <b>データ御提供者様(順不同 敬称略)</b><br>
            <ul>
                <li><a href="https://twitter.com/Rinsaku471" target="_blank">Twitter@Rinsaku471</a></li>
                <li><a href="https://twitter.com/RKS49019722" target="_blank">Twitter@RKS49019722</a></li>
                <li><a href="https://twitter.com/masa_9713" target="_blank">Twitter@masa_9713</a></li>
            </ul>
            以上の皆様から頂いた情報をもとにしております。御提供ありがとうございました。
        </p>

        <div id="sort_table" class="table_wrap">
            <table class="table is-narrow user-progress-total-table music-list">
                <thead>
                    <tr>
                        <th class="sort" data-sort="sort_title">タイトル</th>
                        <th class="sort" data-sort="sort_difficulty">難易度</th>
                        <th class="sort" data-sort="sort_level">レベル</th>
                        <th class="sort" data-sort="sort_extra_level">譜面定数</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="sort" data-sort="sort_title">タイトル</th>
                        <th class="sort" data-sort="sort_difficulty">難易度</th>
                        <th class="sort" data-sort="sort_level">レベル</th>
                        <th class="sort" data-sort="sort_extra_level">譜面定数</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    @foreach ($view as $key => $value)
                        <tr>
                            <td class="sort_title">{{$value['title']}}</td>
                            <td>{{$value['difficulty']}}</td>
                            <td class="sort_level">{{$value['level']}}</td>
                            <td class="sort_title">{!!$value['extra_level']!!}</td>
                            <td class="table-hidden-data sort_extra_level">{{$value['extra_level_raw']}}</td>
                            <td class="table-hidden-data sort_difficulty">{{$value['difficulty_raw']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection
@extends('layouts.app')

@section('title', '楽曲リスト')
@section('sidemark_music', "is-active")
@section('hero_subtitle', "")
@section('hero_title', "楽曲リスト")

@section('additional_footer')
    <script type="text/javascript" src="{{ mix('/js/sortMusic.js') }}"></script>
@endsection

@section('content')
<div class="container">
    <article class="box">
        <p>
            譜面定数が青字斜体のものは推定値です。<br>
            データをご提供頂ける場合は「ハンドルネーム, 楽曲名, 難易度, 譜面定数」を添えて、以下の連絡先からご連絡いただけますと幸いです。<br>
            <a href="https://twitter.com/ongeki_score" target="_blank">Twitter@ongeki_score</a><br>
            <a href="mailto:info&#64;ongeki-score.net">Mail: info&nbsp;(at)&nbsp;ongeki-score.net</a>
        </p>
        <p>
            以下の譜面定数データは使用元の表記なく、誰でも使用することができます。<br>
            但し、楽曲名などを始めとした著作物に対しての許諾ではございません。個人の判断でご利用ください。
        </p>
        <!--
        <p>
            データ御提供者様(順不同 敬称略)<br>
        </p>
        -->

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
</div>
@endsection
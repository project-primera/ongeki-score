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
            各ユーザーごとのレート値は"ユーザーページ>Technical"からご確認いただけます。<br>
            譜面定数が灰斜体のものは推定値です。
        </p>
        <div class="notification">
            譜面定数データは、<a href="https://twitter.com/ongeki_level" target="_blank">オンゲキ譜面定数部(Twitter@ongeki_level)</a>様のデータを活用しております。
        </div>

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
                        <td class="sort_title"><span class="sort-key">{{$value['title']}}</span><a href="/music/{{$value['id']}}/{{$value['difficulty']}}">{{$value['title']}}</a></td>
                            <td>{{ucwords($value['difficulty'])}}</td>
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
